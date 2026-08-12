CREATE TABLE IF NOT EXISTS admission_status_history (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  from_status_id BIGINT UNSIGNED NULL,
  to_status_id BIGINT UNSIGNED NULL,
  changed_by BIGINT UNSIGNED NULL,
  changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  total_elapsed_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  INDEX idx_admission_history_application (application_id, changed_at),
  INDEX idx_admission_history_transition (from_status_id, to_status_id),
  CONSTRAINT fk_admission_history_application FOREIGN KEY (application_id) REFERENCES admission_applications(id) ON DELETE CASCADE,
  CONSTRAINT fk_admission_history_from_status FOREIGN KEY (from_status_id) REFERENCES admission_statuses(id) ON DELETE SET NULL,
  CONSTRAINT fk_admission_history_to_status FOREIGN KEY (to_status_id) REFERENCES admission_statuses(id) ON DELETE SET NULL,
  CONSTRAINT fk_admission_history_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
