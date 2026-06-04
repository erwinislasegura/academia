<?php

final class WhatsAppController extends Controller
{
    public function testTemplate(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $to = (string) ($input['to'] ?? '');
        $templateName = (string) ($input['template_name'] ?? 'confirmacion_postulacion');
        $language = (string) ($input['language'] ?? 'es');
        $placeholders = $this->csvPlaceholders((string) ($input['placeholders'] ?? 'Juan Pérez,1° Medio,' . date('d-m-Y')));

        $result = (new InfobipWhatsAppService())->sendTemplateMessage(
            $to,
            $templateName,
            $language,
            $placeholders,
            [],
            [],
            ['modulo' => 'whatsapp_test', 'tipo' => 'template']
        );

        $this->json($this->publicResult($result), $result['success'] ? 200 : 422);
    }

    public function testText(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $result = (new InfobipWhatsAppService())->sendTextMessage(
            (string) ($input['to'] ?? ''),
            (string) ($input['text'] ?? 'Mensaje de prueba desde Academia Iquique.'),
            ['modulo' => 'whatsapp_test', 'tipo' => 'texto_24h']
        );

        $this->json($this->publicResult($result), $result['success'] ? 200 : 422);
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

        $service = new InfobipWhatsAppService($settings);
        $template = $service->admissionTemplateConfig($settings);
        $guardianName = trim(($application['guardian_first_names'] ?? '') . ' ' . ($application['guardian_last_names'] ?? ''));
        $createdAt = $application['created_at'] ? date('d-m-Y', strtotime((string) $application['created_at'])) : date('d-m-Y');

        $result = $service->sendTemplateMessage(
            $to,
            $template['name'],
            $template['language'],
            [
                $guardianName,
                (string) ($application['student_name'] ?? ''),
                (string) ($application['course'] ?? ''),
                $createdAt,
            ],
            [],
            [],
            [
                'modulo' => 'postulaciones',
                'registro_id' => $postulacionId,
                'tipo' => 'confirmacion_postulacion',
            ]
        );

        if ($respondJson) {
            $this->json($this->publicResult($result), $result['success'] ? 200 : 422);
        }

        return $result;
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
