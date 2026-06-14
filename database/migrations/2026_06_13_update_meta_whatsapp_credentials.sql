INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_phone_number_id', '637971779395576'),
('admission_whatsapp_business_account_id', '646043211679831')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
