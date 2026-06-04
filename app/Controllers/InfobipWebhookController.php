<?php

final class InfobipWebhookController extends Controller
{
    public function handle(): void
    {
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            error_log('[InfobipWebhook] JSON inválido recibido.');
            $this->json(['success' => false, 'error' => 'invalid_json'], 400);
        }

        try {
            $this->processPayload($payload, $raw);
        } catch (Throwable $e) {
            error_log('[InfobipWebhook] Error procesando webhook: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'processing_error'], 200);
        }

        $this->json(['success' => true]);
    }

    private function processPayload(array $payload, string $raw): void
    {
        $events = $this->events($payload);
        $logs = new WhatsAppLog();

        foreach ($events as $event) {
            $messageId = $this->messageId($event);
            if ($messageId !== '') {
                $updated = $logs->updateByMessageId($messageId, [
                    'response_json' => $raw,
                    'status_group' => $this->statusValue($event, 'groupName') ?: $this->statusValue($event, 'groupId') ?: $this->eventType($event),
                    'status_name' => $this->statusValue($event, 'name') ?: $this->eventType($event),
                    'status_description' => $this->statusValue($event, 'description') ?: 'Webhook recibido desde Infobip.',
                    'error_message' => null,
                ]);

                if (!$updated) {
                    $logs->create([
                        'message_id' => $messageId,
                        'to_number' => (string) ($event['to'] ?? $event['receiver'] ?? $event['from'] ?? ''),
                        'template_name' => null,
                        'message_type' => 'webhook',
                        'payload_json' => '{}',
                        'response_json' => $raw,
                        'status_group' => $this->statusValue($event, 'groupName') ?: $this->eventType($event),
                        'status_name' => $this->statusValue($event, 'name') ?: $this->eventType($event),
                        'status_description' => $this->statusValue($event, 'description') ?: 'Webhook recibido sin log previo.',
                        'callback_data' => null,
                        'related_module' => null,
                        'related_id' => null,
                        'error_message' => null,
                    ]);
                }
            }

            if ($this->isInbound($event)) {
                $this->storeInbound($logs, $event, $raw);
            }
        }
    }

    private function events(array $payload): array
    {
        foreach (['results', 'messages', 'events'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $this->isList($payload[$key]) ? $payload[$key] : [$payload[$key]];
            }
        }

        return [$payload];
    }

    private function isList(array $items): bool
    {
        if ($items === []) {
            return true;
        }

        return array_keys($items) === range(0, count($items) - 1);
    }

    private function messageId(array $event): string
    {
        foreach (['messageId', 'message_id', 'bulkId'] as $key) {
            if (!empty($event[$key])) {
                return (string) $event[$key];
            }
        }
        if (!empty($event['message']['id'])) {
            return (string) $event['message']['id'];
        }

        return '';
    }

    private function statusValue(array $event, string $key): string
    {
        if (!empty($event['status'][$key])) {
            return (string) $event['status'][$key];
        }
        if (!empty($event[$key])) {
            return (string) $event[$key];
        }

        return '';
    }

    private function eventType(array $event): string
    {
        foreach (['event', 'eventType', 'type'] as $key) {
            if (!empty($event[$key])) {
                return (string) $event[$key];
            }
        }

        return 'WEBHOOK_RECEIVED';
    }

    private function isInbound(array $event): bool
    {
        $type = strtoupper($this->eventType($event));
        return in_array($type, ['INBOUND_MESSAGE', 'MO', 'MESSAGE_RECEIVED'], true)
            || isset($event['message']['text'])
            || isset($event['text']);
    }

    private function storeInbound(WhatsAppLog $logs, array $event, string $raw): void
    {
        try {
            $logs->createInboundMessage([
                'from_number' => $event['from'] ?? $event['sender'] ?? null,
                'to_number' => $event['to'] ?? $event['receiver'] ?? null,
                'message_id' => $this->messageId($event) ?: null,
                'message_text' => $event['message']['text'] ?? $event['text'] ?? $event['content']['text'] ?? null,
                'payload_json' => $raw,
            ]);
        } catch (Throwable $e) {
            error_log('[InfobipWebhook] No fue posible guardar mensaje entrante: ' . $e->getMessage());
        }
    }

    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
