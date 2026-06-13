<?php

final class MetaWhatsAppService
{
    private const GRAPH_BASE_URL = 'https://graph.facebook.com';
    private const GRAPH_VERSION = 'v20.0';

    private string $baseUrl;
    private string $phoneNumberId;
    private string $accessToken;
    private int $timeout;
    private WhatsAppLog $logs;

    public function __construct(array $overrides = [], ?WhatsAppLog $logs = null)
    {
        $config = App::config('infobip');
        $this->baseUrl = rtrim((string) ($overrides['meta_whatsapp_base_url'] ?? $overrides['whatsapp_base_url'] ?? getenv('META_WHATSAPP_BASE_URL') ?: self::GRAPH_BASE_URL), '/');
        if ($this->baseUrl === '' || stripos($this->baseUrl, 'infobip.com') !== false) {
            $this->baseUrl = self::GRAPH_BASE_URL;
        }
        $version = trim((string) ($overrides['meta_whatsapp_graph_version'] ?? getenv('META_WHATSAPP_GRAPH_VERSION') ?: self::GRAPH_VERSION));
        if ($version !== '' && !str_ends_with($this->baseUrl, '/' . $version) && preg_match('#graph\.facebook\.com$#i', $this->baseUrl)) {
            $this->baseUrl .= '/' . $version;
        }
        $this->phoneNumberId = preg_replace('/\D+/', '', (string) ($overrides['whatsapp_phone_number_id'] ?? getenv('META_WHATSAPP_PHONE_NUMBER_ID') ?: $config['meta_phone_number_id'] ?? '')) ?? '';
        $this->accessToken = trim((string) ($overrides['whatsapp_access_token'] ?? $overrides['whatsapp_api_key'] ?? getenv('META_WHATSAPP_ACCESS_TOKEN') ?: $config['meta_access_token'] ?? ''));
        $this->timeout = max(5, (int) ($overrides['timeout'] ?? $config['timeout'] ?? 15));
        $this->logs = $logs ?? new WhatsAppLog();
    }

