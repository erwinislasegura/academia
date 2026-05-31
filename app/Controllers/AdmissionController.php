<?php

final class AdmissionController extends Controller
{
    private const COURSES = [
        'kinder' => 'Kínder',
        '1-basico' => '1º Básico',
        '2-basico' => '2º Básico',
        '3-basico' => '3º Básico',
        '4-basico' => '4º Básico',
        '5-basico' => '5º Básico',
        '6-basico' => '6º Básico',
        '7-basico' => '7º Básico',
        '8-basico' => '8º Básico',
    ];

    public function show(): void
    {
        $this->view('admissions/postula', [
            'old' => [],
            'errors' => [],
            'success' => false,
            'successMessageHtml' => '',
        ], null);
    }

    public function submit(): void
    {
        $input = $this->input();
        $errors = $this->validate($input);

        if (!empty($input['website'])) {
            $errors[] = 'No fue posible procesar la postulación. Inténtalo nuevamente.';
        }

        if ($errors) {
            $this->view('admissions/postula', [
                'old' => $input,
                'errors' => $errors,
                'success' => false,
                'successMessageHtml' => '',
            ], null);
            return;
        }

        $application = $this->normalize($input);
        (new AdmissionApplication())->create($application);
        $settings = (new ApplicationSetting())->admissionSettings();
        $mailSent = AdmissionMailer::sendApplicationEmails($application, $settings);
        $whatsAppSent = WhatsAppNotifier::sendAdmissionMessage($application, $settings);

        $message = AdmissionMailer::renderTemplate($settings['applicant_html'], $application);
        if (!$mailSent) {
            $message .= '<p><small>Tu postulación quedó registrada, pero el servidor no pudo confirmar el envío de correo automático. Nuestro equipo igualmente revisará tu solicitud.</small></p>';
        }
        if (!$whatsAppSent) {
            $message .= '<p><small>Tu postulación quedó registrada, pero no fue posible confirmar el envío automático por WhatsApp. Nuestro equipo igualmente podrá contactarte por los medios autorizados.</small></p>';
        }

        $this->view('admissions/postula', [
            'old' => [],
            'errors' => [],
            'success' => true,
            'successMessageHtml' => $message,
        ], null);
    }


    public function applications(): void
    {
        Middleware::permission('configurar_postulaciones');
        $model = new AdmissionApplication();
        $this->view('admissions/applications', [
            'title' => 'Postulaciones recibidas',
            'applications' => $model->all(),
            'totalApplications' => $model->count(),
        ]);
    }

