<section class="hero-card">
    <div><p class="eyebrow">Bienvenido de vuelta</p><h2><?= h(Auth::user()['name'] ?? 'Usuario') ?></h2><p>Gestiona usuarios, roles y privilegios desde un entorno seguro para Academia Iquique.</p></div>
    <img src="<?= App::asset('/assets/img/logo.svg') ?>" alt="Academia Iquique">
</section>
<div class="stats-grid">
    <article class="stat-card"><span>Usuarios activos</span><strong><?= h($activeUsers) ?></strong><em>● Operativos</em></article>
    <article class="stat-card"><span>Roles configurados</span><strong><?= h($rolesCount) ?></strong><em>● Privilegios</em></article>
    <article class="stat-card"><span>Permisos disponibles</span><strong><?= h($permissionsCount) ?></strong><em>● Seguridad</em></article>
    <article class="stat-card accent"><span>Último acceso</span><strong><?= h(Auth::user()['last_login_at'] ?? 'Ahora') ?></strong><em>● Sesión segura</em></article>
</div>
<div class="grid-2">
    <section class="panel-card">
        <div class="section-head"><h3>Accesos rápidos</h3></div>
        <div class="quick-actions">
            <?php if (Auth::can('crear_usuarios')): ?><a href="<?= App::url('/users/create') ?>">Crear usuario <span>→</span></a><?php endif; ?>
            <?php if (Auth::can('gestionar_roles')): ?><a href="<?= App::url('/roles') ?>">Roles y permisos <span>→</span></a><?php endif; ?>
            <?php if (Auth::can('gestionar_usuarios')): ?><a href="<?= App::url('/users') ?>">Directorio interno <span>→</span></a><?php endif; ?>
            <?php if (Auth::can('configurar_postulaciones')): ?><a href="<?= App::url('/admission-settings') ?>">Configurar postulaciones <span>→</span></a><?php endif; ?>
            <a href="<?= App::url('/postula.php') ?>" target="_blank" rel="noopener">Ver postula.php <span>↗</span></a>
        </div>
    </section>
    <section class="panel-card" id="actividad">
        <div class="section-head"><h3>Actividad reciente</h3></div>
        <div class="activity-list">
            <?php foreach ($activity as $item): ?>
                <div class="activity-item"><span></span><div><strong><?= h($item['action']) ?></strong><p><?= h($item['description']) ?></p><small><?= h($item['user_name'] ?? 'Sistema') ?> · <?= h($item['created_at']) ?></small></div></div>
            <?php endforeach; ?>
            <?php if (!$activity): ?><p class="muted-text">Sin actividad registrada aún.</p><?php endif; ?>
        </div>
    </section>
</div>
