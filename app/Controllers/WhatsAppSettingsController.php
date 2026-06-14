<?php

final class WhatsAppSettingsController extends Controller
{
    public function edit(): void
    {
        Middleware::permission('configurar_postulaciones');
        $settings = (new ApplicationSetting())->admissionSettings();

        $this->view('whatsapp_settings/edit', [
            'title' => 'Plantilla de WhatsApp',
            'settings' => $settings,
            'errors' => [],
            'recentWhatsAppTests' => (new WhatsAppLog())->latestByModule('whatsapp_test'),
        ]);
    }

    public function update(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $settings = (new ApplicationSetting())->admissionSettings();
        $settings['whatsapp_template_name'] = trim((string) ($input['whatsapp_template_name'] ?? ''));
        $settings['whatsapp_template_language'] = trim((string) ($input['whatsapp_template_language'] ?? ''));
        $settings['whatsapp_phone_number_id'] = trim((string) ($input['whatsapp_phone_number_id'] ?? ''));
        $settings['whatsapp_business_account_id'] = trim((string) ($input['whatsapp_business_account_id'] ?? ''));
        $settings['whatsapp_sender'] = trim((string) ($input['whatsapp_sender'] ?? ''));
        $settings['whatsapp_api_key'] = trim((string) ($input['whatsapp_api_key'] ?? ''));

        $errors = [];
        if ($settings['whatsapp_template_name'] === '') {
            $errors[] = 'Debes ingresar el nombre de la plantilla aprobada en Meta.';
        }
        if ($settings['whatsapp_template_language'] === '') {
            $errors[] = 'Debes ingresar el idioma de la plantilla aprobada en Meta.';
        }
        if ($settings['whatsapp_phone_number_id'] === '') {
            $errors[] = 'Debes ingresar el PHONE_NUMBER_ID de Meta WhatsApp Cloud API.';
        } elseif (!ctype_digit($settings['whatsapp_phone_number_id'])) {
            $errors[] = 'El PHONE_NUMBER_ID debe contener solo números.';
        }
        if ($settings['whatsapp_business_account_id'] === '') {
            $errors[] = 'Debes ingresar el WABA_ID de Meta WhatsApp Business.';
        } elseif (!ctype_digit($settings['whatsapp_business_account_id'])) {
            $errors[] = 'El WABA_ID debe contener solo números.';
        }
        if ($settings['whatsapp_sender'] === '') {
            $errors[] = 'Debes ingresar el número emisor asociado a WhatsApp.';
        }
        if ($settings['whatsapp_api_key'] === '') {
            $errors[] = 'Debes ingresar el token de acceso de Meta WhatsApp Cloud API.';
        }

        if ($errors) {
            $this->view('whatsapp_settings/edit', [
                'title' => 'Plantilla de WhatsApp',
                'settings' => $settings,
                'errors' => $errors,
                'recentWhatsAppTests' => (new WhatsAppLog())->latestByModule('whatsapp_test'),
            ]);
            return;
        }

        $model = new ApplicationSetting();
        $model->set('admission_whatsapp_enabled', '1');
        $model->set('admission_whatsapp_base_url', 'https://graph.facebook.com/v25.0');
        $model->set('admission_whatsapp_phone_number_id', $settings['whatsapp_phone_number_id']);
        $model->set('admission_whatsapp_business_account_id', $settings['whatsapp_business_account_id']);
        $model->set('admission_whatsapp_sender', $this->normalizePhoneNumber($settings['whatsapp_sender']));
        $model->set('admission_whatsapp_template_name', $settings['whatsapp_template_name']);
        $model->set('admission_whatsapp_template_language', $settings['whatsapp_template_language']);
        $model->set('admission_whatsapp_api_key', $settings['whatsapp_api_key']);
        $model->set('admission_whatsapp_access_token', $settings['whatsapp_api_key']);

        Session::flash('success', 'Configuración de WhatsApp actualizada correctamente.');
        $this->redirect('/whatsapp-settings');
    }

    private function normalizePhoneNumber(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
