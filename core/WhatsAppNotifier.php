<?php

final class WhatsAppNotifier
{
    private const API_VERSION = 'v20.0';

    public static function sendAdmissionMessage(array $application, array $settings): bool
    {
        if (empty($settings['whatsapp_enabled'])) {
            return true;
        }

        $phoneNumberId = trim((string) ($settings['whatsapp_phone_number_id'] ?? ''));
        $accessToken = trim((string) ($settings['whatsapp_access_token'] ?? ''));
        $message = trim(self::renderTemplate((string) ($settings['whatsapp_message_template'] ?? ''), $application));
        $recipient = self::normalizePhone((string) ($application['telefono'] ?? ''));

        if ($phoneNumberId === '' || $accessToken === '' || $message === '' || $recipient === '') {
            return false;
        }

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return false;
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            self::API_VERSION,
            rawurlencode($phoneNumberId)
        );

        if (function_exists('curl_init')) {
            return self::sendWithCurl($url, $accessToken, $payload);
        }

        return self::sendWithStream($url, $accessToken, $payload);
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

    private static function sendWithCurl(string $url, string $accessToken, string $payload): bool
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
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

    private static function sendWithStream(string $url, string $accessToken, string $payload): bool
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
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
