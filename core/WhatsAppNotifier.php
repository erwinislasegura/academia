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
        $templateLanguage = trim((string) ($settings['whatsapp_template_language'] ?? 'en_US'));
        if ($templateName !== '') {
            $result = self::sendTemplateWithLanguageRetry(
                $service,
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

    private static function sendTemplateWithLanguageRetry(
        MetaWhatsAppService $service,
        string $to,
        string $templateName,
        string $language,
        array $parameters,
        array $metadata
    ): array {
        $templateName = trim($templateName);
        $language = trim($language);
        $availableLanguages = $service->templateLanguages($templateName);
        if ($availableLanguages !== [] && !in_array($language, $availableLanguages, true)) {
            foreach (self::preferredTemplateLanguages($language, $availableLanguages) as $availableLanguage) {
                $retry = $service->sendTemplateMessage(
                    $to,
                    $templateName,
                    $availableLanguage,
                    $parameters,
                    $metadata + ['retry_language' => $availableLanguage, 'configured_language' => $language, 'reason' => 'language_preflight']
                );
                if ($retry['success']) {
                    return $retry;
                }
                $result = $retry;
            }

            return $result ?? [
                'success' => false,
                'http_code' => 422,
                'message_id' => '',
                'status' => 'TEMPLATE_LANGUAGE_NOT_APPROVED',
                'response' => null,
                'error' => 'El idioma configurado "' . $language . '" no está aprobado para el template "' . $templateName . '". Idiomas aprobados detectados: ' . implode(', ', $availableLanguages) . '.',
            ];
        }

        $result = $service->sendTemplateMessage($to, $templateName, $language, $parameters, $metadata);
        if ($result['success'] || !self::isTemplateTranslationError($result)) {
            return $result;
        }

        foreach (self::preferredTemplateLanguages($language, $availableLanguages ?: $service->templateLanguages($templateName)) as $availableLanguage) {
            if ($availableLanguage === $language) {
                continue;
            }

            $retry = $service->sendTemplateMessage(
                $to,
                $templateName,
                $availableLanguage,
                $parameters,
                $metadata + ['retry_language' => $availableLanguage, 'configured_language' => $language]
            );
            if ($retry['success']) {
                return $retry;
            }
            $result = $retry;
        }

        if ($availableLanguages === []) {
            $result['error'] = trim((string) ($result['error'] ?? '')) . ' ' . self::templateDiagnosticMessage($service, $templateName);
        }

        return $result;
    }

    private static function templateDiagnosticMessage(MetaWhatsAppService $service, string $templateName): string
    {
        $diagnostics = $service->templateDiagnostics($templateName);
        $exact = array_map(
            static fn(array $item): string => $item['name'] . ' / ' . $item['language'] . ' / ' . ($item['status'] ?: 'sin estado') . ' / WABA ' . $item['business_account_id'],
            array_slice($diagnostics['exact'] ?? [], 0, 5)
        );
        if ($exact !== []) {
            return 'Se encontró el template "' . $templateName . '", pero ninguna traducción aparece como aprobada para envío. Traducciones detectadas: ' . implode('; ', $exact) . '.';
        }

        $similar = array_map(
            static fn(array $item): string => $item['name'] . ' / ' . $item['language'] . ' / ' . ($item['status'] ?: 'sin estado') . ' / WABA ' . $item['business_account_id'],
            array_slice($diagnostics['similar'] ?? [], 0, 5)
        );
        $message = 'No se encontró el template "' . $templateName . '" como plantilla aprobada en el WhatsApp Business Account asociado al número configurado.';
        $phoneWaba = (string) ($diagnostics['phone_business_account_id'] ?? '');
        $configuredWaba = (string) ($diagnostics['configured_business_account_id'] ?? '');
        if ($phoneWaba !== '' && $configuredWaba !== '' && $phoneWaba !== $configuredWaba) {
            $message .= ' El número pertenece al WABA ' . $phoneWaba . ', pero la configuración usa WABA ' . $configuredWaba . '.';
        }
        if ($similar !== []) {
            $message .= ' Plantillas similares detectadas: ' . implode('; ', $similar) . '.';
        }
        if (!empty($diagnostics['errors'])) {
            $message .= ' Diagnóstico Meta: ' . implode('; ', array_slice($diagnostics['errors'], 0, 3)) . '.';
        }

        return $message;
    }

    private static function preferredTemplateLanguages(string $configuredLanguage, array $availableLanguages): array
    {
        $preferred = array_values(array_filter([
            $configuredLanguage,
            str_replace('_', '-', $configuredLanguage),
            str_replace('-', '_', $configuredLanguage),
            preg_replace('/[_-].+$/', '', $configuredLanguage) ?: null,
            'en_US',
            'es_CL',
            'es',
            'es_ES',
        ], static fn($language): bool => is_string($language) && trim($language) !== ''));

        return array_values(array_unique(array_merge(
            array_values(array_intersect($preferred, $availableLanguages)),
            $availableLanguages
        )));
    }

    private static function isTemplateTranslationError(array $result): bool
    {
        $error = (string) ($result['error'] ?? '');

        return (int) ($result['http_code'] ?? 0) === 404
            && (str_contains($error, '#132001') || stripos($error, 'translation') !== false);
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
