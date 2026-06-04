<?php

final class InfobipWhatsAppService
{
    private const TEMPLATE_ENDPOINT = '/whatsapp/1/message/template';
    private const TEXT_ENDPOINT = '/whatsapp/1/message/text';

    private string $baseUrl;
    private string $apiKey;
    private string $sender;
    private string $notifyUrl;
    private int $timeout;
    private WhatsAppLog $logs;

    public function __construct(array $overrides = [], ?WhatsAppLog $logs = null)
    {
        $config = App::config('infobip');
        $this->baseUrl = $this->normalizeBaseUrl((string) ($overrides['whatsapp_base_url'] ?? $overrides['base_url'] ?? $config['base_url'] ?? ''));
        $this->apiKey = trim((string) ($overrides['whatsapp_api_key'] ?? $overrides['api_key'] ?? $config['api_key'] ?? ''));
        $this->sender = self::normalizePhone((string) ($overrides['whatsapp_sender'] ?? $overrides['sender'] ?? $overrides['whatsapp_phone_number_id'] ?? $config['whatsapp_sender'] ?? ''));
        $this->notifyUrl = trim((string) ($overrides['whatsapp_notify_url'] ?? $overrides['notify_url'] ?? $config['notify_url'] ?? ''));
        $this->timeout = max(5, (int) ($overrides['timeout'] ?? $config['timeout'] ?? 15));
        $this->logs = $logs ?? new WhatsAppLog();
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 8) {
            $digits = '569' . $digits;
        } elseif (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            $digits = '56' . $digits;
        }

