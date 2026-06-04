-- Migración segura para producción: crea la tabla de cursos si no existe
-- y carga los cursos actuales del formulario sin duplicar registros.
CREATE TABLE IF NOT EXISTS admission_courses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_new_slots TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_admission_courses_active_order (is_active, sort_order),
  INDEX idx_admission_courses_new_slots (is_new_slots)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admission_courses (name, slug, sort_order, is_active, is_new_slots)
VALUES
('Kínder', 'kinder', 10, 1, 0),
('1º Básico', '1-basico', 20, 1, 0),
('2º Básico', '2-basico', 30, 1, 0),
('3º Básico', '3-basico', 40, 1, 0),
('4º Básico', '4-basico', 50, 1, 0),
('5º Básico', '5-basico', 60, 1, 0),
('6º Básico', '6-basico', 70, 1, 0),
('7º Básico', '7-basico', 80, 1, 0),
('8º Básico', '8-basico', 90, 1, 0)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  sort_order = VALUES(sort_order),
  updated_at = CURRENT_TIMESTAMP;
