<?php
if (!function_exists('h')) { function h(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); } }
$user = Auth::user();
?>
<!doctype html>
<html lang="es">
<?php require App::root('app/Views/layouts/header.php'); ?>
<body class="app-shell">
<?php require App::root('app/Views/layouts/sidebar.php'); ?>
<div class="main-panel">
    <?php require App::root('app/Views/layouts/navbar.php'); ?>
    <main class="content-wrap">
        <?php if ($msg = Session::flash('success')): ?><div class="alert success"><?= h($msg) ?></div><?php endif; ?>
        <?php if ($msg = Session::flash('error')): ?><div class="alert error"><?= h($msg) ?></div><?php endif; ?>
        <?php require $viewFile; ?>
    </main>
    <?php require App::root('app/Views/layouts/footer.php'); ?>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
