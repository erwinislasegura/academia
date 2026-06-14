<?php

final class WhatsAppController extends Controller
{
    public function testTemplate(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $to = (string) ($input['to'] ?? '');
        $settings = (new ApplicationSetting())->admissionSettings();
        $templateName = (string) ($input['template_name'] ?? ($settings['whatsapp_template_name'] ?? 'admision2027_final'));
        $language = (string) ($input['language'] ?? ($settings['whatsapp_template_language'] ?? 'en_US'));
        $placeholders = $this->isParameterlessTemplate($templateName) ? [] : $this->csvPlaceholders((string) ($input['placeholders'] ?? 'Nombre completo del apoderado,Nombre del estudiante,Curso,' . date('d-m-Y')));

        $service = new MetaWhatsAppService($settings);
        $result = $this->sendTemplateWithLanguageRetry(
            $service,
            $to,
            $templateName !== '' ? $templateName : (string) ($settings['whatsapp_template_name'] ?? 'admision2027_final'),
            $language !== '' ? $language : (string) ($settings['whatsapp_template_language'] ?? 'en_US'),
            $placeholders,
            ['modulo' => 'whatsapp_test', 'tipo' => 'template']
        );

        $this->json($this->publicResult($result), $result['success'] ? 200 : 422);
    }

    public function testText(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $settings = (new ApplicationSetting())->admissionSettings();
        $result = (new MetaWhatsAppService($settings))->sendTextMessage(
            (string) ($input['to'] ?? ''),
            (string) ($input['text'] ?? 'Mensaje de prueba desde Academia Iquique.'),
            ['modulo' => 'whatsapp_test', 'tipo' => 'texto_24h']
        );

        $this->json($this->publicResult($result), $result['success'] ? 200 : 422);
    }


    public function testSettingsMessage(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $to = (string) ($input['to'] ?? '');
        $mode = (string) ($input['send_mode'] ?? 'template');
        $message = trim((string) ($input['message'] ?? ''));
        $settings = $this->testSettings((new ApplicationSetting())->admissionSettings(), $input);
        $service = new MetaWhatsAppService($settings);
        $metadata = ['modulo' => 'whatsapp_test', 'tipo' => 'panel_configuracion_' . ($mode === 'text' ? 'texto' : 'template')];

        if ($mode === 'text') {
            if ($message === '') {
                $message = 'Mensaje de prueba desde el panel de administración de Academia Iquique.';
            }
            $result = $service->sendTextMessage($to, $message, $metadata);
        } else {
            $templateName = (string) ($settings['whatsapp_template_name'] ?? 'admision2027_final');
            $result = $this->sendTemplateWithLanguageRetry(
                $service,
                $to,
                $templateName,
                (string) ($settings['whatsapp_template_language'] ?? 'en_US'),
                $this->testTemplateParameters($input, $templateName),
                $metadata
            );
        }

        if ($result['success']) {
            $delivery = $this->waitForDeliveryConfirmation((string) $result['message_id']);
            if ($delivery['confirmed']) {
                Session::flash('success', 'WhatsApp confirmó el envío de prueba. ID: ' . (string) $result['message_id'] . '. Estado: ' . $delivery['status'] . '. ' . $delivery['description']);
            } else {
                Session::flash('error', 'Meta aceptó la solicitud, pero todavía no confirmó que el WhatsApp haya salido o se haya entregado. ID: ' . (string) $result['message_id'] . '. ' . $delivery['description']);
            }
        } else {
            Session::flash('error', $this->failureFlashMessage($result));
        }

        $this->redirect('/whatsapp-settings');
    }

