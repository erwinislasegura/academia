INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_enabled', '1'),
('admission_whatsapp_base_url', 'https://graph.facebook.com/v20.0'),
('admission_whatsapp_phone_number_id', '417893274747575'),
('admission_whatsapp_business_account_id', '474074592446301')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
