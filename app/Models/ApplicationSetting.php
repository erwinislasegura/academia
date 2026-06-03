<?php

final class ApplicationSetting extends Model
{
    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT value FROM application_settings WHERE `key` = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        return $value === false ? $default : (string) $value;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO application_settings (`key`, value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$key, $value]);
    }

    public function admissionSettings(): array
    {
        return [
            'notification_email' => $this->get('admission_notification_email', 'contacto@academiaiquique.cl'),
            'applicant_subject' => $this->get('admission_applicant_success_subject', 'Postulación recibida · Academia Iquique'),
            'applicant_html' => $this->get('admission_applicant_success_html', self::defaultApplicantHtml()),
            'whatsapp_enabled' => $this->get('admission_whatsapp_enabled', '0') === '1',
            'whatsapp_phone_number_id' => $this->get('admission_whatsapp_phone_number_id', ''),
            'whatsapp_access_token' => $this->get('admission_whatsapp_access_token', ''),
            'whatsapp_message_template' => $this->get('admission_whatsapp_message_template', WhatsAppNotifier::defaultAdmissionMessage()),
        ];
    }

    public static function defaultApplicantHtml(): string
    {
        return <<<'HTML'
<p>Mensaje de Admision 2026 Academia Iquique"<br /><br />Nombres del Apoderado: {name-2-first-name}</p>
<p>Apellidos del Apoderado: {name-2-last-name}</p>
<p>Email: {email-1}</p>
<p>Teléfono: {phone-1}</p>
<p><span class="pill auto-tooltip" title="" data-original-title="curso">Curso: </span>{select-1}</p>
<p>Acepto condiciones de envio de información: {consent-1}<br /><br />Este mensaje fue enviado desde {site_url}.</p>
HTML;
    }
}
