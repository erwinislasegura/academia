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
$isAdmissionsOpen = str_starts_with($currentPath, '/admissions') || str_starts_with($currentPath, '/admission-statuses') || $isActive(['/admission-settings']);
$isMailSettingsActive = $isActive(['/mail-settings']);
$isConfigOpen = str_starts_with($currentPath, '/users') || str_starts_with($currentPath, '/roles') || $isMailSettingsActive;
$icon = static function (string $name): string {
    $icons = [
        'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h7V4H4v9Zm0 7h7v-5H4v5Zm9 0h7v-9h-7v9Zm0-18v7h7V2h-7Z"/></svg>',
        'admissions' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 1.5V8h4.5M8 12h8M8 16h8M8 8h3"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Zm8.4-3.5c0-.6-.1-1.2-.2-1.7l2-1.5-2-3.5-2.4 1a8.5 8.5 0 0 0-3-1.7L14.5 2h-4l-.4 2.6a8.5 8.5 0 0 0-3 1.7l-2.4-1-2 3.5 2 1.5a8.7 8.7 0 0 0 0 3.4l-2 1.5 2 3.5 2.4-1a8.5 8.5 0 0 0 3 1.7l.4 2.6h4l.4-2.6a8.5 8.5 0 0 0 3-1.7l2.4 1 2-3.5-2-1.5c.1-.5.2-1.1.2-1.7Z"/></svg>',
        'external' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 3v18h-8"/></svg>',
    ];
    return $icons[$name] ?? '';
};
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-head">
        <a href="<?= App::url('/dashboard') ?>" class="brand-block" aria-label="Ir al dashboard">
            <span class="brand-logo-wrap"><img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique" class="brand-logo"></span>
            <span class="brand-copy"><strong>Academia Iquique</strong><small>Sistema Academiapp</small></span>
        </a>
    </div>

    <nav class="side-nav" aria-label="Menú principal">
        <?php if ($canViewDashboard): ?>
            <a href="<?= App::url('/dashboard') ?>" class="nav-link <?= $isActive(['/dashboard']) ? 'is-active' : '' ?>"><span class="nav-icon"><?= $icon('dashboard') ?></span><span>Dashboard</span></a>
        <?php endif; ?>

        <?php if ($canConfigureAdmissions): ?>
            <details class="nav-group" <?= $isAdmissionsOpen ? 'open' : '' ?>>
                <summary class="nav-link nav-summary <?= $isAdmissionsOpen ? 'is-active' : '' ?>">
                    <span class="nav-icon"><?= $icon('admissions') ?></span><span>Postulaciones</span><em>⌄</em>
                </summary>
                <div class="nav-submenu">
                    <a href="<?= App::url('/admissions') ?>" class="nav-sublink <?= str_starts_with($currentPath, '/admissions') ? 'is-active' : '' ?>">Solicitudes</a>
                    <a href="<?= App::url('/admission-statuses') ?>" class="nav-sublink <?= str_starts_with($currentPath, '/admission-statuses') ? 'is-active' : '' ?>">Estados</a>
                    <a href="<?= App::url('/admission-settings') ?>" class="nav-sublink <?= $isActive(['/admission-settings']) ? 'is-active' : '' ?>">Configuración</a>
                </div>
            </details>
        <?php endif; ?>

        <?php if ($hasConfigItems): ?>
            <details class="nav-group" <?= $isConfigOpen ? 'open' : '' ?>>
                <summary class="nav-link nav-summary <?= $isConfigOpen ? 'is-active' : '' ?>">
                    <span class="nav-icon"><?= $icon('settings') ?></span><span>Administración</span><em>⌄</em>
                </summary>
                <div class="nav-submenu">
                    <?php if ($canManageUsers): ?><a href="<?= App::url('/users') ?>" class="nav-sublink <?= str_starts_with($currentPath, '/users') ? 'is-active' : '' ?>">Usuarios</a><?php endif; ?>
                    <?php if ($canManageRoles): ?><a href="<?= App::url('/roles') ?>" class="nav-sublink <?= str_starts_with($currentPath, '/roles') ? 'is-active' : '' ?>">Roles y permisos</a><?php endif; ?>
                    <?php if ($canViewLogs): ?><a href="<?= App::url('/dashboard#actividad') ?>" class="nav-sublink">Actividad</a><?php endif; ?>
                    <?php if ($canConfigureAdmissions): ?><a href="<?= App::url('/mail-settings') ?>" class="nav-sublink <?= $isMailSettingsActive ? 'is-active' : '' ?>">Correo de notificaciones</a><?php endif; ?>
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
            <em><?= $icon('external') ?></em>
        </a>
        <a href="<?= App::url('/logout') ?>" class="logout-link"><em><?= $icon('logout') ?></em><span>Salir<small>Cerrar sesión</small></span></a>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