    public function sendAdmissionConfirmation(int $postulacionId, bool $respondJson = true): array
    {
        if ($respondJson) {
            Middleware::permission('configurar_postulaciones');
        }

        $application = (new AdmissionApplication())->find($postulacionId);
        if ($application === null) {
            $result = [
                'success' => false,
                'http_code' => 404,
                'message_id' => '',
                'status' => 'NOT_FOUND',
                'response' => null,
                'error' => 'Postulación no encontrada.',
            ];
            if ($respondJson) {
                $this->json($this->publicResult($result), 404);
            }
            return $result;
        }

        $to = (string) ($application['guardian_phone'] ?? '');
        if (!InfobipWhatsAppService::isValidPhone($to)) {
            $result = [
                'success' => false,
                'http_code' => 422,
                'message_id' => '',
                'status' => 'INVALID_PHONE',
                'response' => null,
                'error' => 'El teléfono de la postulación no es un celular chileno válido para WhatsApp.',
            ];
            if ($respondJson) {
                $this->json($this->publicResult($result), 422);
            }
            return $result;
        }

        $settings = (new ApplicationSetting())->admissionSettings();
        if (empty($settings['whatsapp_enabled'])) {
            $result = [
                'success' => true,
                'http_code' => 0,
                'message_id' => '',
                'status' => 'DISABLED',
                'response' => null,
                'error' => null,
            ];
            if ($respondJson) {
                $this->json($this->publicResult($result));
            }
            return $result;
        }

        $service = new MetaWhatsAppService($settings);
        $template = [
            'name' => trim((string) ($settings['whatsapp_template_name'] ?? 'admision2027_final')),
            'language' => trim((string) ($settings['whatsapp_template_language'] ?? 'en_US')),
        ];
        $guardianName = WhatsAppNotifier::guardianFullName($application);

        $templateParameters = WhatsAppNotifier::templateParametersFor($template['name'], $application);
        $metadata = [
            'modulo' => 'postulaciones',
            'registro_id' => $postulacionId,
            'tipo' => $template['name'],
        ];
        if ($templateParameters !== []) {
            $metadata['template_variables'] = [
                '{{1}}' => $templateParameters[0],
                '{{2}}' => $templateParameters[1],
                '{{3}}' => $templateParameters[2],
                '{{4}}' => $templateParameters[3],
            ];
        }
        $result = $this->sendTemplateWithLanguageRetry(
            $service,
            $to,
            $template['name'],
            $template['language'],
            $templateParameters,
            $metadata
        );

        if (!$result['success'] && $this->shouldFallbackToText($result)) {
            error_log('[WhatsAppController] Falló template de admisión; se intentará texto libre WhatsApp Cloud API. Estado: ' . $result['status']);
            $result = $service->sendTextMessage(
                $to,
                $this->admissionTextMessage($application, $guardianName),
                $metadata + ['fallback' => 'text_after_template_error']
            );
        }

        if ($respondJson) {
            $this->json($this->publicResult($result), $result['success'] ? 200 : 422);
        }

        return $result;
    }


    private function testSettings(array $settings, array $input): array
    {
        $settings['whatsapp_base_url'] = trim((string) ($input['test_base_url'] ?? '')) ?: (string) ($settings['whatsapp_base_url'] ?? 'https://graph.facebook.com/v25.0');
        $settings['whatsapp_template_name'] = trim((string) ($input['test_template_name'] ?? '')) ?: (string) ($settings['whatsapp_template_name'] ?? 'admision2027_final');
        $settings['whatsapp_template_language'] = trim((string) ($input['test_template_language'] ?? '')) ?: (string) ($settings['whatsapp_template_language'] ?? 'en_US');

        return $settings;
    }

