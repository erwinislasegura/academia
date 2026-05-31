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
$hasConfigItems = $canManageUsers || $canManageRoles || $canViewLogs || $canConfigureAdmissions;
$isConfigOpen = str_starts_with($currentPath, '/users') || str_starts_with($currentPath, '/roles') || $isActive(['/admission-settings']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-head">
        <a href="<?= App::url('/dashboard') ?>" class="brand-block" aria-label="Ir al dashboard">
            <span class="brand-logo-wrap"><img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique" class="brand-logo"></span>
            <span class="brand-copy"><strong>Academia Iquique</strong><small>Panel administrativo</small></span>
        </a>
    </div>

    <nav class="side-nav" aria-label="Menú principal">
        <?php if ($canViewDashboard): ?>
            <a href="<?= App::url('/dashboard') ?>" class="nav-link <?= $isActive(['/dashboard']) ? 'is-active' : '' ?>"><span class="nav-icon">⌁</span><span>Dashboard</span></a>
        <?php endif; ?>

        <?php if ($canConfigureAdmissions): ?>
            <a href="<?= App::url('/admissions') ?>" class="nav-link <?= $isActive(['/admissions']) ? 'is-active' : '' ?>"><span class="nav-icon">▦</span><span>Solicitudes</span></a>
        <?php endif; ?>

        <?php if ($hasConfigItems): ?>
            <details class="nav-group" <?= $isConfigOpen ? 'open' : '' ?>>
                <summary class="nav-link nav-summary <?= $isConfigOpen ? 'is-active' : '' ?>">
                    <span class="nav-icon">⚙</span><span>Configuración</span><em>⌄</em>
                </summary>
                <div class="nav-submenu">
                    <?php if ($canManageUsers): ?><a href="<?= App::url('/users') ?>" class="nav-sublink <?= str_starts_with($currentPath, '/users') ? 'is-active' : '' ?>">Usuarios</a><?php endif; ?>
                    <?php if ($canManageRoles): ?><a href="<?= App::url('/roles') ?>" class="nav-sublink <?= str_starts_with($currentPath, '/roles') ? 'is-active' : '' ?>">Roles y permisos</a><?php endif; ?>
                    <?php if ($canViewLogs): ?><a href="<?= App::url('/dashboard#actividad') ?>" class="nav-sublink">Actividad</a><?php endif; ?>
                    <?php if ($canConfigureAdmissions): ?><a href="<?= App::url('/admission-settings') ?>" class="nav-sublink <?= $isActive(['/admission-settings']) ? 'is-active' : '' ?>">Postulaciones</a><?php endif; ?>
                </div>
            </details>
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
