<?php

return [
    'base_url' => rtrim((string) (getenv('INFOBIP_BASE_URL') ?: getenv('INFOBIP_API_BASE_URL') ?: 'https://4k99ym.api.infobip.com'), '/'),
    'api_key' => (string) (getenv('INFOBIP_API_KEY') ?: ''),
    'whatsapp_sender' => (string) (getenv('INFOBIP_WHATSAPP_SENDER') ?: '56962251376'),
    'notify_url' => (string) (getenv('INFOBIP_NOTIFY_URL') ?: ''),
    'timeout' => (int) (getenv('INFOBIP_TIMEOUT') ?: 15),
    'admission_template_name' => (string) (getenv('INFOBIP_ADMISSION_TEMPLATE') ?: 'confirmacion_postulacion'),
    'admission_template_language' => (string) (getenv('INFOBIP_ADMISSION_TEMPLATE_LANGUAGE') ?: 'es'),
];
