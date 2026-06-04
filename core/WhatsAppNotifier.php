<?php

final class WhatsAppNotifier
{
    public static function sendAdmissionMessage(array $application, array $settings): bool
    {
        if (empty($settings['whatsapp_enabled'])) {
            return true;
        }

        $result = (new InfobipWhatsAppService($settings))->sendTextMessage(
            (string) ($application['telefono'] ?? ''),
            trim(self::renderTemplate((string) ($settings['whatsapp_message_template'] ?? self::defaultAdmissionMessage()), $application)),
            [
                'modulo' => 'postulaciones',
                'tipo' => 'admision_texto_libre',
            ]
        );

        return (bool) $result['success'];
    }

    public static function defaultAdmissionMessage(): string
    {
        return 'Hola {{nombres_apoderado}}, recibimos correctamente la postulación de {{estudiante}} para {{curso}}. Nuestro equipo de admisión revisará los antecedentes y se contactará contigo. Academia Iquique';
    }

    public static function normalizeRecipientPhone(string $phone): string
    {
        return InfobipWhatsAppService::normalizePhone($phone);
    }

    public static function isValidRecipientPhone(string $phone): bool
    {
        return InfobipWhatsAppService::isValidPhone($phone);
    }

    public static function formatRecipientPhone(string $phone): string
    {
        return InfobipWhatsAppService::formatPhone($phone);
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
}
