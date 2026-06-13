INSERT INTO application_settings (`key`, value) VALUES
('admission_whatsapp_template_name', 'confirmacion_postulacion_2027'),
('admission_whatsapp_template_language', 'es_CL')
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP;
