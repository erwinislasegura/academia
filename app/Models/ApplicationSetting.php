<?php

final class ApplicationSetting extends Model
{
    private const LEGACY_MAIL_PASSWORDS = [
        'G;bD1;5z_$b1{NF2',
    ];

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


    public function mailSettings(): array
    {
        $defaults = App::config('mail');
        $defaultPassword = (string) ($defaults['password'] ?? '');

        return [
            'mailer' => $this->get('mail_mailer', (string) ($defaults['mailer'] ?? 'smtp')),
            'host' => $this->get('mail_host', (string) ($defaults['host'] ?? '')),
            'port' => (int) $this->get('mail_port', (string) ($defaults['port'] ?? 465)),
            'username' => $this->get('mail_username', (string) ($defaults['username'] ?? '')),
            'password' => $this->currentMailPassword($defaultPassword),
            'encryption' => $this->get('mail_encryption', (string) ($defaults['encryption'] ?? 'ssl')),
            'from_address' => $this->get('mail_from_address', (string) ($defaults['from_address'] ?? 'notificacion@academia.gocreative.cl')),
            'from_name' => $this->get('mail_from_name', (string) ($defaults['from_name'] ?? 'Academia Iquique')),
        ];
    }

    private function currentMailPassword(string $defaultPassword): string
    {
        $storedPassword = $this->get('mail_password', $defaultPassword);
        if ($storedPassword === null || in_array($storedPassword, self::LEGACY_MAIL_PASSWORDS, true)) {
            return $defaultPassword;
        }

        return $storedPassword;
    }

    public function saveMailSettings(array $settings): void
    {
        foreach ([
            'mail_mailer' => 'mailer',
            'mail_host' => 'host',
            'mail_port' => 'port',
            'mail_username' => 'username',
            'mail_password' => 'password',
            'mail_encryption' => 'encryption',
            'mail_from_address' => 'from_address',
            'mail_from_name' => 'from_name',
        ] as $key => $settingKey) {
            $this->set($key, (string) ($settings[$settingKey] ?? ''));
        }
    }

    public function admissionSettings(): array
    {
        $applicantHtml = $this->get('admission_applicant_success_html', self::defaultApplicantHtml());
        if (self::isOutdatedDefaultApplicantHtml($applicantHtml)) {
            $applicantHtml = self::defaultApplicantHtml();
        }

        return [
            'notification_email' => $this->get('admission_notification_email', 'contacto@academiaiquique.cl'),
            'applicant_subject' => $this->get('admission_applicant_success_subject', 'Postulación recibida · Academia Iquique'),
            'applicant_html' => $applicantHtml,
            'whatsapp_enabled' => $this->get('admission_whatsapp_enabled', '1') === '1',
            'whatsapp_base_url' => $this->get('admission_whatsapp_base_url', getenv('INFOBIP_API_BASE_URL') ?: '4k99ym.api.infobip.com'),
            'whatsapp_sender' => $this->get('admission_whatsapp_sender', $this->get('admission_whatsapp_phone_number_id', getenv('INFOBIP_WHATSAPP_SENDER') ?: '56985741931')),
            'whatsapp_api_key' => $this->get('admission_whatsapp_api_key', $this->get('admission_whatsapp_access_token', getenv('INFOBIP_API_KEY') ?: '2e8f648e77b9fc422c1fda84055b99d6-d309d362-1a5c-4aa4-ac86-057666d341f3')),
            'whatsapp_message_template' => $this->get('admission_whatsapp_message_template', WhatsAppNotifier::defaultAdmissionMessage()),
        ];
    }

