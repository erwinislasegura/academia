<aside class="sidebar" id="sidebar">
    <div class="brand-block">
        <img src="<?= App::asset('/assets/img/logo.svg') ?>" alt="Academia Iquique" class="brand-logo">
        <div><strong>Academia</strong><span>Iquique</span></div>
    </div>
    <nav class="side-nav">
        <?php if (Auth::can('ver_dashboard')): ?><a href="<?= App::url('/dashboard') ?>" class="nav-link"><span>⌁</span> Dashboard</a><?php endif; ?>
        <?php if (Auth::can('gestionar_usuarios')): ?><a href="<?= App::url('/users') ?>" class="nav-link"><span>◇</span> Usuarios</a><?php endif; ?>
        <?php if (Auth::can('gestionar_roles')): ?><a href="<?= App::url('/roles') ?>" class="nav-link"><span>⬡</span> Roles y permisos</a><?php endif; ?>
        <?php if (Auth::can('ver_logs')): ?><a href="<?= App::url('/dashboard#actividad') ?>" class="nav-link"><span>◷</span> Actividad</a><?php endif; ?>
        <?php if (Auth::can('configurar_postulaciones')): ?><a href="<?= App::url('/admission-settings') ?>" class="nav-link"><span>✉</span> Postulaciones</a><?php endif; ?>
    </nav>
    <div class="sidebar-card">
        <p>Plataforma institucional lista para crecer con alumnos, clases, pagos y asistencia.</p>
    </div>
    <a href="<?= App::url('/logout') ?>" class="logout-link">Cerrar sesión</a>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
