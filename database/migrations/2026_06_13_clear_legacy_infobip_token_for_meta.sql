UPDATE application_settings
SET value = '', updated_at = CURRENT_TIMESTAMP
WHERE `key` IN ('admission_whatsapp_api_key', 'admission_whatsapp_access_token')
  AND value = '197b0955fb86b0af598d9d14e140b27a-d8dba3f9-65c1-4716-8708-e027a97d35e4';