    public static function defaultApplicantHtml(): string
    {
        return <<<'HTML'
<table class="container" style="max-width: 600px; margin: 0 auto; background: #ffffff;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="padding: 0;"><img class="full-img" style="width: 100%; height: auto; border: 0; text-decoration: none;" src="https://academiaiquique.cl/wp-content/uploads/2025/10/banner.png" alt="Admisión 2026 - Academia Iquique" width="600" /></td>
</tr>
<tr>
<td class="p-24" style="padding: 24px 28px 8px 28px; font-family: Arial, Helvetica, sans-serif; color: #0b2239; font-size: 14px; line-height: 1.6;">
<h2 style="margin: 0 0 12px 0; font-size: 20px; color: #114b8b; font-weight: bold;">Información Admisión 2026 - Academia Iquique</h2>
<p style="margin: 0 0 12px 0;">Hola</p>
<p style="margin: 0 0 12px 0;">Gracias por su interés en postular a nuestro colegio.</p>
<p style="margin: 0 0 12px 0;">El proceso de <strong>admisión 2026</strong> ya se encuentra abierto y permanecerá vigente hasta completar los cupos disponibles por curso. Para continuar con su postulación, le solicitamos completar la ficha en la siguiente plataforma:</p>
<table style="margin: 16px 0px 20px; height: 83px;" role="presentation" border="0" width="290" cellspacing="0" cellpadding="0" align="left">
<tbody>
<tr>
<td style="border-radius: 6px;" bgcolor="#114b8b"><a class="btn" style="padding: 12px 18px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px;" href="https://academiaiquique.postulaciones.colegium.com/" target="_blank" rel="noopener">Completar ficha de postulación</a></td>
</tr>
</tbody>
</table>
<div style="clear: both;"> </div>
<h3 style="margin: 24px 0 10px 0; font-size: 16px; color: #114b8b; border-bottom: 2px solid #114b8b; padding-bottom: 6px;">Información general</h3>
<ul style="padding-left: 18px; margin: 0 0 12px 0;">
<li>Vacantes disponibles desde <strong>Kínder</strong> hasta <strong>8° Básico</strong>.</li>
<li>Cada curso tiene un máximo de <strong>30 estudiantes</strong>.</li>
<li>Matrícula: <strong>$325.000</strong> (1 cuota).</li>
<li>Arancel anual: <strong>$3.250.000</strong> (dividido en 10 cuotas de $325.000).<sup>**</sup></li>
<li><strong>No contamos</strong> con Programa de Integración Escolar (PIE).</li>
</ul>
<p style="margin: 0 0 12px 0;">Ante cualquier consulta, puede escribirnos a <a style="color: #114b8b; text-decoration: none;" href="mailto:admision@academiaiquique.cl">admision@academiaiquique.cl</a> o llamarnos al <a style="color: #114b8b; text-decoration: none;" href="tel:+56985741931">+56 9 85741931</a>.</p>
<p style="margin: 0 0 12px 0;">Adjunto encontrará el <strong>Reglamento de Admisión</strong>, donde se detallan las etapas y fechas del proceso.</p>
</td>
</tr>
<tr>
<td style="padding: 8px 20px 0 20px;"><img class="full-img" style="width: 100%; max-width: 560px; height: auto; margin: 0 auto; border: 0;" src="https://academiaiquique.cl/wp-content/uploads/2025/10/etapas3-1024x723.png" alt="Proceso de Admisión - Etapas" width="560" /></td>
</tr>
<tr>
<td style="padding: 20px 28px 6px 28px; font-family: Arial, Helvetica, sans-serif; color: #0b2239; font-size: 14px; line-height: 1.6;">
<p style="margin: 0 0 12px 0;">Atentamente,</p>
<p style="margin: 0 0 12px 0;"><strong>Equipo de Admisión<br />Academia Iquique</strong></p>
<p style="margin: 0; font-size: 12px; color: #5f6b7a;">** valores corresponden sólo para alumnos nuevos.</p>
</td>
</tr>
<tr>
<td style="padding: 16px 28px 28px 28px; text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #98a2b3;">© 2025 Academia Iquique</td>
</tr>
</tbody>
</table>
HTML;
    }

    private static function isOutdatedDefaultApplicantHtml(?string $html): bool
    {
        return in_array($html, [
            '<p>Hola {{nombres_apoderado}},</p><p>Tu postulación para {{estudiante}} al curso {{curso}} fue recibida exitosamente.</p><p>Nuestro equipo de admisión revisará los antecedentes y se contactará contigo para orientar los próximos pasos.</p><p><strong>Academia Iquique</strong></p>',
            '<p>Mensaje de Admision 2026 Academia Iquique"<br /><br />Nombres del Apoderado: {name-2-first-name}</p>
<p>Apellidos del Apoderado: {name-2-last-name}</p>
<p>Email: {email-1}</p>
<p>Teléfono: {phone-1}</p>
<p><span class="pill auto-tooltip" title="" data-original-title="curso">Curso: </span>{select-1}</p>
<p>Acepto condiciones de envio de información: {consent-1}<br /><br />Este mensaje fue enviado desde {site_url}.</p>',
            '<p>Mensaje de Admision 2026 Academia Iquique"<br /><br />Nombres del Apoderado: {name-2-first-name}</p><p>Apellidos del Apoderado: {name-2-last-name}</p><p>Email: {email-1}</p><p>Teléfono: {phone-1}</p><p><span class="pill auto-tooltip" title="" data-original-title="curso">Curso: </span>{select-1}</p><p>Acepto condiciones de envio de información: {consent-1}<br /><br />Este mensaje fue enviado desde {site_url}.</p>',
        ], true);
    }

}
