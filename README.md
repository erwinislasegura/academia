# Academia Iquique · Sistema Academiapp MVC

Sistema web administrativo desarrollado en **PHP 8+ puro**, arquitectura **MVC**, **PDO** y **MySQL**. Incluye login seguro para administradores y administrativos, dashboard, gestión de usuarios, roles, permisos y registro de actividad con una identidad visual institucional inspirada en Academia Iquique.

## Requisitos

- PHP 8.0 o superior.
- Extensiones PHP: `pdo`, `pdo_mysql`, `session`.
- MySQL 5.7+ o MariaDB compatible.
- Servidor web con `public/` como document root o servidor embebido de PHP.

## 1. Crear la base de datos

Puedes crearla manualmente:

```sql
CREATE DATABASE academia_iquique CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

El script `database.sql` también incluye `CREATE DATABASE IF NOT EXISTS`.

## 2. Importar `database.sql`

Desde la raíz del proyecto:

```bash
mysql -u root -p < database.sql
```

También se incluye una copia en `.sql/database.sql` si prefieres mantener scripts SQL agrupados.

## 3. Configurar conexión

Edita `config/database.php` o usa variables de entorno:

```php
'host' => getenv('DB_HOST') ?: '127.0.0.1',
'database' => getenv('DB_DATABASE') ?: 'academia_iquique',
'username' => getenv('DB_USERNAME') ?: 'root',
'password' => getenv('DB_PASSWORD') ?: '',
```

Variables disponibles:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=academia_iquique
export DB_USERNAME=root
export DB_PASSWORD=secret
```

## 4. Levantar el proyecto

Para desarrollo local:

```bash
php -S localhost:8000 -t public
```

Luego abre:

```txt
http://localhost:8000/login
```

En Apache/Nginx, configura el document root apuntando a la carpeta `public/`.

## 5. Datos de acceso inicial

```txt
Email: admin@academiaiquique.cl
Password: Admin123456
Rol: Super Administrador
```

La contraseña inicial se almacena encriptada con `password_hash`.

## 6. Estructura principal

```txt
app/Controllers   Controladores MVC
app/Models        Modelos con consultas PDO preparadas
app/Views         Vistas y layouts
core/             Router, Auth, Middleware, Database, Session, Validator
config/           Configuración de app y base de datos
public/           Front controller y assets públicos (logo vectorial SVG, sin binarios en git)
routes/web.php    Definición de rutas
storage/logs      Carpeta preparada para logs futuros
```


## 7. Postulación pública

La página pública de admisión queda disponible sin iniciar sesión en:

```txt
http://localhost:8000/postula
```

El formulario registra la postulación, envía el detalle al correo configurado en Academiapp y envía un correo HTML de confirmación al postulante. La configuración se administra desde Sistema Academiapp en **Postulaciones**, donde puedes definir el correo receptor y editar el mensaje HTML usando variables como `{{nombres_apoderado}}`, `{{estudiante}}` y `{{curso}}`.

Para integrar solo el formulario en WordPress, incrusta la vista aislada con un bloque de HTML personalizado:

```html
<iframe
  src="https://tu-dominio.cl/postula-embed"
  style="width:100%; min-height:900px; border:0;"
  loading="lazy">
</iframe>
```

Opcionalmente, desde Sistema Academiapp puedes activar el envío automático por WhatsApp Cloud API al teléfono informado por el postulante. Para usarlo debes configurar el **Phone Number ID**, un token de acceso válido y el texto del mensaje; WhatsApp puede exigir plantillas aprobadas para conversaciones iniciadas por la institución.

Para desarrollo con el servidor embebido de PHP y rutas limpias, puedes usar:

```bash
php -S localhost:8000 public/index.php
```

## Nota sobre el logo

Para evitar errores de binarios en el repositorio, el identificador incluido está versionado como `public/assets/img/logo.svg`. Si necesitas usar el archivo PNG oficial adjunto por diseño/marketing, puedes colocarlo localmente en `public/assets/img/logo.png` y cambiar las referencias de las vistas a ese archivo.

## Seguridad incluida

- Login con `password_verify`.
- Hash de contraseñas con `password_hash`.
- Consultas preparadas con PDO.
- Regeneración de ID de sesión al iniciar sesión.
- Middleware de autenticación y permisos.
- Usuarios activos/inactivos.
- Registro de actividad básica.
- Reglas para evitar eliminar el usuario actual o el último Super Administrador activo.

## Extensión futura

La arquitectura está preparada para incorporar módulos como alumnos, profesores, clases, asistencia, pagos, reportes, fichas deportivas y comunicaciones internas manteniendo el patrón MVC.
