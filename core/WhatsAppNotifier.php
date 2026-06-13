<?php

final class WhatsAppNotifier
{
    public static function sendAdmissionMessage(array $application, array $settings): bool
    {
        if (empty($settings['whatsapp_enabled'])) {
            return true;
        }

        $service = new MetaWhatsAppService($settings);
        $metadata = [
            'modulo' => 'postulaciones',
            'registro_id' => $application['id'] ?? null,
            'tipo' => 'confirmacion_postulacion_template',
        ];

        $templateName = trim((string) ($settings['whatsapp_template_name'] ?? ''));
        $templateLanguage = trim((string) ($settings['whatsapp_template_language'] ?? 'es'));
        if ($templateName !== '') {
            $result = $service->sendTemplateMessage(
                (string) ($application['telefono'] ?? ''),
                $templateName,
                $templateLanguage,
                self::admissionTemplateParameters($application),
                $metadata
            );

            if ($result['success']) {
                return true;
            }

            error_log('[WhatsAppNotifier] Falló envío de template WhatsApp Cloud API: ' . (string) ($result['error'] ?? $result['status'] ?? 'error desconocido'));
        }

        $result = $service->sendTextMessage(
            (string) ($application['telefono'] ?? ''),
            trim(self::renderTemplate((string) ($settings['whatsapp_message_template'] ?? self::defaultAdmissionMessage()), $application)),
            array_merge($metadata, ['tipo' => 'admision_texto_libre_numero_formulario'])
        );

        if (!$result['success']) {
            error_log('[WhatsAppNotifier] Falló envío de texto WhatsApp Cloud API: ' . (string) ($result['error'] ?? $result['status'] ?? 'error desconocido'));
        }

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

    private static function admissionTemplateParameters(array $application): array
    {
        return [
            trim(($application['nombres_apoderado'] ?? '') . ' ' . ($application['apellidos_apoderado'] ?? '')),
            (string) ($application['estudiante'] ?? ''),
            (string) ($application['curso'] ?? ''),
            date('d-m-Y'),
        ];
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
