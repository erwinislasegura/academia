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

La página pública del proceso de postulación queda disponible sin iniciar sesión en:

```txt
http://localhost:8000/postula
```

El formulario registra la postulación, envía el detalle al correo configurado en Academiapp y envía un correo HTML de confirmación al postulante. La configuración se administra desde Sistema Academiapp en **Postulaciones**, donde puedes definir el correo receptor y editar el mensaje HTML usando variables como `{{nombres_apoderado}}`, `{{estudiante}}` y `{{curso}}`.

Para que el correo HTML se envíe de forma confiable al correo ingresado por el postulante, configura un servicio SMTP real del dominio o proveedor transaccional. Puedes hacerlo con variables de entorno:

```env
MAIL_MAILER=smtp
MAIL_HOST=academia.gocreative.cl
MAIL_PORT=465
MAIL_USERNAME=notificacion@academia.gocreative.cl
MAIL_PASSWORD=contraseña-de-la-cuenta
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=notificacion@academia.gocreative.cl
MAIL_FROM_NAME="Academia Iquique"
```

Si no configuras SMTP, el sistema intentará usar `mail()` de PHP, lo que requiere que el servidor tenga un MTA/sendmail local funcionando y correctamente autorizado por SPF/DKIM/DMARC.

Para integrar solo el formulario en WordPress, incrusta la vista aislada con un bloque de HTML personalizado:

```html
<iframe
  src="https://tu-dominio.cl/postula-embed.php"
  style="width:100%; min-height:680px; border:0;"
  loading="lazy">
</iframe>
```

> Si tu servidor tiene correctamente activadas las URLs limpias con `mod_rewrite`, también puedes usar `https://tu-dominio.cl/postula-embed`. La variante `postula-embed.php` es más compatible con hosting compartido y WordPress porque no depende de la reescritura de URLs.

La API de WhatsApp aún no está implementada, por lo que el envío automático al teléfono informado por el postulante permanece desactivado en el panel de postulaciones.

Cuando se retome la integración, configura las credenciales por variables de entorno o desde el panel de postulaciones. La API Key no debe quedar escrita en el código ni en los seeds SQL:

```env
INFOBIP_BASE_URL=https://4k99ym.api.infobip.com
INFOBIP_API_KEY=tu-clave-api-infobip
INFOBIP_WHATSAPP_SENDER=56962251376
INFOBIP_NOTIFY_URL=https://tu-dominio.cl/webhook/infobip-whatsapp
INFOBIP_ADMISSION_TEMPLATE=confirmacion_postulacion
INFOBIP_ADMISSION_TEMPLATE_LANGUAGE=es
```

Ejemplo de payload para template:

```json
{
  "from": "56962251376",
  "to": "56912345678",
  "messageId": "tpl-20260604120000-abc123",
  "content": {
    "templateName": "confirmacion_postulacion",
    "templateData": {
      "body": {
        "placeholders": ["Juan Pérez", "Sofía Pérez", "1° Básico", "04-06-2026"]
      }
    },
    "language": "es"
  },
  "callbackData": "{\"modulo\":\"postulaciones\",\"registro_id\":123}",
  "notifyUrl": "https://tu-dominio.cl/webhook/infobip-whatsapp"
}
```

Ejemplo de payload para texto libre dentro de ventana de 24 horas:

```json
{
  "from": "56962251376",
  "to": "56912345678",
  "messageId": "txt-20260604120000-def456",
  "content": {
    "text": "Hola, recibimos tu postulación."
  },
  "callbackData": "{\"modulo\":\"postulaciones\"}"
}
```

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
