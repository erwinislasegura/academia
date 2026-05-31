<section class="hero-card compact-hero">
    <div>
        <p class="eyebrow">Admisión 2026</p>
        <h2>Postulaciones recibidas</h2>
        <p>Revisa en una tabla compacta los datos registrados desde el formulario público.</p>
    </div>
    <div class="hero-actions">
        <a class="btn primary" href="<?= App::url('/admissions/export') ?>">Exportar Excel</a>
        <a class="btn secondary" href="<?= App::url('/admission-settings') ?>">Configuración</a>
    </div>
</section>

<section class="panel-card compact-panel">
    <div class="section-head compact-head">
        <div>
            <h3>Solicitudes registradas</h3>
            <p><?= h((string) $totalApplications) ?> postulaciones ordenadas desde la más reciente.</p>
        </div>
        <span class="badge role"><?= h((string) $totalApplications) ?> total</span>
    </div>

    <div class="table-responsive">
        <table class="modern-table compact-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Apoderado</th>
                    <th>Contacto</th>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Mensaje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                    <?php $guardian = trim(($application['guardian_first_names'] ?? '') . ' ' . ($application['guardian_last_names'] ?? '')); ?>
                    <tr>
                        <td><strong><?= h($application['created_at'] ?? '') ?></strong></td>
                        <td>
                            <strong><?= h($guardian) ?></strong>
                            <span>#<?= h($application['id'] ?? '') ?></span>
                        </td>
                        <td>
                            <strong><?= h($application['guardian_email'] ?? '') ?></strong>
                            <span><?= h($application['guardian_phone'] ?? '') ?></span>
                        </td>
                        <td><strong><?= h($application['student_name'] ?? '') ?></strong></td>
                        <td><span class="badge ok"><?= h($application['course'] ?? '') ?></span></td>
                        <td class="message-cell"><?= h($application['message'] ?: 'Sin mensaje adicional') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$applications): ?>
                    <tr><td colspan="6" class="empty">Aún no hay postulaciones registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
