<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = App::basePath();
if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}
$currentPath = '/' . trim($currentPath, '/');
$currentPath = $currentPath === '/' ? '/' : rtrim($currentPath, '/');
$isActive = static fn(array $paths): bool => in_array($currentPath, $paths, true);
$canViewDashboard = Auth::can('ver_dashboard');
$canManageUsers = Auth::can('gestionar_usuarios');
$canManageRoles = Auth::can('gestionar_roles');
$canViewLogs = Auth::can('ver_logs');
$canConfigureAdmissions = Auth::can('configurar_postulaciones');
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-head">
        <a href="<?= App::url('/dashboard') ?>" class="brand-block" aria-label="Ir al dashboard">
            <span class="brand-logo-wrap"><img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique" class="brand-logo"></span>
            <span class="brand-copy"><strong>Academia Iquique</strong><small>Panel administrativo</small></span>
        </a>
    </div>

    <nav class="side-nav" aria-label="Menú principal">
        <span class="nav-section">Principal</span>
        <?php if ($canViewDashboard): ?><a href="<?= App::url('/dashboard') ?>" class="nav-link <?= $isActive(['/dashboard']) ? 'is-active' : '' ?>"><span class="nav-icon">⌁</span><span>Dashboard</span></a><?php endif; ?>

        <?php if ($canManageUsers || $canManageRoles || $canViewLogs): ?>
            <span class="nav-section">Gestión</span>
        <?php endif; ?>
        <?php if ($canManageUsers): ?><a href="<?= App::url('/users') ?>" class="nav-link <?= str_starts_with($currentPath, '/users') ? 'is-active' : '' ?>"><span class="nav-icon">◇</span><span>Usuarios</span></a><?php endif; ?>
        <?php if ($canManageRoles): ?><a href="<?= App::url('/roles') ?>" class="nav-link <?= str_starts_with($currentPath, '/roles') ? 'is-active' : '' ?>"><span class="nav-icon">⬡</span><span>Roles y permisos</span></a><?php endif; ?>
        <?php if ($canViewLogs): ?><a href="<?= App::url('/dashboard#actividad') ?>" class="nav-link"><span class="nav-icon">◷</span><span>Actividad</span></a><?php endif; ?>

        <?php if ($canConfigureAdmissions): ?>
            <span class="nav-section">Admisión</span>
            <a href="<?= App::url('/admission-settings') ?>" class="nav-link <?= $isActive(['/admission-settings']) ? 'is-active' : '' ?>"><span class="nav-icon">✉</span><span>Postulaciones</span></a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a class="public-page-link" href="<?= App::url('/postula.php') ?>" target="_blank" rel="noopener">
            <span>
                <strong>Ver página pública</strong>
                <small>postula.php</small>
            </span>
            <em>↗</em>
        </a>
        <a href="<?= App::url('/logout') ?>" class="logout-link"><span>Salir</span><small>Cerrar sesión</small></a>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
