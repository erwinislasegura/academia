<?php

final class AdmissionMailer
{
    public static function sendApplicationEmails(array $application, array $settings): bool
    {
        $applicantSent = self::sendApplicantEmail($application, $settings);
        $adminSent = self::sendAdminNotification($application, $settings);

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
        $replacements['{{nombre_apoderado}}'] = htmlspecialchars(
            trim((string) ($application['nombres_apoderado'] ?? '') . ' ' . (string) ($application['apellidos_apoderado'] ?? '')),
            ENT_QUOTES,
            'UTF-8'
        );

        $legacyAliases = [
            '{name-2-first-name}' => 'nombres_apoderado',
            '{name-2-last-name}' => 'apellidos_apoderado',
            '{email-1}' => 'email',
            '{phone-1}' => 'telefono',
            '{select-1}' => 'curso',
            '{consent-1}' => 'autorizacion',
        ];
        foreach ($legacyAliases as $placeholder => $key) {
            $replacements[$placeholder] = htmlspecialchars((string) ($application[$key] ?? ''), ENT_QUOTES, 'UTF-8');
        }
        $replacements['{consent-1}'] = !empty($application['autorizacion']) ? 'Sí' : 'No';
        $replacements['{site_url}'] = htmlspecialchars((string) App::config('app.url'), ENT_QUOTES, 'UTF-8');

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
            self::logFailure('Dirección de destino inválida: ' . $to);
            return false;
        }

        $mailConfig ??= (new ApplicationSetting())->mailSettings();
        $fromAddress = (string) ($mailConfig['from_address'] ?? 'no-reply@academiaiquique.cl');
        $fromName = (string) ($mailConfig['from_name'] ?? 'Academia Iquique');
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            self::logFailure('Dirección remitente inválida: ' . $fromAddress);
            return false;
        }

        $boundary = self::mimeBoundary();
        $messageBody = self::multipartBody($html, $boundary);
        $headers = self::htmlHeaders($fromAddress, $fromName, $replyTo, $boundary);
        $encodedSubject = self::encodeHeader($subject);

        if (self::shouldSendWithSmtp($mailConfig)) {
            $sent = self::sendWithSmtp($to, $encodedSubject, $messageBody, $headers, $mailConfig);
            if ($sent) {
                return true;
            }

            self::logFailure('Se intentará el envío alternativo con mail() para ' . $to . '.');
        }

        return self::sendWithPhpMail($to, $encodedSubject, $messageBody, $headers, $fromAddress);
    }

    private static function htmlHeaders(string $fromAddress, string $fromName, string $replyTo, string $boundary): array
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'From: ' . self::formatAddress($fromAddress, $fromName),
            'Date: ' . date(DATE_RFC2822),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . self::smtpHostname() . '>',
            'X-Mailer: Academia Iquique Admissions',
        ];
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return $headers;
    }

    private static function shouldSendWithSmtp(array $mailConfig): bool
    {
        $mailer = strtolower(trim((string) ($mailConfig['mailer'] ?? '')));
        if ($mailer === '') {
            return trim((string) ($mailConfig['host'] ?? '')) !== '';
        }

        return $mailer === 'smtp';
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
            self::logFailure('Configuración SMTP incompleta: host o remitente inválido.');
            return false;
        }

        $target = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $socket = @stream_socket_client($target . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) {
            self::logFailure(sprintf('No fue posible conectar con SMTP %s:%d (%d: %s).', $host, $port, $errno, $errstr));
            return false;
        }
        stream_set_timeout($socket, 15);

        $ok = self::smtpExpect($socket, [220])
            && self::smtpCommand($socket, 'EHLO ' . self::smtpHostname(), [250]);

        if ($ok && $encryption === 'tls') {
            $ok = self::smtpCommand($socket, 'STARTTLS', [220])
                && @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
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

        if (!$ok) {
            self::logFailure('El servidor SMTP rechazó el envío del correo a ' . $to . '.');
        }

        return $ok;
    }

    private static function sendWithPhpMail(string $to, string $subject, string $body, array $headers, string $fromAddress): bool
    {
        $additionalParams = '-f' . escapeshellarg($fromAddress);
        $sent = mail($to, $subject, $body, implode("\r\n", $headers), $additionalParams);
        if (!$sent) {
            self::logFailure('mail() no pudo enviar el correo a ' . $to . '.');
        }

        return $sent;
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

    private static function multipartBody(string $html, string $boundary): string
    {
        $plainText = self::htmlToText($html);
        $parts = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            self::encodeBody($plainText),
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: quoted-printable',
            '',
            self::encodeBody($html),
            '--' . $boundary . '--',
            '',
        ];

        return implode("\r\n", $parts);
    }

    private static function htmlToText(string $html): string
    {
        $text = preg_replace('/<(br|\/p|\/li|\/tr|\/h[1-6])\b[^>]*>/i', "\n", $html) ?? $html;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    private static function encodeBody(string $body): string
    {
        return quoted_printable_encode($body);
    }

    private static function mimeBoundary(): string
    {
        return '=_academia_' . bin2hex(random_bytes(12));
    }

    private static function logFailure(string $message): void
    {
        error_log('[AdmissionMailer] ' . $message);
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
