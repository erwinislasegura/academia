<?php

final class AdmissionMailer
{
    public static function sendApplicationEmails(array $application, array $settings): bool
    {
        $adminSent = self::sendHtml(
            $settings['notification_email'],
            'Nueva postulación de admisión 2026',
            self::adminMessage($application),
            $application['email']
        );

        $applicantSent = self::sendHtml(
            $application['email'],
            $settings['applicant_subject'],
            self::renderTemplate($settings['applicant_html'], $application),
            $settings['notification_email']
        );

        return $adminSent && $applicantSent;
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

    private static function sendHtml(string $to, string $subject, string $html, string $replyTo = ''): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: Academia Iquique <no-reply@academiaiquique.cl>',
        ];
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, implode("\r\n", $headers));
    }
}
