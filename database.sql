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
