CREATE TABLE IF NOT EXISTS whatsapp_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  message_id VARCHAR(120) NOT NULL UNIQUE,
  to_number VARCHAR(30) NOT NULL,
  template_name VARCHAR(120) NULL,
  message_type VARCHAR(40) NOT NULL,
  payload_json MEDIUMTEXT NOT NULL,
  response_json MEDIUMTEXT NULL,
  status_group VARCHAR(80) NULL,
  status_name VARCHAR(120) NULL,
  status_description TEXT NULL,
  callback_data MEDIUMTEXT NULL,
  related_module VARCHAR(80) NULL,
  related_id BIGINT UNSIGNED NULL,
  error_message TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_whatsapp_logs_to_number (to_number),
  INDEX idx_whatsapp_logs_related (related_module, related_id),
  INDEX idx_whatsapp_logs_status (status_group, status_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_inbound_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  from_number VARCHAR(30) NULL,
  to_number VARCHAR(30) NULL,
  message_id VARCHAR(120) NULL,
  message_text TEXT NULL,
  payload_json MEDIUMTEXT NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_whatsapp_inbound_from (from_number),
  INDEX idx_whatsapp_inbound_message_id (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
