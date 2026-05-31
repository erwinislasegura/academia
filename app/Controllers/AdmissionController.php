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

        $message = AdmissionMailer::renderTemplate($settings['applicant_html'], $application);
        if (!$mailSent) {
            $message .= '<p><small>Tu postulación quedó registrada, pero el servidor no pudo confirmar el envío de correo automático. Nuestro equipo igualmente revisará tu solicitud.</small></p>';
        }

        $this->view('admissions/postula', [
            'old' => [],
            'errors' => [],
            'success' => true,
            'successMessageHtml' => $message,
        ], null);
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
