INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_template_name', 'hello_world'),
('admission_whatsapp_template_language', 'en_US')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
