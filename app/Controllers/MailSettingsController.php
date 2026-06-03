<?php

final class MailSettingsController extends Controller
{
    public function edit(): void
    {
        Middleware::permission('configurar_postulaciones');
        $settings = (new ApplicationSetting())->mailSettings();
        $this->view('mail_settings/edit', [
            'title' => 'Configuración de correo',
            'settings' => $settings,
            'errors' => [],
            'testEmail' => $settings['from_address'] ?? '',
        ]);
    }

    public function update(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $model = new ApplicationSetting();
        $currentSettings = $model->mailSettings();
        $settings = $this->normalize($input, $currentSettings);
        $errors = $this->validate($settings);
        $action = (string) ($input['action'] ?? 'save');
        $testEmail = (string) ($input['test_email'] ?? ($settings['from_address'] ?? ''));

        if ($action === 'test' && !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Debes ingresar un correo válido para enviar la prueba.';
        }

        if ($errors) {
            $this->view('mail_settings/edit', [
                'title' => 'Configuración de correo',
                'settings' => $settings,
                'errors' => $errors,
                'testEmail' => $testEmail,
            ]);
            return;
        }

        $model->saveMailSettings($settings);

        if ($action === 'test') {
            $sent = AdmissionMailer::sendTestEmail($testEmail, $settings);
            Session::flash($sent ? 'success' : 'error', $sent ? 'Configuración guardada y correo de prueba enviado.' : 'Configuración guardada, pero no fue posible enviar el correo de prueba. Revisa host, puerto, cifrado, usuario y contraseña.');
            $this->redirect('/mail-settings');
        }

        Session::flash('success', 'Configuración de correo guardada correctamente.');
        $this->redirect('/mail-settings');
    }

    private function normalize(array $input, array $currentSettings): array
    {
        return [
            'mailer' => (string) ($input['mailer'] ?? 'smtp'),
            'host' => (string) ($input['host'] ?? ''),
            'port' => (int) ($input['port'] ?? 465),
            'username' => (string) ($input['username'] ?? ''),
            'password' => trim((string) ($input['password'] ?? '')) !== '' ? (string) $input['password'] : (string) ($currentSettings['password'] ?? ''),
            'encryption' => (string) ($input['encryption'] ?? 'ssl'),
            'from_address' => strtolower((string) ($input['from_address'] ?? '')),
            'from_name' => (string) ($input['from_name'] ?? 'Academia Iquique'),
        ];
    }

    private function validate(array $settings): array
    {
        $errors = [];
        if (!in_array($settings['mailer'], ['smtp', 'mail'], true)) {
            $errors[] = 'Selecciona un método de envío válido.';
        }
        if ($settings['mailer'] === 'smtp') {
            foreach (['host' => 'servidor SMTP', 'username' => 'usuario SMTP', 'password' => 'contraseña SMTP'] as $key => $label) {
                if (trim((string) ($settings[$key] ?? '')) === '') {
                    $errors[] = 'Debes ingresar el ' . $label . '.';
                }
            }
            if ((int) ($settings['port'] ?? 0) <= 0) {
                $errors[] = 'Debes ingresar un puerto SMTP válido.';
            }
        }
        if (!in_array($settings['encryption'], ['ssl', 'tls', 'none'], true)) {
            $errors[] = 'Selecciona un cifrado válido.';
        }
        if (!filter_var($settings['from_address'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Debes ingresar un correo remitente válido.';
        }
        if (trim((string) ($settings['from_name'] ?? '')) === '') {
            $errors[] = 'Debes ingresar el nombre del remitente.';
        }

        return $errors;
    }
}