    private function sendTemplateWithLanguageRetry(
        MetaWhatsAppService $service,
        string $to,
        string $templateName,
        string $language,
        array $parameters,
        array $metadata
    ): array {
        $templateName = trim($templateName);
        $language = trim($language);
        if ($this->isParameterlessTemplate($templateName)) {
            return $service->sendTemplateMessage($to, $templateName, $language, [], $metadata);
        }
        $availableLanguages = $service->templateLanguages($templateName);
        if ($availableLanguages !== [] && !in_array($language, $availableLanguages, true)) {
            foreach ($this->preferredTemplateLanguages($language, $availableLanguages) as $availableLanguage) {
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
        if ($result['success'] || !$this->isTemplateTranslationError($result)) {
            return $result;
        }

        foreach ($this->preferredTemplateLanguages($language, $availableLanguages ?: $service->templateLanguages($templateName)) as $availableLanguage) {
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
            $result['error'] = trim((string) ($result['error'] ?? '')) . ' ' . $this->templateDiagnosticMessage($service, $templateName);
        }

        return $result;
    }

    private function templateDiagnosticMessage(MetaWhatsAppService $service, string $templateName): string
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

    private function preferredTemplateLanguages(string $configuredLanguage, array $availableLanguages): array
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

    private function isTemplateTranslationError(array $result): bool
    {
        $error = (string) ($result['error'] ?? '');

        return (int) ($result['http_code'] ?? 0) === 404
            && (str_contains($error, '#132001') || stripos($error, 'translation') !== false);
    }

    private function testTemplateParameters(array $input, string $templateName): array
    {
        if ($this->isParameterlessTemplate($templateName)) {
            return [];
        }


        return WhatsAppNotifier::templateParametersFor($templateName, [
            'nombre_apoderado' => trim((string) ($input['guardian_name'] ?? 'Nombre completo del apoderado')),
            'estudiante' => trim((string) ($input['student_name'] ?? 'Nombre del estudiante')),
            'curso' => trim((string) ($input['course'] ?? 'Curso')),
            'fecha_postulacion' => date('Y-m-d'),
        ]);
    }

    private function isParameterlessTemplate(string $templateName): bool
    {
        return trim($templateName) === 'hello_world';
    }

    private function failureFlashMessage(array $result): string
    {
        $error = trim((string) ($result['error'] ?? ''));
        $status = trim((string) ($result['status'] ?? ''));
        $httpCode = (int) ($result['http_code'] ?? 0);
        $detail = $error !== '' ? $error : ($status !== '' ? $status : 'error desconocido');

        if ($this->isTemplateTranslationError($result)) {
            $detail .= ' Revisa que el nombre del template y su idioma coincidan exactamente con una plantilla aprobada en Meta.';
        }

        return 'No fue posible enviar el WhatsApp de prueba' . ($httpCode > 0 ? ' (HTTP ' . $httpCode . ')' : '') . ': ' . $detail;
    }

    private function shouldFallbackToText(array $result): bool
    {
        return in_array((string) ($result['status'] ?? ''), [
            'TEMPLATE_ERROR',
            'PARAMETER_ERROR',
            'META_ERROR',
        ], true) || in_array((int) ($result['http_code'] ?? 0), [400, 404, 422], true);
    }

    private function admissionTextMessage(array $application, string $guardianName): string
    {
        $template = WhatsAppNotifier::defaultAdmissionMessage();
        $data = [
            'nombres_apoderado' => trim((string) ($application['guardian_first_names'] ?? '')),
            'apellidos_apoderado' => trim((string) ($application['guardian_last_names'] ?? '')),
            'nombre_apoderado' => $guardianName,
            'email' => (string) ($application['guardian_email'] ?? ''),
            'telefono' => (string) ($application['guardian_phone'] ?? ''),
            'estudiante' => (string) ($application['student_name'] ?? ''),
            'curso' => (string) ($application['course'] ?? ''),
            'mensaje' => (string) ($application['message'] ?? ''),
        ];

        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return trim($template);
    }

    private function csvPlaceholders(string $csv): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $csv)), static fn(string $value): bool => $value !== ''));
    }

    private function publicResult(array $result): array
    {
        return [
            'success' => (bool) $result['success'],
            'http_code' => (int) $result['http_code'],
            'message_id' => (string) $result['message_id'],
            'status' => (string) $result['status'],
            'error' => $result['error'],
        ];
    }

    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
