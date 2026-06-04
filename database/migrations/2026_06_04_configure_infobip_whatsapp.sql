INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_enabled', '1'),
('admission_whatsapp_base_url', '4k99ym.api.infobip.com'),
('admission_whatsapp_sender', '56985741931'),
('admission_whatsapp_api_key', '2e8f648e77b9fc422c1fda84055b99d6-d309d362-1a5c-4aa4-ac86-057666d341f3'),
('admission_whatsapp_access_token', '2e8f648e77b9fc422c1fda84055b99d6-d309d362-1a5c-4aa4-ac86-057666d341f3')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
