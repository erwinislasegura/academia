<?php

final class AdmissionMailer
{
    public static function sendApplicationEmails(array $application, array $settings): bool
    {
        $adminSent = self::sendAdminNotification($application, $settings);
        $applicantSent = self::sendApplicantEmail($application, $settings);

        return $adminSent && $applicantSent;
    }

    public static function sendAdminNotification(array $application, array $settings): bool
    {
        return self::sendHtml(
            $settings['notification_email'],
            'Nueva postulación de admisión 2026',
            self::adminMessage($application),
            $application['email']
        );
    }

    public static function sendApplicantEmail(array $application, array $settings): bool
    {
        return self::sendHtml(
            $application['email'],
            $settings['applicant_subject'],
            self::renderTemplate($settings['applicant_html'], $application),
            $settings['notification_email']
        );
    }

    public static function sendTestEmail(string $to, array $mailConfig): bool
    {
        return self::sendHtml(
            $to,
            'Prueba de correo · Academia Iquique',
            '<p>Este es un correo de prueba para validar la configuración SMTP de Academia Iquique.</p>',
            '',
            $mailConfig
        );
    }

    public static function renderTemplate(string $html, array $application): string
    {
        $replacements = [];
        foreach ($application as $key => $value) {
            $replacements['{{' . $key . '}}'] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        }
        $replacements['{{nombre_apoderado}}'] = trim(($replacements['{{nombres_apoderado}}'] ?? '') . ' ' . ($replacements['{{apellidos_apoderado}}'] ?? ''));

        return strtr($html, $replacements);
    }

    private static function adminMessage(array $application): string
    {
        $rows = [
            'Nombres del apoderado' => $application['nombres_apoderado'],
            'Apellidos del apoderado' => $application['apellidos_apoderado'],
            'Correo electrónico' => $application['email'],
            'Teléfono' => $application['telefono'],
            'Estudiante' => $application['estudiante'],
            'Curso al que postula' => $application['curso'],
            'Mensaje adicional' => $application['mensaje'] ?: 'Sin mensaje adicional',
        ];
        $html = '<h2>Nueva postulación de admisión 2026</h2><table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#e2e8f0">';
        foreach ($rows as $label => $value) {
            $html .= '<tr><th align="left">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</th><td>' . nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')) . '</td></tr>';
        }
        return $html . '</table>';
    }

    private static function sendHtml(string $to, string $subject, string $html, string $replyTo = '', ?array $mailConfig = null): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $mailConfig ??= (new ApplicationSetting())->mailSettings();
        $fromAddress = (string) ($mailConfig['from_address'] ?? 'no-reply@academiaiquique.cl');
        $fromName = (string) ($mailConfig['from_name'] ?? 'Academia Iquique');
        $headers = self::htmlHeaders($fromAddress, $fromName, $replyTo);
        $encodedSubject = self::encodeHeader($subject);

        if (self::shouldSendWithSmtp($mailConfig)) {
            return self::sendWithSmtp($to, $encodedSubject, $html, $headers, $mailConfig);
        }

        return mail($to, $encodedSubject, $html, implode("\r\n", $headers));
    }

    private static function htmlHeaders(string $fromAddress, string $fromName, string $replyTo = ''): array
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::formatAddress($fromAddress, $fromName),
        ];
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return $headers;
    }

    private static function shouldSendWithSmtp(array $mailConfig): bool
    {
        return strtolower((string) ($mailConfig['mailer'] ?? 'mail')) === 'smtp'
            || trim((string) ($mailConfig['host'] ?? '')) !== '';
    }

    private static function sendWithSmtp(string $to, string $subject, string $html, array $headers, array $mailConfig): bool
    {
        $host = trim((string) ($mailConfig['host'] ?? ''));
        $port = (int) ($mailConfig['port'] ?? 587);
        $username = (string) ($mailConfig['username'] ?? '');
        $password = (string) ($mailConfig['password'] ?? '');
        $encryption = strtolower((string) ($mailConfig['encryption'] ?? 'tls'));
        $fromAddress = (string) ($mailConfig['from_address'] ?? 'no-reply@academiaiquique.cl');

        if ($host === '' || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $target = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $socket = @fsockopen($target, $port, $errno, $errstr, 15);
        if (!is_resource($socket)) {
            return false;
        }
        stream_set_timeout($socket, 15);

        $ok = self::smtpExpect($socket, [220])
            && self::smtpCommand($socket, 'EHLO ' . self::smtpHostname(), [250]);

        if ($ok && $encryption === 'tls') {
            $ok = self::smtpCommand($socket, 'STARTTLS', [220])
                && stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
                && self::smtpCommand($socket, 'EHLO ' . self::smtpHostname(), [250]);
        }

        if ($ok && ($username !== '' || $password !== '')) {
            $ok = self::smtpCommand($socket, 'AUTH LOGIN', [334])
                && self::smtpCommand($socket, base64_encode($username), [334])
                && self::smtpCommand($socket, base64_encode($password), [235]);
        }

        $message = implode("\r\n", array_merge($headers, [
            'Subject: ' . $subject,
            'To: ' . $to,
        ])) . "\r\n\r\n" . self::normalizeSmtpBody($html) . "\r\n.";

        $ok = $ok
            && self::smtpCommand($socket, 'MAIL FROM:<' . $fromAddress . '>', [250])
            && self::smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251])
            && self::smtpCommand($socket, 'DATA', [354])
            && self::smtpCommand($socket, $message, [250]);

        self::smtpCommand($socket, 'QUIT', [221]);
        fclose($socket);

        return $ok;
    }

    private static function smtpCommand($socket, string $command, array $expectedCodes): bool
    {
        fwrite($socket, $command . "\r\n");
        return self::smtpExpect($socket, $expectedCodes);
    }

    private static function smtpExpect($socket, array $expectedCodes): bool
    {
        $line = '';
        do {
            $line = fgets($socket, 512);
            if ($line === false) {
                return false;
            }
        } while (isset($line[3]) && $line[3] === '-');

        return in_array((int) substr($line, 0, 3), $expectedCodes, true);
    }

    private static function normalizeSmtpBody(string $html): string
    {
        $body = preg_replace("/\r\n|\r|\n/", "\r\n", $html) ?? $html;
        return preg_replace('/^\./m', '..', $body) ?? $body;
    }

    private static function formatAddress(string $email, string $name): string
    {
        return self::encodeHeader($name) . ' <' . $email . '>';
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function smtpHostname(): string
    {
        return parse_url((string) App::config('app.url'), PHP_URL_HOST) ?: 'localhost';
    }

}
