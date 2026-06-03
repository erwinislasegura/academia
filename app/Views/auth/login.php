<?php if (!function_exists('h')) { function h(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); } } ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academiapp | Academia Iquique</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= App::asset('/assets/css/app.css') ?>">
</head>
<body class="login-page">
    <main class="login-hero" aria-labelledby="login-title">
        <section class="login-shell">
            <aside class="login-brand-panel" aria-label="Información de acceso institucional">
                <div class="brand-content">
                    <div class="brand-mark-large">
                        <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique">
                    </div>

                    <p class="eyebrow light">Academia Iquique</p>
                    <h1 id="login-title">Sistema Academiapp</h1>
                    <p>
                        Acceso privado para administradores y administrativos de
                        Academia Iquique.
                    </p>
                </div>

                <div class="login-admin-note">
                    <span>Gestión interna</span>
                    <p>
                        Administra postulaciones, usuarios, roles y actividad desde
                        un entorno seguro y ordenado.
                    </p>
                </div>
            </aside>

            <section class="login-card" aria-label="Formulario de acceso">
                <div class="login-mobile-logo">
                    <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique">
                </div>

                <div class="login-heading">
                    <p class="eyebrow">Acceso institucional</p>
                    <h2>Ingresar a Academiapp</h2>
                    <p class="login-subtitle">Utiliza tus credenciales institucionales para acceder al sistema.</p>
                </div>

                <?php if ($msg = Session::flash('error')): ?>
                    <div class="alert error"><?= h($msg) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= App::url('/login') ?>" class="form-stack login-form">
                    <label class="input-group">
                        <span>Correo institucional</span>
                        <span class="input-shell">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6.5h16v11H4z"/><path d="m4 7 8 6 8-6"/></svg>
                            <input type="email" name="email" placeholder="usuario@academiaiquique.cl" value="<?= h(Session::flash('old_email') ?? '') ?>" autocomplete="email" required>
                        </span>
                    </label>

                    <label class="input-group">
                        <span class="password-label-row">
                            <span>Contraseña</span>
                            <a href="mailto:contacto@academiaiquique.cl?subject=Recuperar%20acceso%20Academiapp">Recuperar acceso</a>
                        </span>
                        <span class="input-shell">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input id="passwordField" type="password" name="password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required>
                            <button class="password-toggle" type="button" data-password-toggle="passwordField" aria-label="Mostrar u ocultar contraseña">Ver</button>
                        </span>
                    </label>

                    <label class="remember-row">
                        <input type="checkbox" name="remember">
                        <span>Mantener sesión</span>
                    </label>

                    <button class="btn primary full login-submit" type="submit">Acceder</button>
                </form>

                <div class="login-footnote">
                    <strong>Acceso exclusivo del equipo interno</strong>
                    <span>
                        Si necesitas una cuenta o recuperar permisos, solicita apoyo al equipo administrador.
                        <a href="mailto:contacto@academiaiquique.cl?subject=Soporte%20Academiapp">Solicitar soporte</a>
                    </span>
                </div>

                <div class="login-support">
                    Soporte:
                    <a href="mailto:contacto@academiaiquique.cl">contacto@academiaiquique.cl</a>
                </div>
            </section>
        </section>
    </main>
    <script src="<?= App::asset('/assets/js/app.js') ?>"></script>
</body>
</html>
