<?php if (!function_exists('h')) { function h(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); } } ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Academia Iquique</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="login-page">
    <section class="login-hero">
        <div class="login-card">
            <div class="login-logo-wrap"><img src="/assets/img/logo.svg" alt="Academia Iquique"></div>
            <p class="eyebrow">Panel Administrativo</p>
            <h1>Academia Iquique</h1>
            <p class="login-subtitle">Gestión interna segura y profesional</p>
            <?php if ($msg = Session::flash('error')): ?><div class="alert error"><?= h($msg) ?></div><?php endif; ?>
            <form method="post" action="/login" class="form-stack">
                <label>Email institucional<input type="email" name="email" placeholder="admin@academiaiquique.cl" value="<?= h(Session::flash('old_email') ?? '') ?>" required></label>
                <label>Contraseña<input type="password" name="password" placeholder="••••••••" required></label>
                <button class="btn primary full" type="submit">Ingresar al panel</button>
            </form>
        </div>
        <div class="login-copy"><span></span><strong>Disciplina, gestión y excelencia deportiva.</strong></div>
    </section>
</body>
</html>
