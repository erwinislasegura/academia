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
        $placeholders = $this->csvPlaceholders((string) ($input['placeholders'] ?? 'Nombre completo del apoderado,Nombre del estudiante,Curso,' . date('d-m-Y')));

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

        try {
            $input = $this->input();
            $to = (string) ($input['to'] ?? '');
            $mode = (string) ($input['send_mode'] ?? 'template');
            $message = trim((string) ($input['message'] ?? ''));
            $settings = (new ApplicationSetting())->admissionSettings();
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
                Session::flash('success', 'Meta recibió correctamente la solicitud de plantilla. ID: ' . (string) $result['message_id'] . '. Esto confirma la aceptación de la API, no la recepción en el teléfono.');
            } else {
                Session::flash('error', $this->failureFlashMessage($result));
            }
        } catch (Throwable $e) {
            error_log('[WhatsAppController] Error al enviar prueba desde configuración: ' . $e->getMessage());
            Session::flash('error', 'No fue posible ejecutar la prueba de WhatsApp: ' . $e->getMessage());
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


    private function sendTemplateWithLanguageRetry(
        MetaWhatsAppService $service,
        string $to,
        string $templateName,
        string $language,
        array $parameters,
        array $metadata
    ): array {
        return $service->sendTemplateMessage($to, trim($templateName), trim($language), $parameters, $metadata);
    }

    private function isTemplateTranslationError(array $result): bool
    {
        $error = (string) ($result['error'] ?? '');

        return (int) ($result['http_code'] ?? 0) === 404
            && (str_contains($error, '#132001') || stripos($error, 'translation') !== false);
    }

    private function testTemplateParameters(array $input, string $templateName): array
    {
        return WhatsAppNotifier::templateParametersFor($templateName, [
            'nombre_apoderado' => trim((string) ($input['guardian_name'] ?? 'Nombre completo del apoderado')),
            'estudiante' => trim((string) ($input['student_name'] ?? 'Nombre del estudiante')),
            'curso' => trim((string) ($input['course'] ?? 'Curso')),
            'fecha_postulacion' => date('Y-m-d'),
        ]);
    }

    private function csvPlaceholders(string $csv): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $csv)), static fn(string $value): bool => $value !== ''));
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

        return 'No fue posible completar la solicitud de WhatsApp de prueba' . ($httpCode > 0 ? ' (HTTP ' . $httpCode . ')' : '') . ': ' . $detail;
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
