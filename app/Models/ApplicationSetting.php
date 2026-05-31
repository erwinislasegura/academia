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
        return '<p>Hola {{nombres_apoderado}},</p><p>Tu postulación para {{estudiante}} al curso {{curso}} fue recibida exitosamente.</p><p>Nuestro equipo de admisión revisará los antecedentes y se contactará contigo para orientar los próximos pasos.</p><p><strong>Academia Iquique</strong></p>';
    }
}
