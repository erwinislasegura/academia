INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_enabled', '1'),
('admission_whatsapp_base_url', 'https://graph.facebook.com/v25.0'),
('admission_whatsapp_phone_number_id', '1120859884450392'),
('admission_whatsapp_business_account_id', '956149663693846'),
('admission_whatsapp_sender', '56956701090')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
