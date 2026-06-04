<?php

final class WhatsAppNotifier
{
    private const INFOBIP_TEXT_ENDPOINT = '/whatsapp/1/message/text';

    public static function sendAdmissionMessage(array $application, array $settings): bool
    {
        if (empty($settings['whatsapp_enabled'])) {
            return true;
        }

        $baseUrl = self::normalizeBaseUrl((string) ($settings['whatsapp_base_url'] ?? ''));
        $sender = self::normalizePhone((string) ($settings['whatsapp_sender'] ?? ($settings['whatsapp_phone_number_id'] ?? '')));
        $apiKey = trim((string) ($settings['whatsapp_api_key'] ?? ($settings['whatsapp_access_token'] ?? '')));
        $message = trim(self::renderTemplate((string) ($settings['whatsapp_message_template'] ?? ''), $application));
        $recipient = self::normalizePhone((string) ($application['telefono'] ?? ''));

        if ($baseUrl === '' || $sender === '' || $apiKey === '' || $message === '' || $recipient === '') {
            return false;
        }

        $payload = json_encode([
            'from' => $sender,
            'to' => $recipient,
            'messageId' => self::messageId(),
            'content' => [
                'text' => $message,
            ],
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return false;
        }

        $url = $baseUrl . self::INFOBIP_TEXT_ENDPOINT;

        if (function_exists('curl_init')) {
            return self::sendWithCurl($url, $apiKey, $payload);
        }

        return self::sendWithStream($url, $apiKey, $payload);
    }

    public static function defaultAdmissionMessage(): string
    {
        return 'Hola {{nombres_apoderado}}, recibimos correctamente la postulación de {{estudiante}} para {{curso}}. Nuestro equipo de admisión revisará los antecedentes y se contactará contigo. Academia Iquique';
    }

    private static function renderTemplate(string $template, array $application): string
    {
        $replacements = [];
        foreach ($application as $key => $value) {
            $replacements['{{' . $key . '}}'] = trim((string) $value);
        }
        $replacements['{{nombre_apoderado}}'] = trim(($application['nombres_apoderado'] ?? '') . ' ' . ($application['apellidos_apoderado'] ?? ''));

        return strtr($template, $replacements);
    }

    private static function normalizeBaseUrl(string $baseUrl): string
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

    private static function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if ($hasPlus) {
            return $digits;
        }

        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            return '56' . $digits;
        }

        if (strlen($digits) === 8) {
            return '569' . $digits;
        }

        return $digits;
    }

    private static function messageId(): string
    {
        return bin2hex(random_bytes(16));
    }

    private static function authorizationHeader(string $apiKey): string
    {
        return 'Authorization: App ' . $apiKey;
    }

    private static function sendWithCurl(string $url, string $apiKey, string $payload): bool
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                self::authorizationHeader($apiKey),
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_errno($ch);
        curl_close($ch);

        return $error === 0 && $status >= 200 && $status < 300;
    }

    private static function sendWithStream(string $url, string $apiKey, string $payload): bool
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    self::authorizationHeader($apiKey),
                    'Content-Type: application/json',
                    'Accept: application/json',
                ]),
                'content' => $payload,
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);
        if ($result === false || !isset($http_response_header[0])) {
            return false;
        }

        return preg_match('/^HTTP\/\S+\s+2\d\d\b/', $http_response_header[0]) === 1;
    }
}
