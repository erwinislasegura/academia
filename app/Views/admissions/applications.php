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

<?php
    $formatProcessDuration = static function (?int $seconds): string {
        if ($seconds === null) {
            return 'Sin datos aún';
        }
        $seconds = max(0, $seconds);
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        if ($days > 0) {
            return $days . ' ' . ($days === 1 ? 'día' : 'días') . ($hours > 0 ? ' ' . $hours . ' h' : '');
        }
        if ($hours > 0) {
            return $hours . ' ' . ($hours === 1 ? 'hora' : 'horas');
        }
        return 'Menos de 1 hora';
    };
    $formatCompactDuration = static function (?int $seconds): string {
        if ($seconds === null) {
            return 'Sin dato';
        }
        $seconds = max(0, $seconds);
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        if ($days > 0) {
            return $days . ' d' . ($hours > 0 ? ' ' . $hours . ' h' : '');
        }
        if ($hours > 0) {
            return $hours . ' h';
        }
        return '< 1 h';
    };
?>

<section class="panel-card compact-panel admission-averages">
    <div class="section-head compact-head">
        <div>
            <h3>Promedio de avance por estado</h3>
            <p>Tiempo promedio de cada transición y total acumulado desde que se recibió la postulación.</p>
        </div>
    </div>
    <div class="admission-averages__grid">
        <?php foreach (($averageTimesByStatus ?? []) as $average): ?>
            <?php $hasAverage = (int) ($average['transitions'] ?? 0) > 0; ?>
            <article class="admission-average" style="--status-color: <?= h($average['color'] ?? '#94a3b8') ?>">
                <span class="status-dot"></span>
                <div>
                    <strong><?= h($average['label'] ?? 'Sin estado') ?></strong>
                    <?php if ($hasAverage): ?>
                        <small>Etapa: <?= h($formatProcessDuration((int) $average['average_stage_seconds'])) ?></small>
                        <small>Total acumulado: <?= h($formatProcessDuration((int) $average['average_total_seconds'])) ?></small>
                    <?php else: ?>
                        <small>Sin transiciones registradas</small>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
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
        <table class="modern-table compact-table admissions-table admissions-table--redesigned">
            <thead>
                <tr>
                    <th class="followup-column">Seguimiento</th>
                    <th>Postulación</th>
                    <th>Estudiante</th>
                    <th>Apoderado</th>
                    <th>Contacto</th>
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
                        <td class="followup-column" data-label="Seguimiento">
                            <?php
                                $timelineEvents = $application['status_timeline'] ?? [];
                                $stageCount = count($timelineEvents);
                            ?>
                            <details class="admission-progress">
                                <summary>
                                    <span class="admission-progress__current">
                                        <i style="--stage-color: <?= h($application['status_color'] ?? '#94a3b8') ?>"></i>
                                        <span>
                                            <b><?= h($application['status_name'] ?? 'Sin estado') ?></b>
                                            <small><?= h($formatProcessDuration((int) $application['total_elapsed_seconds'])) ?> en proceso</small>
                                        </span>
                                    </span>
                                    <span class="admission-progress__action">
                                        <small><?= h((string) $stageCount) ?> <?= $stageCount === 1 ? 'etapa' : 'etapas' ?></small>
                                        <b>Ver recorrido</b>
                                    </span>
                                </summary>
                                <div class="admission-progress__history">
                                    <?php foreach ($timelineEvents as $index => $event): ?>
                                        <?php
                                            $eventTimestamp = !empty($event['changed_at']) ? strtotime((string) $event['changed_at']) : false;
                                            $eventDate = $eventTimestamp !== false ? date('d/m/Y · H:i', $eventTimestamp) : 'Fecha no disponible';
                                            $isHistorical = !empty($event['is_migrated']);
                                        ?>
                                        <div class="admission-progress__event<?= $isHistorical ? ' is-historical' : '' ?>">
                                            <i style="--stage-color: <?= h($event['status_color'] ?? '#94a3b8') ?>"></i>
                                            <span>
                                                <b><?= h($event['status_name'] ?? 'Sin estado') ?></b>
                                                <small><?= h($eventDate) ?></small>
                                            </span>
                                            <?php if ($index > 0): ?>
                                                <em><?= h($formatCompactDuration(($event['duration_seconds'] ?? null) !== null ? (int) $event['duration_seconds'] : null)) ?></em>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </td>
                        <td class="application-column" data-label="Postulación">
                            <time class="date-stack" datetime="<?= h($createdAt) ?>">
                                <span><?= h($createdDate) ?></span>
                                <?php if ($createdTime !== ''): ?><small><?= h($createdTime) ?> hrs</small><?php endif; ?>
                            </time>
                            <span class="application-id">#<?= h($application['id'] ?? '') ?></span>
                        </td>
                        <td class="student-column" data-label="Estudiante">
                            <strong><?= h($application['student_name'] ?? '') ?></strong>
                            <span class="student-meta">
                                <?= h(($application['student_gender'] ?? '') === 'nina' ? 'Niña' : (($application['student_gender'] ?? '') === 'nino' ? 'Niño' : 'Sin dato')) ?>
                                · <?= h(($application['student_age'] ?? null) !== null ? $application['student_age'] . ' años' : 'Edad no registrada') ?>
                            </span>
                            <span class="badge ok"><?= h($application['course'] ?? '') ?></span>
                        </td>
                        <td class="guardian-column" data-label="Apoderado">
                            <span class="table-primary-data"><?= h($guardian) ?></span>
                            <small>Responsable de la postulación</small>
                        </td>
                        <td class="contact-column" data-label="Contacto">
                            <span class="table-primary-data"><?= h($application['guardian_email'] ?? '') ?></span>
                            <span><?= h($application['guardian_phone'] ?? '') ?></span>
                        </td>
                        <td class="status-column" data-label="Estado">
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
                        <td class="table-action-cell" data-label="Acciones">
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
                    <tr><td colspan="7" class="empty">Aún no hay postulaciones registradas.</td></tr>
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