    public function sendTextMessage(string $to, string $text, array $metadata = []): array
    {
        $messageId = $this->generateMessageId();
        $recipient = InfobipWhatsAppService::normalizePhone($to);
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => trim($text),
            ],
        ];

        $validationError = $this->validate($recipient, trim($text));
        return $this->send($payload, $messageId, $metadata, $validationError);
    }

    private function validate(string $recipient, string $text): ?string
    {
        if ($this->phoneNumberId === '') {
            return 'Phone Number ID de WhatsApp Cloud API no configurado.';
        }
        if ($this->accessToken === '') {
            return 'Token de acceso de WhatsApp Cloud API no configurado.';
        }
        if ($recipient === '') {
            return 'Destinatario WhatsApp inválido. Debe ser un celular chileno en formato 569XXXXXXXX.';
        }
        if ($text === '') {
            return 'El texto de WhatsApp está vacío.';
        }

        return null;
    }

    private function send(array $payload, string $messageId, array $metadata, ?string $validationError): array
    {
        $logId = $this->createLog($payload, $messageId, $metadata, $validationError);
        if ($validationError !== null) {
            $result = $this->standardResult(false, 0, $messageId, 'VALIDATION_ERROR', null, $validationError);
            $this->updateLog($messageId, $result, $logId);
            return $result;
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $result = $this->standardResult(false, 0, $messageId, 'JSON_ERROR', null, 'No fue posible codificar el payload JSON.');
            $this->updateLog($messageId, $result, $logId);
            return $result;
        }

        [$raw, $httpCode, $transportError] = $this->post($this->baseUrl . '/' . $this->phoneNumberId . '/messages', $json);
        $decoded = $this->decodeResponse($raw);
        $responseMessageId = $decoded['messages'][0]['id'] ?? $messageId;
        $success = $transportError === null && in_array($httpCode, [200, 201], true) && is_array($decoded);
        $error = $this->errorMessage($success, $httpCode, $transportError, $decoded, $raw);
        $result = $this->standardResult($success, $httpCode, (string) $responseMessageId, $success ? 'SENT' : $this->statusFromError($httpCode), $decoded, $error);
        $this->updateLog($messageId, $result, $logId, (string) $responseMessageId);

        return $result;
    }

    private function post(string $url, string $json): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return ['', 0, 'No fue posible inicializar cURL.'];
            }
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->accessToken,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
            ]);
            $response = curl_exec($ch);
            $raw = is_string($response) ? $response : '';
            $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_errno($ch) !== 0 ? (curl_error($ch) ?: 'Error de conexión con WhatsApp Cloud API.') : null;
            curl_close($ch);
            return [$raw, $httpCode, $error];
        }

        $context = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
                'Accept: application/json',
            ]),
            'content' => $json,
            'timeout' => $this->timeout,
            'ignore_errors' => true,
        ]]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false || !isset($http_response_header[0])) {
            return ['', 0, 'No fue posible conectar con WhatsApp Cloud API.'];
        }
        preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $http_response_header[0], $matches);
        return [(string) $raw, (int) ($matches[1] ?? 0), null];
    }

    private function createLog(array $payload, string $messageId, array $metadata, ?string $error): ?int
    {
        try {
            return $this->logs->create([
                'message_id' => $messageId,
                'to_number' => (string) ($payload['to'] ?? ''),
                'template_name' => null,
                'message_type' => 'meta_text',
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE) ?: '{}',
                'response_json' => null,
                'status_group' => $error === null ? 'PENDING' : 'ERROR',
                'status_name' => $error === null ? 'PENDING' : 'VALIDATION_ERROR',
                'status_description' => $error === null ? 'Mensaje preparado para WhatsApp Cloud API.' : $error,
                'callback_data' => json_encode($metadata, JSON_UNESCAPED_UNICODE) ?: '{}',
                'related_module' => $metadata['modulo'] ?? $metadata['module'] ?? null,
                'related_id' => $metadata['registro_id'] ?? $metadata['related_id'] ?? null,
                'error_message' => $error,
            ]);
        } catch (Throwable $e) {
            error_log('[MetaWhatsAppService] No fue posible crear log WhatsApp: ' . $e->getMessage());
            return null;
        }
    }

    private function updateLog(string $messageId, array $result, ?int $logId, ?string $responseMessageId = null): void
    {
        try {
            $this->logs->updateByMessageId($messageId, [
                'message_id' => $responseMessageId ?: $messageId,
                'response_json' => json_encode($result['response'], JSON_UNESCAPED_UNICODE) ?: null,
                'status_group' => $result['success'] ? 'SENT' : 'ERROR',
                'status_name' => (string) $result['status'],
                'status_description' => $result['success'] ? 'WhatsApp Cloud API aceptó el mensaje.' : (string) $result['error'],
                'error_message' => $result['error'],
            ]);
        } catch (Throwable $e) {
            error_log('[MetaWhatsAppService] No fue posible actualizar log WhatsApp' . ($logId ? ' #' . $logId : '') . ': ' . $e->getMessage());
        }
    }

    private function decodeResponse(string $raw): ?array
    {
        if (trim($raw) === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function errorMessage(bool $success, int $httpCode, ?string $transportError, ?array $decoded, string $raw): ?string
    {
        if ($success) {
            return null;
        }
        if ($transportError !== null) {
            return $transportError;
        }
        if (!empty($decoded['error']['message'])) {
            return (string) $decoded['error']['message'];
        }
        return 'WhatsApp Cloud API no confirmó el envío. HTTP ' . $httpCode . '. Respuesta: ' . substr(trim($raw), 0, 500);
    }

    private function statusFromError(int $httpCode): string
    {
        if (in_array($httpCode, [401, 403], true)) {
            return 'AUTH_ERROR';
        }
        if ($httpCode === 400) {
            return 'PARAMETER_ERROR';
        }
        if ($httpCode === 0) {
            return 'CONNECTION_ERROR';
        }
        return 'META_ERROR';
    }

    private function standardResult(bool $success, int $httpCode, string $messageId, string $status, ?array $response, ?string $error): array
    {
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'message_id' => $messageId,
            'status' => $status,
            'response' => $response,
            'error' => $error,
        ];
    }

    private function generateMessageId(): string
    {
        return 'meta-' . date('YmdHis') . '-' . bin2hex(random_bytes(8));
    }
}
