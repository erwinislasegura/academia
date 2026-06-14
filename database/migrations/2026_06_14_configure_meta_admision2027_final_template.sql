INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_enabled', '1'),
('admission_whatsapp_base_url', 'https://graph.facebook.com/v25.0'),
('admission_whatsapp_phone_number_id', '637971779395576'),
('admission_whatsapp_template_name', 'admision2027_final'),
('admission_whatsapp_template_language', 'en_US')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
