<section class="hero-card compact-hero">
    <div>
        <p class="eyebrow">Formulario público</p>
        <h2>Cursos de admisión</h2>
        <p>Agrega cursos al formulario, habilita o deshabilita cupos y destaca los niveles con nuevos cupos disponibles.</p>
    </div>
    <div class="hero-actions">
        <a class="btn secondary" href="<?= App::url('/postula.php') ?>" target="_blank" rel="noopener">Ver formulario</a>
        <a class="btn primary" href="<?= App::url('/admission-courses/create') ?>">+ Nuevo curso</a>
    </div>
</section>

<section class="panel-card compact-panel">
    <div class="section-head compact-head">
        <div>
            <h3>Cursos configurados</h3>
            <p>Solo los cursos activos aparecen como opción seleccionable en el formulario público.</p>
        </div>
        <span class="badge role"><?= h((string) count($courses)) ?> cursos</span>
    </div>

    <div class="table-responsive">
        <table class="modern-table compact-table">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th>Slug</th>
                    <th>Orden</th>
                    <th>Uso</th>
                    <th>Estado</th>
                    <th>Cupos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td>
                            <strong><?= h($course['name']) ?></strong>
                            <span><?= !empty($course['is_active']) ? 'Disponible en el formulario' : 'Oculto para nuevas postulaciones' ?></span>
                        </td>
                        <td><span class="badge role"><?= h($course['slug']) ?></span></td>
                        <td><?= h((string) $course['sort_order']) ?></td>
                        <td><?= h((string) $course['applications_count']) ?> postulaciones</td>
                        <td><span class="badge <?= !empty($course['is_active']) ? 'ok' : 'off' ?>"><?= !empty($course['is_active']) ? 'Activo' : 'Inactivo' ?></span></td>
                        <td><span class="badge <?= !empty($course['is_new_slots']) ? 'ok' : 'off' ?>"><?= !empty($course['is_new_slots']) ? 'Nuevos cupos' : 'Regular' ?></span></td>
                        <td class="actions">
                            <a href="<?= App::url('/admission-courses/edit/' . h($course['id'])) ?>">Editar</a>
                            <form method="post" action="<?= App::url('/admission-courses/delete/' . h($course['id'])) ?>" data-confirm="¿Eliminar curso?">
                                <button class="danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$courses): ?>
                    <tr><td colspan="7" class="empty">Aún no hay cursos configurados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
