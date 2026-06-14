<?php

final class AdmissionController extends Controller
{

    public function show(): void
    {
        $this->renderPublicForm('admissions/postula');
    }

    public function showEmbed(): void
    {
        $this->renderPublicForm('admissions/postula_embed');
    }

    public function submit(): void
    {
        $this->handleSubmission('admissions/postula');
    }

    public function submitEmbed(): void
    {
        $this->handleSubmission('admissions/postula_embed');
    }

    private function renderPublicForm(string $view): void
    {
        $this->view($view, [
            'old' => [],
            'errors' => [],
            'success' => false,
            'successMessageHtml' => '',
            'courses' => (new AdmissionCourse())->activeOptions(),
        ], null);
    }

    private function handleSubmission(string $view): void
    {
        $input = $this->input();
        $errors = $this->validate($input);

        if (!empty($input['website'])) {
            $errors[] = 'No fue posible procesar la postulación. Inténtalo nuevamente.';
        }

        if ($errors) {
            $this->view($view, [
                'old' => $input,
                'errors' => $errors,
                'success' => false,
                'successMessageHtml' => '',
                'courses' => (new AdmissionCourse())->activeOptions(),
            ], null);
            return;
        }

        $application = $this->normalize($input);
        $application['id'] = (new AdmissionApplication())->create($application);
        $settings = (new ApplicationSetting())->admissionSettings();
        AdmissionMailer::sendApplicantEmail($application, $settings);
        AdmissionMailer::sendAdminNotification($application, $settings);
        $whatsAppResult = WhatsAppNotifier::sendAdmissionMessageResult($application, $settings);
        if (!$whatsAppResult['success']) {
            error_log('[AdmissionController] WhatsApp automático no enviado para postulación #' . $application['id'] . ': ' . (string) ($whatsAppResult['error'] ?? $whatsAppResult['status'] ?? 'error desconocido'));
        }

        $this->view($view, [
            'old' => [],
            'errors' => [],
            'success' => true,
            'successMessageHtml' => '',
            'courses' => (new AdmissionCourse())->activeOptions(),
        ], null);
    }

    public function applications(): void
    {
        Middleware::permission('configurar_postulaciones');
        $model = new AdmissionApplication();
        $this->view('admissions/applications', [
            'title' => 'Postulaciones recibidas',
            'applications' => $model->all(),
            'statuses' => (new AdmissionStatus())->all(true),
            'totalApplications' => $model->count(),
        ]);
    }

    public function updateApplicationStatus(int $id): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $statusId = trim((string) ($input['status_id'] ?? '')) === '' ? null : (int) $input['status_id'];

        $ok = (new AdmissionApplication())->updateStatus($id, $statusId);
        (new User())->log((int) Session::get('user_id'), 'admission_status_changed', 'Actualizó el estado de la postulación #' . $id . '.');
        Session::flash($ok ? 'success' : 'error', $ok ? 'Estado de postulación actualizado.' : 'No fue posible actualizar el estado seleccionado.');
        $this->redirect('/admissions');
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
        foreach (['ID', 'Fecha', 'Apoderado', 'Email', 'Teléfono', 'Estudiante', 'Sexo', 'Fecha nacimiento', 'Edad', 'Curso', 'Estado', 'Mensaje'] as $heading) {
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
                ($application['student_gender'] ?? '') === 'nina' ? 'Niña' : (($application['student_gender'] ?? '') === 'nino' ? 'Niño' : ''),
                $application['student_birthdate'] ?? '',
                $application['student_age'] ?? '',
                $application['course'] ?? '',
                $application['status_name'] ?? 'Sin estado',
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
        $settings = (new ApplicationSetting())->admissionSettings();
        $this->view('admissions/settings', [
            'title' => 'Configuración de postulaciones',
            'settings' => $settings,
            'errors' => [],
            'previewApplication' => self::previewApplication(),
            'applicantPreviewHtml' => self::applicantPreviewHtml($settings),
        ]);
    }

