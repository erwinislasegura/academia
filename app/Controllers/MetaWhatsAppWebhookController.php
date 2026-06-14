<?php

final class MetaWhatsAppWebhookController extends Controller
{
    public function verify(): void
    {
        $mode = (string) ($_GET['hub_mode'] ?? $_GET['hub.mode'] ?? '');
        $token = (string) ($_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? '');
        $challenge = (string) ($_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? '');
        $expectedToken = (string) (App::config('infobip.meta_webhook_verify_token') ?: getenv('META_WHATSAPP_WEBHOOK_VERIFY_TOKEN') ?: '');

        if ($mode === 'subscribe' && $challenge !== '' && ($expectedToken === '' || hash_equals($expectedToken, $token))) {
            header('Content-Type: text/plain; charset=UTF-8');
            echo $challenge;
            exit;
        }

        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Forbidden';
        exit;
    }

    public function handle(): void
    {
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            error_log('[MetaWhatsAppWebhook] JSON inválido recibido.');
            $this->json(['success' => false, 'error' => 'invalid_json'], 400);
        }

        try {
            $this->processPayload($payload);
        } catch (Throwable $e) {
            error_log('[MetaWhatsAppWebhook] Error procesando webhook: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'processing_error'], 200);
        }

        $this->json(['success' => true]);
    }

    private function processPayload(array $payload): void
    {
        $logs = new WhatsAppLog();
        foreach (($payload['entry'] ?? []) as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $value = is_array($change['value'] ?? null) ? $change['value'] : [];
                $displayPhone = (string) ($value['metadata']['display_phone_number'] ?? '');
                foreach (($value['statuses'] ?? []) as $status) {
                    if (is_array($status)) {
                        $this->updateOutboundStatus($logs, $status);
                    }
                }
                foreach (($value['messages'] ?? []) as $message) {
                    if (is_array($message)) {
                        $this->storeInboundMessage($logs, $message, $displayPhone);
                    }
                }
            }
        }
    }

    private function updateOutboundStatus(WhatsAppLog $logs, array $status): void
    {
        $messageId = trim((string) ($status['id'] ?? ''));
        if ($messageId === '') {
            return;
        }

        $statusName = strtoupper(trim((string) ($status['status'] ?? '')) ?: 'UNKNOWN');
        $errorMessage = $this->statusErrorMessage($status);
        $updated = $logs->updateByMessageId($messageId, [
            'response_json' => json_encode($status, JSON_UNESCAPED_UNICODE) ?: '{}',
            'status_group' => $this->statusGroup($statusName),
            'status_name' => $statusName,
            'status_description' => $errorMessage ?: $this->statusDescription($statusName),
            'error_message' => $errorMessage,
        ]);

        if (!$updated) {
            error_log('[MetaWhatsAppWebhook] Estado recibido para message_id no registrado: ' . $messageId . ' / ' . $statusName);
        }
    }

    private function storeInboundMessage(WhatsAppLog $logs, array $message, string $displayPhone): void
    {
        $messageId = trim((string) ($message['id'] ?? ''));
        if ($messageId === '') {
            return;
        }

        $text = (string) ($message['text']['body'] ?? '');
        $logs->createInboundMessage([
            'from_number' => (string) ($message['from'] ?? ''),
            'to_number' => $displayPhone,
            'message_id' => $messageId,
            'message_text' => $text,
            'payload_json' => json_encode($message, JSON_UNESCAPED_UNICODE) ?: '{}',
        ]);
    }

    private function statusGroup(string $statusName): string
    {
        return match ($statusName) {
            'SENT' => 'SENT',
            'DELIVERED', 'READ' => 'DELIVERED',
            'FAILED' => 'ERROR',
            default => 'PENDING',
        };
    }

    private function statusDescription(string $statusName): string
    {
        return match ($statusName) {
            'SENT' => 'Meta aceptó y marcó el mensaje como enviado.',
            'DELIVERED' => 'WhatsApp confirmó la entrega al destinatario.',
            'READ' => 'El destinatario leyó el mensaje.',
            'FAILED' => 'WhatsApp informó que el mensaje falló.',
            default => 'Estado recibido desde webhook de Meta WhatsApp.',
        };
    }

    private function statusErrorMessage(array $status): ?string
    {
        $errors = $status['errors'] ?? [];
        if (!is_array($errors) || $errors === []) {
            return null;
        }

        $messages = [];
        foreach ($errors as $error) {
            if (!is_array($error)) {
                continue;
            }
            $code = trim((string) ($error['code'] ?? ''));
            $title = trim((string) ($error['title'] ?? ''));
            $details = trim((string) ($error['error_data']['details'] ?? $error['message'] ?? ''));
            $messages[] = trim(($code !== '' ? '#' . $code . ' ' : '') . $title . ($details !== '' ? ': ' . $details : ''));
        }

        return $messages === [] ? null : implode('; ', $messages);
    }

    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
