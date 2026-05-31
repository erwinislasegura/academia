<?php if (!function_exists('h')) { function h(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); } } ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Academia Iquique</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= App::asset('/assets/css/app.css') ?>">
</head>
<body class="login-page">
    <main class="login-hero" aria-labelledby="login-title">
        <div class="login-orb orb-one"></div>
        <div class="login-orb orb-two"></div>
        <section class="login-shell">
            <aside class="login-brand-panel">
                <div class="brand-mark-large">
                    <img src="<?= App::asset('/assets/img/logo.svg') ?>" alt="Academia Iquique">
                </div>
                <p class="eyebrow light">Academia Iquique</p>
                <h1 id="login-title">Panel Administrativo</h1>
                <p>Gestión interna segura y profesional para una academia deportiva moderna, ordenada y lista para crecer.</p>
                <div class="login-metrics" aria-label="Características del sistema">
                    <span>RBAC</span>
                    <span>Usuarios</span>
                    <span>Actividad</span>
                </div>
            </aside>

            <section class="login-card" aria-label="Formulario de acceso">
                <div class="login-mobile-logo">
                    <img src="<?= App::asset('/assets/img/logo.svg') ?>" alt="Academia Iquique">
                </div>
                <p class="eyebrow">Acceso seguro</p>
                <h2>Bienvenido de vuelta</h2>
                <p class="login-subtitle">Ingresa con tus credenciales institucionales.</p>

                <?php if ($msg = Session::flash('error')): ?>
                    <div class="alert error"><?= h($msg) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= App::url('/login') ?>" class="form-stack login-form">
                    <label class="input-group">
                        <span>Email institucional</span>
                        <span class="input-shell">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6.5h16v11H4z"/><path d="m4 7 8 6 8-6"/></svg>
                            <input type="email" name="email" placeholder="admin@academiaiquique.cl" value="<?= h(Session::flash('old_email') ?? '') ?>" autocomplete="email" required>
                        </span>
                    </label>
                    <label class="input-group">
                        <span>Contraseña</span>
                        <span class="input-shell">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input id="passwordField" type="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                            <button class="password-toggle" type="button" data-password-toggle="passwordField" aria-label="Mostrar u ocultar contraseña">Ver</button>
                        </span>
                    </label>
                    <button class="btn primary full login-submit" type="submit">Ingresar al panel <span>→</span></button>
                </form>

                <div class="login-footnote">
                    <strong>Acceso restringido</strong>
                    <span>Sesión protegida con control de roles y permisos.</span>
                </div>
            </section>
        </section>
    </main>
    <script src="<?= App::asset('/assets/js/app.js') ?>"></script>
</body>
</html>