    public function updateSettings(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
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


        $settings = [
            'notification_email' => $input['notification_email'] ?? '',
            'applicant_subject' => $input['applicant_subject'] ?? '',
            'applicant_html' => $input['applicant_html'] ?? '',
        ];

        if ($errors) {
            $this->view('admissions/settings', [
                'title' => 'Configuración de postulaciones',
                'settings' => $settings,
                'errors' => $errors,
                'previewApplication' => self::previewApplication(),
                'applicantPreviewHtml' => self::applicantPreviewHtml($settings),
            ]);
            return;
        }

        $model = new ApplicationSetting();
        $model->set('admission_notification_email', $settings['notification_email']);
        $model->set('admission_applicant_success_subject', $settings['applicant_subject']);
        $model->set('admission_applicant_success_html', $settings['applicant_html']);
        Session::flash('success', 'Configuración de postulaciones actualizada correctamente.');
        $this->redirect('/admission-settings');
    }


    private static function applicantPreviewHtml(array $settings): string
    {
        return AdmissionMailer::renderTemplate((string) ($settings['applicant_html'] ?? ''), self::previewApplication());
    }

    private static function previewApplication(): array
    {
        return [
            'nombres_apoderado' => 'María José',
            'apellidos_apoderado' => 'González Pérez',
            'email' => 'familia@ejemplo.cl',
            'telefono' => '+56 9 8574 1931',
            'estudiante' => 'Sofía González',
            'sexo_estudiante' => 'nina',
            'fecha_nacimiento' => '2020-05-14',
            'curso' => '1º Básico',
            'mensaje' => 'Solicito información sobre el proceso de postulación 2027.',
            'autorizacion' => '1',
        ];
    }

    private function validate(array $input): array
    {
        $errors = [];
        foreach (['nombres_apoderado', 'apellidos_apoderado', 'email', 'telefono', 'estudiante', 'sexo_estudiante', 'fecha_nacimiento', 'curso'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                $errors[] = 'El campo ' . str_replace('_', ' ', $field) . ' es obligatorio.';
            }
        }
        if (!filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un correo electrónico válido.';
        }
        if (trim((string) ($input['telefono'] ?? '')) !== '' && !WhatsAppNotifier::isValidRecipientPhone((string) $input['telefono'])) {
            $errors[] = 'Ingresa un teléfono móvil chileno válido para WhatsApp. Usa el formato +56 9 1234 5678.';
        }
        if (!in_array(($input['sexo_estudiante'] ?? ''), ['nino', 'nina'], true)) {
            $errors[] = 'Selecciona si el postulante es niño o niña.';
        }
        if (!$this->validBirthdate((string) ($input['fecha_nacimiento'] ?? ''))) {
            $errors[] = 'Ingresa una fecha de nacimiento válida.';
        }
        if (!$this->selectedCourse($input)) {
            $errors[] = 'Selecciona un curso válido y disponible.';
        }
        if (empty($input['autorizacion'])) {
            $errors[] = 'Debes autorizar el contacto para continuar con la postulación.';
        }

        return $errors;
    }


    private function validBirthdate(string $date): bool
    {
        $birthdate = DateTime::createFromFormat('Y-m-d', $date);
        if (!$birthdate || $birthdate->format('Y-m-d') !== $date) {
            return false;
        }

        $today = new DateTime('today');
        return $birthdate <= $today && $birthdate >= $today->modify('-25 years');
    }

    private function selectedCourse(array $input): ?array
    {
        return (new AdmissionCourse())->findActiveBySlug((string) ($input['curso'] ?? ''));
    }

    private function normalize(array $input): array
    {
        return [
            'nombres_apoderado' => $input['nombres_apoderado'],
            'apellidos_apoderado' => $input['apellidos_apoderado'],
            'email' => strtolower($input['email']),
            'telefono' => WhatsAppNotifier::formatRecipientPhone((string) $input['telefono']),
            'estudiante' => $input['estudiante'],
            'sexo_estudiante' => $input['sexo_estudiante'],
            'fecha_nacimiento' => $input['fecha_nacimiento'],
            'curso' => $this->selectedCourse($input)['name'],
            'mensaje' => $input['mensaje'] ?? '',
            'autorizacion' => '1',
        ];
    }
}
