CREATE DATABASE IF NOT EXISTS academia_iquique CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE academia_iquique;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  module VARCHAR(80) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  UNIQUE KEY uq_role_permission (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(180) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_password_resets_email (email)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  description TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO roles (id, name, slug, description) VALUES
(1, 'Super Administrador', 'super-administrador', 'Control total del sistema y sus privilegios.'),
(2, 'Administrador', 'administrador', 'Administración operativa de usuarios y roles.'),
(3, 'Coordinador', 'coordinador', 'Coordinación interna y consulta de información.'),
(4, 'Usuario', 'usuario', 'Acceso básico al panel institucional.');

INSERT INTO permissions (id, name, slug, module, description) VALUES
(1, 'Ver dashboard', 'ver_dashboard', 'Dashboard', 'Acceso al panel principal.'),
(2, 'Gestionar usuarios', 'gestionar_usuarios', 'Usuarios', 'Ver y administrar el módulo de usuarios.'),
(3, 'Crear usuarios', 'crear_usuarios', 'Usuarios', 'Registrar nuevos usuarios.'),
(4, 'Editar usuarios', 'editar_usuarios', 'Usuarios', 'Actualizar usuarios y cambiar estados.'),
(5, 'Eliminar usuarios', 'eliminar_usuarios', 'Usuarios', 'Eliminar usuarios respetando reglas de seguridad.'),
(6, 'Gestionar roles', 'gestionar_roles', 'Roles', 'Ver roles y privilegios.'),
(7, 'Gestionar permisos', 'gestionar_permisos', 'Roles', 'Crear, editar o eliminar roles y permisos asignados.'),
(8, 'Ver logs', 'ver_logs', 'Actividad', 'Consultar actividad reciente del sistema.');

INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;
INSERT INTO role_permissions (role_id, permission_id) VALUES
(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,8),
(3,1),(3,2),(3,4),(3,8),
(4,1);

INSERT INTO users (name, email, password, role_id, status) VALUES
('Super Admin', 'admin@academiaiquique.cl', '$2y$12$IO/xY85Xh.1WbqmN2ZER6.gUpp40KJtrrm/Fo7kNKuPRNisizKPZa', 1, 'active');

CREATE TABLE application_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(120) NOT NULL UNIQUE,
  value MEDIUMTEXT NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admission_statuses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  color VARCHAR(7) NOT NULL DEFAULT '#071D7A',
  description TEXT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admission_statuses (id, name, slug, color, description, sort_order, is_active) VALUES
(1, 'Recibida', 'recibida', '#2563EB', 'Solicitud ingresada desde el formulario público.', 10, 1),
(2, 'En revisión', 'en-revision', '#F59E0B', 'El equipo de admisión está revisando los antecedentes.', 20, 1),
(3, 'Contactada', 'contactada', '#7C3AED', 'La familia ya fue contactada para continuar el proceso.', 30, 1),
(4, 'Aceptada', 'aceptada', '#16A34A', 'Postulación aceptada para avanzar a matrícula.', 40, 1),
(5, 'Rechazada', 'rechazada', '#DC2626', 'Postulación cerrada sin cupo o sin continuidad.', 50, 1);

CREATE TABLE admission_applications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guardian_first_names VARCHAR(150) NOT NULL,
  guardian_last_names VARCHAR(150) NOT NULL,
  guardian_email VARCHAR(180) NOT NULL,
  guardian_phone VARCHAR(60) NOT NULL,
  student_name VARCHAR(180) NOT NULL,
  course VARCHAR(80) NOT NULL,
  message TEXT NULL,
  status_id BIGINT UNSIGNED NULL DEFAULT 1,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admission_applications_email (guardian_email),
  INDEX idx_admission_applications_created_at (created_at),
  INDEX idx_admission_applications_status (status_id),
  CONSTRAINT fk_admission_applications_status FOREIGN KEY (status_id) REFERENCES admission_statuses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO permissions (id, name, slug, module, description) VALUES
(9, 'Configurar postulaciones', 'configurar_postulaciones', 'Admisión', 'Configurar correo receptor y mensaje HTML para postulantes.');

INSERT INTO role_permissions (role_id, permission_id) VALUES
(1,9),(2,9);

INSERT INTO application_settings (`key`, value) VALUES
('admission_notification_email', 'contacto@academiaiquique.cl'),
('admission_applicant_success_subject', 'Postulación recibida · Academia Iquique'),
('admission_applicant_success_html', '<p>Hola {{nombres_apoderado}},</p><p>Tu postulación para {{estudiante}} al curso {{curso}} fue recibida exitosamente.</p><p>Nuestro equipo de admisión revisará los antecedentes y se contactará contigo para orientar los próximos pasos.</p><p><strong>Academia Iquique</strong></p>'),
('admission_whatsapp_enabled', '0'),
('admission_whatsapp_phone_number_id', ''),
('admission_whatsapp_access_token', ''),
('admission_whatsapp_message_template', 'Hola {{nombres_apoderado}}, recibimos correctamente la postulación de {{estudiante}} para {{curso}}. Nuestro equipo de admisión revisará los antecedentes y se contactará contigo. Academia Iquique');
