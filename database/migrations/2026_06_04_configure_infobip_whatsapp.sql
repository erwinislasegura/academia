INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_enabled', '1'),
('admission_whatsapp_base_url', 'https://4k99ym.api.infobip.com'),
('admission_whatsapp_sender', '56962251376'),
('admission_whatsapp_api_key', '197b0955fb86b0af598d9d14e140b27a-d8dba3f9-65c1-4716-8708-e027a97d35e4'),
('admission_whatsapp_access_token', '197b0955fb86b0af598d9d14e140b27a-d8dba3f9-65c1-4716-8708-e027a97d35e4'),
('admission_whatsapp_notify_url', ''),
('admission_whatsapp_template_name', 'confirmacion_postulacion'),
('admission_whatsapp_template_language', 'es')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