    public function exportApplications(): void
    {
        Middleware::permission('configurar_postulaciones');
        $applications = (new AdmissionApplication())->all();
        $filename = 'postulaciones-' . date('Y-m-d') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<table border="1">';
        echo '<thead><tr>';
        foreach (['ID', 'Fecha', 'Apoderado', 'Email', 'Teléfono', 'Estudiante', 'Curso', 'Mensaje'] as $heading) {
            echo '<th>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($applications as $application) {
            $guardian = trim(($application['guardian_first_names'] ?? '') . ' ' . ($application['guardian_last_names'] ?? ''));
            $cells = [
                $application['id'] ?? '',
                $application['created_at'] ?? '',
                $guardian,
                $application['guardian_email'] ?? '',
                $application['guardian_phone'] ?? '',
                $application['student_name'] ?? '',
                $application['course'] ?? '',
                $application['message'] ?? '',
            ];

            echo '<tr>';
            foreach ($cells as $cell) {
                echo '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
        exit;
    }

    public function settings(): void
    {
        Middleware::permission('configurar_postulaciones');
        $this->view('admissions/settings', [
            'title' => 'Configuración de postulaciones',
            'settings' => (new ApplicationSetting())->admissionSettings(),
            'errors' => [],
        ]);
    }

    public function updateSettings(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $currentSettings = (new ApplicationSetting())->admissionSettings();
        $errors = [];

        if (!filter_var($input['notification_email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Debes ingresar un correo de destino válido.';
        }
        if (trim($input['applicant_subject'] ?? '') === '') {
            $errors[] = 'Debes ingresar el asunto del correo para el postulante.';
        }
        if (trim($input['applicant_html'] ?? '') === '') {
            $errors[] = 'Debes ingresar el mensaje HTML para el postulante.';
        }

        $whatsAppEnabled = !empty($input['whatsapp_enabled']);
        $whatsAppAccessToken = trim($input['whatsapp_access_token'] ?? '') !== ''
            ? (string) $input['whatsapp_access_token']
            : (string) ($currentSettings['whatsapp_access_token'] ?? '');

        if ($whatsAppEnabled && trim($input['whatsapp_phone_number_id'] ?? '') === '') {
            $errors[] = 'Debes ingresar el ID del número de WhatsApp Business.';
        }
        if ($whatsAppEnabled && trim($whatsAppAccessToken) === '') {
            $errors[] = 'Debes ingresar el token de acceso de WhatsApp Business.';
        }
        if ($whatsAppEnabled && trim($input['whatsapp_message_template'] ?? '') === '') {
            $errors[] = 'Debes ingresar el mensaje automático de WhatsApp.';
        }

        $settings = [
            'notification_email' => $input['notification_email'] ?? '',
            'applicant_subject' => $input['applicant_subject'] ?? '',
            'applicant_html' => $input['applicant_html'] ?? '',
            'whatsapp_enabled' => $whatsAppEnabled,
            'whatsapp_phone_number_id' => $input['whatsapp_phone_number_id'] ?? '',
            'whatsapp_access_token' => $whatsAppAccessToken,
            'whatsapp_message_template' => $input['whatsapp_message_template'] ?? '',
        ];

        if ($errors) {
            $this->view('admissions/settings', [
                'title' => 'Configuración de postulaciones',
                'settings' => $settings,
                'errors' => $errors,
            ]);
            return;
        }

        $model = new ApplicationSetting();
        $model->set('admission_notification_email', $settings['notification_email']);
        $model->set('admission_applicant_success_subject', $settings['applicant_subject']);
        $model->set('admission_applicant_success_html', $settings['applicant_html']);
        $model->set('admission_whatsapp_enabled', $settings['whatsapp_enabled'] ? '1' : '0');
        $model->set('admission_whatsapp_phone_number_id', $settings['whatsapp_phone_number_id']);
        $model->set('admission_whatsapp_access_token', $settings['whatsapp_access_token']);
        $model->set('admission_whatsapp_message_template', $settings['whatsapp_message_template']);

        Session::flash('success', 'Configuración de postulaciones actualizada correctamente.');
        $this->redirect('/admission-settings');
    }

    private function validate(array $input): array
    {
        $errors = [];
        foreach (['nombres_apoderado', 'apellidos_apoderado', 'email', 'telefono', 'estudiante', 'curso'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                $errors[] = 'El campo ' . str_replace('_', ' ', $field) . ' es obligatorio.';
            }
        }
        if (!filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un correo electrónico válido.';
        }
        if (!array_key_exists((string) ($input['curso'] ?? ''), self::COURSES)) {
            $errors[] = 'Selecciona un curso válido.';
        }
        if (empty($input['autorizacion'])) {
            $errors[] = 'Debes autorizar el contacto para continuar con la postulación.';
        }

        return $errors;
    }

    private function normalize(array $input): array
    {
        return [
            'nombres_apoderado' => $input['nombres_apoderado'],
            'apellidos_apoderado' => $input['apellidos_apoderado'],
            'email' => strtolower($input['email']),
            'telefono' => $input['telefono'],
            'estudiante' => $input['estudiante'],
            'curso' => self::COURSES[$input['curso']],
            'mensaje' => $input['mensaje'] ?? '',
            'autorizacion' => '1',
        ];
    }
}
