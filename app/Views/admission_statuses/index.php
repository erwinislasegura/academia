<section class="hero-card compact-hero">
    <div>
        <p class="eyebrow">Flujo de admisión</p>
        <h2>Estados de postulación</h2>
        <p>Crea y ordena los estados disponibles para gestionar cada solicitud recibida.</p>
    </div>
    <div class="hero-actions">
        <a class="btn secondary" href="<?= App::url('/admissions') ?>">Ver postulaciones</a>
        <a class="btn primary" href="<?= App::url('/admission-statuses/create') ?>">+ Nuevo estado</a>
    </div>
</section>

<section class="panel-card compact-panel">
    <div class="section-head compact-head">
        <div>
            <h3>Estados configurados</h3>
            <p>Los estados activos aparecen en el selector de cada postulación.</p>
        </div>
        <span class="badge role"><?= h((string) count($statuses)) ?> estados</span>
    </div>

    <div class="table-responsive">
        <table class="modern-table compact-table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Slug</th>
                    <th>Orden</th>
                    <th>Uso</th>
                    <th>Disponibilidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statuses as $status): ?>
                    <tr>
                        <td>
                            <span class="status-dot" style="--status-color: <?= h($status['color'] ?? '#071D7A') ?>"></span>
                            <strong><?= h($status['name']) ?></strong>
                            <span><?= h($status['description'] ?: 'Sin descripción') ?></span>
                        </td>
                        <td><span class="badge role"><?= h($status['slug']) ?></span></td>
                        <td><?= h((string) $status['sort_order']) ?></td>
                        <td><?= h((string) $status['applications_count']) ?> postulaciones</td>
                        <td><span class="badge <?= !empty($status['is_active']) ? 'ok' : 'off' ?>"><?= !empty($status['is_active']) ? 'Activo' : 'Inactivo' ?></span></td>
                        <td class="actions">
                            <a href="<?= App::url('/admission-statuses/edit/' . h($status['id'])) ?>">Editar</a>
                            <form method="post" action="<?= App::url('/admission-statuses/delete/' . h($status['id'])) ?>" data-confirm="¿Eliminar estado?">
                                <button class="danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$statuses): ?>
                    <tr><td colspan="6" class="empty">Aún no hay estados configurados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