        return preg_match('/^569\d{8}$/', $digits) ? $digits : '';
    }

    public static function formatPhone(string $phone): string
    {
        $digits = self::normalizePhone($phone);
        if ($digits === '') {
            return trim($phone);
        }

        return sprintf('+56 9 %s %s', substr($digits, 3, 4), substr($digits, 7, 4));
    }

    public static function isValidPhone(string $phone): bool
    {
        return self::normalizePhone($phone) !== '';
    }

    public function sendTemplateMessage(
        string $to,
        string $templateName,
        string $languageCode,
        array $bodyPlaceholders = [],
        array $headerPlaceholders = [],
        array $buttonPlaceholders = [],
        array $metadata = []
    ): array {
        $messageId = $this->generateMessageId('tpl');
        $recipient = self::normalizePhone($to);
        $callbackData = $this->callbackData($metadata);

        $payload = [
            'from' => $this->sender,
            'to' => $recipient,
            'messageId' => $messageId,
            'content' => [
                'templateName' => trim($templateName),
                'templateData' => $this->templateData($bodyPlaceholders, $headerPlaceholders, $buttonPlaceholders),
                'language' => trim($languageCode),
            ],
            'callbackData' => $callbackData,
        ];
        if ($this->notifyUrl !== '') {
            $payload['notifyUrl'] = $this->notifyUrl;
        }

        $validationError = $this->validateCommon($recipient);
        if ($validationError === null && trim($templateName) === '') {
            $validationError = 'El nombre del template de WhatsApp está vacío.';
        }
        if ($validationError === null && trim($languageCode) === '') {
            $validationError = 'El idioma del template de WhatsApp está vacío.';
        }

        return $this->send(self::TEMPLATE_ENDPOINT, $payload, 'template', trim($templateName), $metadata, $validationError);
    }

    public function sendTextMessage(string $to, string $text, array $metadata = []): array
    {
        // Este método solo debe usarse si el usuario ya escribió al negocio dentro de las últimas 24 horas.
        // Para iniciar conversación o enviar mensajes fuera de esa ventana se deben usar templates aprobados.
        $messageId = $this->generateMessageId('txt');
        $recipient = self::normalizePhone($to);
        $callbackData = $this->callbackData($metadata);

        $payload = [
            'from' => $this->sender,
            'to' => $recipient,
            'messageId' => $messageId,
            'content' => [
                'text' => trim($text),
            ],
            'callbackData' => $callbackData,
        ];
        if ($this->notifyUrl !== '') {
            $payload['notifyUrl'] = $this->notifyUrl;
        }

        $validationError = $this->validateCommon($recipient);
        if ($validationError === null && trim($text) === '') {
            $validationError = 'El texto de WhatsApp está vacío.';
        }

        return $this->send(self::TEXT_ENDPOINT, $payload, 'text', null, $metadata, $validationError);
    }

    public function admissionTemplateConfig(array $settings = []): array
    {
        $config = App::config('infobip');

        return [
            'name' => trim((string) ($settings['whatsapp_template_name'] ?? $config['admission_template_name'] ?? 'confirmacion_postulacion')),
            'language' => trim((string) ($settings['whatsapp_template_language'] ?? $config['admission_template_language'] ?? 'es')),
        ];
    }

    private function send(string $endpoint, array $payload, string $messageType, ?string $templateName, array $metadata, ?string $validationError): array
    {
        $messageId = (string) $payload['messageId'];
        $logId = $this->createLog($payload, $messageType, $templateName, $metadata, $validationError);

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

        $raw = '';
        $httpCode = 0;
        $curlError = null;
        $url = $this->baseUrl . $endpoint;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                $curlError = 'No fue posible inicializar cURL.';
            } else {
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: App ' . $this->apiKey,
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
                if (curl_errno($ch) !== 0) {
                    $curlError = curl_error($ch) ?: 'Error de conexión con Infobip.';
                }
                curl_close($ch);
            }
        } else {
            [$raw, $httpCode, $curlError] = $this->sendWithStream($url, $json);
        }

        $decoded = $this->decodeResponse($raw);
        $responseMessageId = $this->extractMessageId($decoded) ?: $messageId;
        $success = $curlError === null && in_array($httpCode, [200, 201], true) && is_array($decoded);
        $error = $this->errorMessage($success, $httpCode, $curlError, $decoded, $raw);
        $status = $success ? 'SENT' : $this->statusFromError($httpCode, $error);

        $result = $this->standardResult($success, $httpCode, $responseMessageId, $status, $decoded, $error);
        $this->updateLog($messageId, $result, $logId, $responseMessageId);

        return $result;
    }

    private function validateCommon(string $recipient): ?string
    {
        if ($this->baseUrl === '') {
            return 'URL base de Infobip no configurada.';
        }
        if ($this->apiKey === '') {
            return 'API Key de Infobip no configurada.';
        }
        if ($this->sender === '') {
            return 'Sender de WhatsApp no configurado o inválido.';
        }
        if ($recipient === '') {
            return 'Destinatario WhatsApp inválido. Debe ser un celular chileno en formato 569XXXXXXXX.';
        }

        return null;
    }

    private function templateData(array $bodyPlaceholders, array $headerPlaceholders, array $buttonPlaceholders): array
    {
        $data = [];
        if ($bodyPlaceholders !== []) {
            $data['body'] = ['placeholders' => array_values(array_map('strval', $bodyPlaceholders))];
        }
        if ($headerPlaceholders !== []) {
            $data['header'] = ['placeholders' => array_values(array_map('strval', $headerPlaceholders))];
        }
        if ($buttonPlaceholders !== []) {
            $data['buttons'] = array_values(array_map(
                static fn($value): array => is_array($value) ? $value : ['type' => 'QUICK_REPLY', 'parameter' => (string) $value],
                $buttonPlaceholders
            ));
        }

        return $data;
    }

    private function normalizeBaseUrl(string $baseUrl): string
    {
        $baseUrl = trim($baseUrl);
        if ($baseUrl === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $baseUrl)) {
            $baseUrl = 'https://' . $baseUrl;
        }

        return rtrim($baseUrl, '/');
    }

    private function callbackData(array $metadata): string
    {
        $json = json_encode($metadata, JSON_UNESCAPED_UNICODE);
        return $json === false ? '{}' : $json;
    }

    private function createLog(array $payload, string $messageType, ?string $templateName, array $metadata, ?string $error): ?int
    {
        try {
            return $this->logs->create([
                'message_id' => (string) $payload['messageId'],
                'to_number' => (string) $payload['to'],
                'template_name' => $templateName,
                'message_type' => $messageType,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE) ?: '{}',
                'response_json' => null,
                'status_group' => $error === null ? 'PENDING' : 'ERROR',
                'status_name' => $error === null ? 'PENDING' : 'VALIDATION_ERROR',
                'status_description' => $error === null ? 'Mensaje preparado para Infobip.' : $error,
                'callback_data' => (string) ($payload['callbackData'] ?? '{}'),
                'related_module' => $metadata['modulo'] ?? $metadata['module'] ?? null,
                'related_id' => $metadata['registro_id'] ?? $metadata['related_id'] ?? null,
                'error_message' => $error,
            ]);
        } catch (Throwable $e) {
            error_log('[InfobipWhatsAppService] No fue posible crear log WhatsApp: ' . $e->getMessage());
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
                'status_description' => $result['success'] ? 'Infobip aceptó el mensaje.' : (string) $result['error'],
                'error_message' => $result['error'],
            ]);
        } catch (Throwable $e) {
            error_log('[InfobipWhatsAppService] No fue posible actualizar log WhatsApp' . ($logId ? ' #' . $logId : '') . ': ' . $e->getMessage());
        }
    }

    private function sendWithStream(string $url, string $json): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Authorization: App ' . $this->apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ]),
                'content' => $json,
                'timeout' => $this->timeout,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false || !isset($http_response_header[0])) {
            return ['', 0, 'No fue posible conectar con Infobip.'];
        }

        preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $http_response_header[0], $matches);
        return [(string) $raw, (int) ($matches[1] ?? 0), null];
    }

    private function decodeResponse(string $raw): ?array
    {
        if (trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function extractMessageId(?array $response): ?string
    {
        if ($response === null) {
            return null;
        }
        if (!empty($response['messageId'])) {
            return (string) $response['messageId'];
        }
        if (!empty($response['messages'][0]['messageId'])) {
            return (string) $response['messages'][0]['messageId'];
        }

        return null;
    }

    private function errorMessage(bool $success, int $httpCode, ?string $curlError, ?array $decoded, string $raw): ?string
    {
        if ($success) {
            return null;
        }
        if ($curlError !== null) {
            return $curlError;
        }
        if ($decoded === null) {
            return 'Infobip devolvió una respuesta JSON inválida o vacía. HTTP ' . $httpCode . '. Respuesta: ' . substr(trim($raw), 0, 500);
        }
        if (!empty($decoded['requestError']['serviceException']['text'])) {
            return (string) $decoded['requestError']['serviceException']['text'];
        }
        if (!empty($decoded['requestError']['serviceException']['messageId'])) {
            return 'Infobip rechazó la solicitud: ' . $decoded['requestError']['serviceException']['messageId'];
        }
        if ($httpCode === 401 || $httpCode === 403) {
            return 'Infobip rechazó la autenticación o autorización.';
        }
        if ($httpCode === 400) {
            return 'Infobip rechazó parámetros del template o del mensaje.';
        }

        return 'Infobip no confirmó el envío. HTTP ' . $httpCode . '.';
    }

    private function statusFromError(int $httpCode, ?string $error): string
    {
        if ($httpCode === 401 || $httpCode === 403) {
            return 'AUTH_ERROR';
        }
        if ($httpCode === 400 && $error !== null && stripos($error, 'template') !== false) {
            return 'TEMPLATE_ERROR';
        }
        if ($httpCode === 400) {
            return 'PARAMETER_ERROR';
        }
        if ($httpCode === 0) {
            return 'CONNECTION_ERROR';
        }

        return 'INFOBIP_ERROR';
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

    private function generateMessageId(string $prefix): string
    {
        return $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(8));
    }
}
