<section class="hero-card compact-hero">
    <div>
        <p class="eyebrow">Proceso de postulación 2027</p>
        <h2>Postulaciones recibidas</h2>
        <p>Revisa, clasifica y actualiza el estado de cada solicitud desde el formulario público.</p>
    </div>
    <div class="hero-actions">
        <a class="btn primary" href="<?= App::url('/admissions/export') ?>">Exportar Excel</a>
        <a class="btn secondary" href="<?= App::url('/admission-statuses') ?>">Estados</a>
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
        <table class="modern-table compact-table admissions-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Apoderado</th>
                    <th>Contacto</th>
                    <th>Estudiante</th>
                    <th>Postulante</th>
                    <th>Nacimiento</th>
                    <th>Edad</th>
                    <th>Curso</th>
                    <th>Estado</th>
                    <th class="table-action-head">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                    <?php
                        $guardian = trim(($application['guardian_first_names'] ?? '') . ' ' . ($application['guardian_last_names'] ?? ''));
                        $message = trim((string) ($application['message'] ?? ''));
                        $messageLabel = $message !== '' ? 'Ver mensaje' : 'Sin mensaje';
                        $createdAt = (string) ($application['created_at'] ?? '');
                        $createdTimestamp = $createdAt !== '' ? strtotime($createdAt) : false;
                        $createdDate = $createdTimestamp !== false ? date('d/m/Y', $createdTimestamp) : 'Sin fecha';
                        $createdTime = $createdTimestamp !== false ? date('H:i', $createdTimestamp) : '';
                    ?>
                    <tr>
                        <td>
                            <time class="date-stack" datetime="<?= h($createdAt) ?>">
                                <span><?= h($createdDate) ?></span>
                                <?php if ($createdTime !== ''): ?><small><?= h($createdTime) ?> hrs</small><?php endif; ?>
                            </time>
                        </td>
                        <td>
                            <span class="table-primary-data"><?= h($guardian) ?></span>
                            <span>#<?= h($application['id'] ?? '') ?></span>
                        </td>
                        <td>
                            <span class="table-primary-data"><?= h($application['guardian_email'] ?? '') ?></span>
                            <span><?= h($application['guardian_phone'] ?? '') ?></span>
                        </td>
                        <td><strong><?= h($application['student_name'] ?? '') ?></strong></td>
                        <td><span class="gender-pill"><?= h(($application['student_gender'] ?? '') === 'nina' ? 'Niña' : (($application['student_gender'] ?? '') === 'nino' ? 'Niño' : 'Sin dato')) ?></span></td>
                        <td><?= h(!empty($application['student_birthdate']) ? date('d/m/Y', strtotime((string) $application['student_birthdate'])) : 'Sin dato') ?></td>
                        <td><strong><?= h(($application['student_age'] ?? null) !== null ? $application['student_age'] . ' años' : 'Sin edad') ?></strong></td>
                        <td><span class="badge ok"><?= h($application['course'] ?? '') ?></span></td>
                        <td>
                            <form class="status-form" method="post" action="<?= App::url('/admissions/status/' . h($application['id'])) ?>">
                                <span class="status-dot" style="--status-color: <?= h($application['status_color'] ?? '#94a3b8') ?>"></span>
                                <select name="status_id" aria-label="Estado de la postulación #<?= h($application['id'] ?? '') ?>" onchange="this.form.submit()">
                                    <option value="">Sin estado</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?= h($status['id']) ?>" <?= (string) ($application['status_id'] ?? '') === (string) $status['id'] ? 'selected' : '' ?>><?= h($status['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <noscript><button class="btn secondary">Guardar</button></noscript>
                            </form>
                        </td>
                        <td class="table-action-cell">
                            <details class="action-dropdown">
                                <summary>Acciones</summary>
                                <div class="action-dropdown__menu">
                                    <button
                                        class="action-dropdown__item message-modal-trigger"
                                        type="button"
                                        data-applicant="<?= h($guardian !== '' ? $guardian : ('Postulación #' . ($application['id'] ?? ''))) ?>"
                                        data-student="<?= h($application['student_name'] ?? '') ?>"
                                        data-message="<?= h($message !== '' ? $message : 'Sin mensaje adicional') ?>"
                                    ><?= h($messageLabel) ?></button>
                                    <a class="action-dropdown__item" href="<?= App::url('/admissions/edit/' . h($application['id'])) ?>">Editar</a>
                                    <form method="post" action="<?= App::url('/admissions/delete/' . h($application['id'])) ?>" data-confirm="¿Eliminar esta postulación? Esta acción no se puede deshacer.">
                                        <button class="action-dropdown__item danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$applications): ?>
                    <tr><td colspan="10" class="empty">Aún no hay postulaciones registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>


<dialog class="admission-message-modal" id="admission-message-modal" aria-labelledby="admission-message-title">
    <div class="admission-message-modal__header">
        <div>
            <span class="eyebrow">Mensaje de postulación</span>
            <h3 id="admission-message-title">Detalle del mensaje</h3>
            <p id="admission-message-meta"></p>
        </div>
        <button class="modal-close" type="button" data-message-modal-close aria-label="Cerrar modal">×</button>
    </div>
    <div class="admission-message-modal__body" id="admission-message-body"></div>
    <div class="admission-message-modal__footer">
        <button class="btn primary" type="button" data-message-modal-close>Cerrar</button>
    </div>
</dialog>
