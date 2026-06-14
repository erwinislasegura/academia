<?php

return [
    'base_url' => rtrim((string) (getenv('INFOBIP_BASE_URL') ?: getenv('INFOBIP_API_BASE_URL') ?: 'https://4k99ym.api.infobip.com'), '/'),
    'api_key' => (string) (getenv('INFOBIP_API_KEY') ?: '197b0955fb86b0af598d9d14e140b27a-d8dba3f9-65c1-4716-8708-e027a97d35e4'),
    'whatsapp_sender' => (string) (getenv('INFOBIP_WHATSAPP_SENDER') ?: '56956701090'),
    'notify_url' => (string) (getenv('INFOBIP_NOTIFY_URL') ?: ''),
    'timeout' => (int) (getenv('INFOBIP_TIMEOUT') ?: 15),
    'admission_template_name' => (string) (getenv('INFOBIP_ADMISSION_TEMPLATE') ?: 'admision2027_final'),
    'admission_template_language' => (string) (getenv('INFOBIP_ADMISSION_TEMPLATE_LANGUAGE') ?: 'en_US'),
    'meta_phone_number_id' => (string) (getenv('META_WHATSAPP_PHONE_NUMBER_ID') ?: '1120859884450392'),
    'meta_business_account_id' => (string) (getenv('META_WHATSAPP_BUSINESS_ACCOUNT_ID') ?: '956149663693846'),
    'meta_access_token' => (string) (getenv('META_WHATSAPP_ACCESS_TOKEN') ?: ''),
    'meta_webhook_verify_token' => (string) (getenv('META_WHATSAPP_WEBHOOK_VERIFY_TOKEN') ?: ''),
];
