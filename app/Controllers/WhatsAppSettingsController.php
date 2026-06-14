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
        ]);
    }

    public function update(): void
    {
        Middleware::permission('configurar_postulaciones');
        $input = $this->input();
        $settings = (new ApplicationSetting())->admissionSettings();
        $settings['whatsapp_template_name'] = trim((string) ($input['whatsapp_template_name'] ?? ''));
        $settings['whatsapp_template_language'] = trim((string) ($input['whatsapp_template_language'] ?? ''));

        $errors = [];
        if ($settings['whatsapp_template_name'] === '') {
            $errors[] = 'Debes ingresar el nombre de la plantilla aprobada en Meta.';
        }
        if ($settings['whatsapp_template_language'] === '') {
            $errors[] = 'Debes ingresar el idioma de la plantilla aprobada en Meta.';
        }

        if ($errors) {
            $this->view('whatsapp_settings/edit', [
                'title' => 'Plantilla de WhatsApp',
                'settings' => $settings,
                'errors' => $errors,
            ]);
            return;
        }

        $model = new ApplicationSetting();
        $model->set('admission_whatsapp_enabled', '1');
        $model->set('admission_whatsapp_base_url', 'https://graph.facebook.com/v25.0');
        $model->set('admission_whatsapp_phone_number_id', '637971779395576');
        $model->set('admission_whatsapp_business_account_id', '646043211679831');
        $model->set('admission_whatsapp_template_name', $settings['whatsapp_template_name']);
        $model->set('admission_whatsapp_template_language', $settings['whatsapp_template_language']);

        Session::flash('success', 'Plantilla de WhatsApp actualizada correctamente.');
        $this->redirect('/whatsapp-settings');
    }
}
