<?php

final class WhatsAppNotifier
{
    public static function sendAdmissionMessage(array $application, array $settings): bool
    {
        return self::sendAdmissionMessageResult($application, $settings)['success'];
    }

    public static function sendAdmissionMessageResult(array $application, array $settings): array
    {
        if (empty($settings['whatsapp_enabled'])) {
            return [
                'success' => true,
                'http_code' => 0,
                'message_id' => '',
                'status' => 'DISABLED',
                'response' => null,
                'error' => null,
            ];
        }

        $service = new MetaWhatsAppService($settings);
        $metadata = [
            'modulo' => 'postulaciones',
            'registro_id' => $application['id'] ?? null,
            'tipo' => 'confirmacion_postulacion_template',
        ];

        $templateName = trim((string) ($settings['whatsapp_template_name'] ?? ''));
        $templateLanguage = trim((string) ($settings['whatsapp_template_language'] ?? 'en_US'));
        $templateParameters = self::templateParametersFor($templateName, $application);
        if ($templateParameters !== []) {
            $metadata['template_variables'] = [
                '{{1}}' => $templateParameters[0],
                '{{2}}' => $templateParameters[1],
                '{{3}}' => $templateParameters[2],
                '{{4}}' => $templateParameters[3],
            ];
        }
        if ($templateName !== '') {
            $result = self::sendTemplateWithLanguageRetry(
                $service,
                (string) ($application['telefono'] ?? ''),
                $templateName,
                $templateLanguage,
                $templateParameters,
                $metadata
            );

            if ($result['success']) {
                return $result;
            }

            error_log('[WhatsAppNotifier] Falló envío de template WhatsApp Cloud API: ' . (string) ($result['error'] ?? $result['status'] ?? 'error desconocido'));
            if (!self::shouldFallbackToText($result)) {
                return $result;
            }
        }

        $result = $service->sendTextMessage(
            (string) ($application['telefono'] ?? ''),
            trim(self::renderTemplate((string) ($settings['whatsapp_message_template'] ?? self::defaultAdmissionMessage()), $application)),
            array_merge($metadata, ['tipo' => 'admision_texto_libre_numero_formulario'])
        );

        if (!$result['success']) {
            error_log('[WhatsAppNotifier] Falló envío de texto WhatsApp Cloud API: ' . (string) ($result['error'] ?? $result['status'] ?? 'error desconocido'));
        }

        return $result;
    }

    private static function shouldFallbackToText(array $result): bool
    {
        return in_array((string) ($result['status'] ?? ''), [
            'TEMPLATE_ERROR',
            'PARAMETER_ERROR',
            'META_ERROR',
        ], true) || in_array((int) ($result['http_code'] ?? 0), [400, 404, 422], true);
    }

    private static function sendTemplateWithLanguageRetry(
        MetaWhatsAppService $service,
        string $to,
        string $templateName,
        string $language,
        array $parameters,
        array $metadata
    ): array {
        return $service->sendTemplateMessage($to, trim($templateName), trim($language), [], $metadata);
    }

    public static function defaultAdmissionMessage(): string
    {
        return 'Hola {{nombres_apoderado}}, confirmamos la recepción de la postulación de {{estudiante}} para {{curso}}. Nuestro equipo de admisión revisará la información enviada y se contactará contigo si requiere antecedentes adicionales o para informar los próximos pasos. Academia Iquique';
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

    public static function templateParametersFor(string $templateName, array $application): array
    {
        return [];
    }

    public static function guardianFullName(array $application): string
    {
        return trim((string) ($application['nombre_apoderado'] ?? '')
            ?: trim((string) ($application['nombres_apoderado'] ?? $application['guardian_first_names'] ?? '') . ' ' . (string) ($application['apellidos_apoderado'] ?? $application['guardian_last_names'] ?? '')));
    }

    public static function studentName(array $application): string
    {
        return trim((string) ($application['estudiante'] ?? $application['student_name'] ?? ''));
    }

    public static function courseName(array $application): string
    {
        return trim((string) ($application['curso'] ?? $application['course'] ?? ''));
    }

    public static function applicationDate(array $application): string
    {
        $rawDate = trim((string) ($application['fecha_postulacion'] ?? $application['created_at'] ?? ''));
        $timestamp = $rawDate !== '' ? strtotime($rawDate) : false;

        return date('d-m-Y', $timestamp ?: time());
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
