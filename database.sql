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


CREATE TABLE admission_courses (
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

INSERT INTO admission_courses (name, slug, sort_order, is_active, is_new_slots) VALUES
('Kínder', 'kinder', 10, 1, 0),
('1º Básico', '1-basico', 20, 1, 0),
('2º Básico', '2-basico', 30, 1, 0),
('3º Básico', '3-basico', 40, 1, 0),
('4º Básico', '4-basico', 50, 1, 0),
('5º Básico', '5-basico', 60, 1, 0),
('6º Básico', '6-basico', 70, 1, 0),
('7º Básico', '7-basico', 80, 1, 0),
('8º Básico', '8-basico', 90, 1, 0);

CREATE TABLE admission_applications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  guardian_first_names VARCHAR(150) NOT NULL,
  guardian_last_names VARCHAR(150) NOT NULL,
  guardian_email VARCHAR(180) NOT NULL,
  guardian_phone VARCHAR(60) NOT NULL,
  student_name VARCHAR(180) NOT NULL,
  student_gender ENUM('nino','nina') NULL,
  student_birthdate DATE NULL,
  course VARCHAR(80) NOT NULL,
  message TEXT NULL,
  status_id BIGINT UNSIGNED NULL DEFAULT 1,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admission_applications_email (guardian_email),
  INDEX idx_admission_applications_created_at (created_at),
  INDEX idx_admission_applications_gender (student_gender),
  INDEX idx_admission_applications_birthdate (student_birthdate),
  INDEX idx_admission_applications_status (status_id),
  CONSTRAINT fk_admission_applications_status FOREIGN KEY (status_id) REFERENCES admission_statuses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO permissions (id, name, slug, module, description) VALUES
(9, 'Configurar postulaciones', 'configurar_postulaciones', 'Admisión', 'Configurar cursos, estados, correo receptor y mensajes para postulantes.');

INSERT INTO role_permissions (role_id, permission_id) VALUES
(1,9),(2,9);

INSERT INTO application_settings (`key`, value) VALUES
('admission_notification_email', 'contacto@academiaiquique.cl'),
('admission_applicant_success_subject', 'Postulación recibida · Academia Iquique'),
('admission_applicant_success_html', '<table class="container" style="max-width: 600px; margin: 0 auto; background: #ffffff;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0"><tbody><tr><td style="padding: 0;"><img class="full-img" style="width: 100%; height: auto; border: 0; text-decoration: none;" src="https://academiaiquique.cl/wp-content/uploads/2025/10/banner.png" alt="Admisión 2026 - Academia Iquique" width="600" /></td></tr><tr><td class="p-24" style="padding: 24px 28px 8px 28px; font-family: Arial, Helvetica, sans-serif; color: #0b2239; font-size: 14px; line-height: 1.6;"><h2 style="margin: 0 0 12px 0; font-size: 20px; color: #114b8b; font-weight: bold;">Información Admisión 2026 - Academia Iquique</h2><p style="margin: 0 0 12px 0;">Hola</p><p style="margin: 0 0 12px 0;">Gracias por su interés en postular a nuestro colegio.</p><p style="margin: 0 0 12px 0;">El proceso de <strong>admisión 2026</strong> ya se encuentra abierto y permanecerá vigente hasta completar los cupos disponibles por curso. Para continuar con su postulación, le solicitamos completar la ficha en la siguiente plataforma:</p><table style="margin: 16px 0px 20px; height: 83px;" role="presentation" border="0" width="290" cellspacing="0" cellpadding="0" align="left"><tbody><tr><td style="border-radius: 6px;" bgcolor="#114b8b"><a class="btn" style="padding: 12px 18px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 6px;" href="https://academiaiquique.postulaciones.colegium.com/" target="_blank" rel="noopener">Completar ficha de postulación</a></td></tr></tbody></table><div style="clear: both;"> </div><h3 style="margin: 24px 0 10px 0; font-size: 16px; color: #114b8b; border-bottom: 2px solid #114b8b; padding-bottom: 6px;">Información general</h3><ul style="padding-left: 18px; margin: 0 0 12px 0;"><li>Vacantes disponibles desde <strong>Kínder</strong> hasta <strong>8° Básico</strong>.</li><li>Cada curso tiene un máximo de <strong>30 estudiantes</strong>.</li><li>Matrícula: <strong>$325.000</strong> (1 cuota).</li><li>Arancel anual: <strong>$3.250.000</strong> (dividido en 10 cuotas de $325.000).<sup>**</sup></li><li><strong>No contamos</strong> con Programa de Integración Escolar (PIE).</li></ul><p style="margin: 0 0 12px 0;">Ante cualquier consulta, puede escribirnos a <a style="color: #114b8b; text-decoration: none;" href="mailto:admision@academiaiquique.cl">admision@academiaiquique.cl</a> o llamarnos al <a style="color: #114b8b; text-decoration: none;" href="tel:+56985741931">+56 9 85741931</a>.</p><p style="margin: 0 0 12px 0;">Adjunto encontrará el <strong>Reglamento de Admisión</strong>, donde se detallan las etapas y fechas del proceso.</p></td></tr><tr><td style="padding: 8px 20px 0 20px;"><img class="full-img" style="width: 100%; max-width: 560px; height: auto; margin: 0 auto; border: 0;" src="https://academiaiquique.cl/wp-content/uploads/2025/10/etapas3-1024x723.png" alt="Proceso de Admisión - Etapas" width="560" /></td></tr><tr><td style="padding: 20px 28px 6px 28px; font-family: Arial, Helvetica, sans-serif; color: #0b2239; font-size: 14px; line-height: 1.6;"><p style="margin: 0 0 12px 0;">Atentamente,</p><p style="margin: 0 0 12px 0;"><strong>Equipo de Admisión<br />Academia Iquique</strong></p><p style="margin: 0; font-size: 12px; color: #5f6b7a;">** valores corresponden sólo para alumnos nuevos.</p></td></tr><tr><td style="padding: 16px 28px 28px 28px; text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #98a2b3;">© 2025 Academia Iquique</td></tr></tbody></table>'),
('admission_whatsapp_enabled', '1'),
('admission_whatsapp_base_url', 'https://graph.facebook.com/v25.0'),
('admission_whatsapp_sender', '56962251376'),
('admission_whatsapp_phone_number_id', '637971779395576'),
('admission_whatsapp_api_key', 'EAAbmIPjo8O0BRo0Gd6QFCWy8GkPGUSOGcwCV3iV43RvhE05NHjZAeCa8ZBw6Vkk9N6jMZB1gQ1xaMCkkSDkk1AT5tBakzDNNOdV2ZC56TILr6eX51GwHZCoZBQKY2nMfIqvZC2YvgT17Ojl3ohi9lxuMz2ZAlg2BVojxA9U37Jzj4B6XvS8dZANHMkE73KLQpuO0Vtl3IxW6gZC6SDwm7MooCG81hks4ZCuKvyfE8G14CwB0vm16dZBiB2lHpnA09fNuwrA3YCzFOdZBjBBDVRRQElkI1NpSUPcEZD'),
('admission_whatsapp_access_token', 'EAAbmIPjo8O0BRo0Gd6QFCWy8GkPGUSOGcwCV3iV43RvhE05NHjZAeCa8ZBw6Vkk9N6jMZB1gQ1xaMCkkSDkk1AT5tBakzDNNOdV2ZC56TILr6eX51GwHZCoZBQKY2nMfIqvZC2YvgT17Ojl3ohi9lxuMz2ZAlg2BVojxA9U37Jzj4B6XvS8dZANHMkE73KLQpuO0Vtl3IxW6gZC6SDwm7MooCG81hks4ZCuKvyfE8G14CwB0vm16dZBiB2lHpnA09fNuwrA3YCzFOdZBjBBDVRRQElkI1NpSUPcEZD'),
('admission_whatsapp_business_account_id', '646043211679831'),
('admission_whatsapp_notify_url', ''),
('admission_whatsapp_template_name', 'admision2027_final'),
('admission_whatsapp_template_language', 'en_US'),
('admission_whatsapp_message_template', 'Hola {{nombres_apoderado}}, confirmamos la recepción de la postulación de {{estudiante}} para {{curso}}. Nuestro equipo de admisión revisará la información enviada y se contactará contigo si requiere antecedentes adicionales o para informar los próximos pasos. Academia Iquique'),
('mail_mailer', 'smtp'),
('mail_host', 'academia.gocreative.cl'),
('mail_port', '465'),
('mail_username', 'notificacion@academia.gocreative.cl'),
('mail_password', 'Contra3333%%%&&'),
('mail_encryption', 'ssl'),
('mail_from_address', 'notificacion@academia.gocreative.cl'),
('mail_from_name', 'Academia Iquique');
