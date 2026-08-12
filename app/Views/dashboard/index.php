<?php
if (!function_exists('dashboardMax')) {
    function dashboardMax(array $rows): int
    {
        $values = array_map(static fn ($row) => (int) ($row['total'] ?? 0), $rows);
        return max([1, ...$values]);
    }
}
if (!function_exists('dashboardTotal')) {
    function dashboardTotal(array $rows): int
    {
        return array_sum(array_map(static fn ($row) => (int) ($row['total'] ?? 0), $rows));
    }
}
if (!function_exists('dashboardDuration')) {
    function dashboardDuration(?int $seconds): string
    {
        if ($seconds === null) {
            return 'Sin datos aún';
        }
        $seconds = max(0, $seconds);
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        if ($days > 0) {
            return $days . ' ' . ($days === 1 ? 'día' : 'días') . ($hours > 0 ? ' ' . $hours . ' h' : '');
        }
        return $hours > 0 ? $hours . ' ' . ($hours === 1 ? 'hora' : 'horas') : 'Menos de 1 hora';
    }
}
$metrics = $admissionMetrics ?? ['total' => 0, 'new_this_week' => 0, 'contact_rate' => 0, 'acceptance_rate' => 0, 'girls' => 0, 'boys' => 0, 'without_gender' => 0, 'with_gender' => 0];
$totalApplicants = max(1, (int) ($metrics['total'] ?? 0));
$courseMax = dashboardMax($applicationsByCourse ?? []);
$statusTotal = max(1, dashboardTotal($applicationsByStatus ?? []));
$trendMax = dashboardMax($applicationsTrend ?? []);
$contactRate = min(100, (float) ($metrics['contact_rate'] ?? 0));
$acceptanceRate = min(100, (float) ($metrics['acceptance_rate'] ?? 0));
$girlsPercent = round(((int) ($metrics['girls'] ?? 0) / $totalApplicants) * 100);
$boysPercent = round(((int) ($metrics['boys'] ?? 0) / $totalApplicants) * 100);
$withoutGenderPercent = round(((int) ($metrics['without_gender'] ?? 0) / $totalApplicants) * 100);
$averageTimesMap = [];
foreach (($averageTimesByStatus ?? []) as $averageTime) {
    $averageTimesMap[(string) ($averageTime['label'] ?? '')] = $averageTime;
}
?>
<section class="admission-dashboard admission-dashboard--executive">
    <div class="admission-dashboard__hero">
        <div>
            <p class="eyebrow light">Admisión 2027 · Panel ejecutivo</p>
            <h2>Resumen de postulaciones</h2>
            <p>Indicadores depurados para revisar volumen, seguimiento y distribución. Se eliminaron tarjetas repetidas y cruces secundarios para facilitar la lectura.</p>
        </div>
        <div class="admission-dashboard__actions">
            <?php if (Auth::can('configurar_postulaciones')): ?>
                <a class="btn primary" href="<?= App::url('/admissions/export') ?>">Exportar informe</a>
                <a class="btn secondary" href="<?= App::url('/admissions') ?>">Gestionar postulantes</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-summary-grid dashboard-summary-grid--executive">
        <article class="summary-card summary-card--total">
            <span>Total postulantes</span>
            <strong><?= h($metrics['total']) ?></strong>
            <small>Proceso escolar 2027</small>
        </article>
        <article class="summary-card">
            <span>Nuevos esta semana</span>
            <strong><?= h($metrics['new_this_week'] ?? 0) ?></strong>
            <small>Ingresos recientes</small>
        </article>
        <article class="summary-card summary-card--ring" style="--ring-angle: <?= h((string) ($contactRate * 3.6)) ?>deg">
            <span>Contactabilidad</span>
            <strong><?= h((string) round($contactRate)) ?>%</strong>
            <small>Contactada + aceptada</small>
        </article>
        <article class="summary-card summary-card--success" style="--ring-angle: <?= h((string) ($acceptanceRate * 3.6)) ?>deg">
            <span>Aceptación</span>
            <strong><?= h((string) round($acceptanceRate)) ?>%</strong>
            <small>Postulaciones aceptadas</small>
        </article>
        <article class="summary-card summary-card--alert">
            <span>Datos por completar</span>
            <strong><?= h($metrics['without_gender'] ?? 0) ?></strong>
            <small><?= h((string) $withoutGenderPercent) ?>% sin sexo informado</small>
        </article>
    </div>

    <div class="dashboard-report-grid dashboard-report-grid--executive">
        <article class="report-card report-card--large report-card--priority">
            <div class="report-card__head">
                <div><h3>Demanda por curso</h3><p>Cursos ordenados por número de postulantes y peso sobre el total.</p></div>
                <span><?= h((string) ($metrics['total'] ?? 0)) ?> postulantes</span>
            </div>
            <div class="course-ranking course-ranking--executive">
                <?php foreach (($applicationsByCourse ?? []) as $row): ?>
                    <?php $percent = ((int) $row['total'] / $totalApplicants) * 100; $width = ((int) $row['total'] / $courseMax) * 100; ?>
                    <div class="course-ranking__row">
                        <div><strong><?= h($row['label']) ?></strong><small><?= h((string) round($percent)) ?>% del total</small></div>
                        <span aria-label="<?= h((string) round($percent)) ?>%"><i style="width: <?= h((string) $width) ?>%"></i></span>
                        <b><?= h($row['total']) ?></b>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($applicationsByCourse)): ?><p class="muted-text">Aún no hay postulaciones para graficar.</p><?php endif; ?>
            </div>
        </article>

        <article class="report-card report-card--chart">
            <div class="report-card__head"><div><h3>Tendencia diaria</h3><p>Ingresos por día. Barras más altas indican mayor actividad.</p></div></div>
            <div class="spark-columns spark-columns--readable">
                <?php foreach (($applicationsTrend ?? []) as $row): ?>
                    <?php $height = max(12, ((int) $row['total'] / $trendMax) * 100); ?>
                    <div title="<?= h($row['label']) ?> · <?= h($row['total']) ?> postulaciones"><span style="height: <?= h((string) $height) ?>%"><b><?= h($row['total']) ?></b></span><small><?= h(date('d/m', strtotime($row['label']))) ?></small></div>
                <?php endforeach; ?>
                <?php if (empty($applicationsTrend)): ?><p class="muted-text">Sin datos recientes.</p><?php endif; ?>
            </div>
        </article>

        <article class="report-card">
            <div class="report-card__head"><div><h3>Distribución por sexo</h3><p>Dato general para control de completitud, sin duplicar tarjetas.</p></div></div>
            <div class="gender-summary">
                <div class="gender-summary__bar" aria-label="Niñas <?= h((string) $girlsPercent) ?>%, niños <?= h((string) $boysPercent) ?>%, sin dato <?= h((string) $withoutGenderPercent) ?>%">
                    <i class="girls" style="width: <?= h((string) $girlsPercent) ?>%"></i><i class="boys" style="width: <?= h((string) $boysPercent) ?>%"></i><i class="unknown" style="width: <?= h((string) $withoutGenderPercent) ?>%"></i>
                </div>
                <div class="dashboard-legend dashboard-legend--compact">
                    <div><span class="girls"></span><b>Niñas</b><em><?= h($metrics['girls'] ?? 0) ?> · <?= h((string) $girlsPercent) ?>%</em></div>
                    <div><span class="boys"></span><b>Niños</b><em><?= h($metrics['boys'] ?? 0) ?> · <?= h((string) $boysPercent) ?>%</em></div>
                    <div><span class="unknown"></span><b>Sin dato</b><em><?= h($metrics['without_gender'] ?? 0) ?> · <?= h((string) $withoutGenderPercent) ?>%</em></div>
                </div>
            </div>
        </article>

        <article class="report-card">
            <div class="report-card__head"><div><h3>Seguimiento por estado</h3><p>Estado actual y promedio de tiempo para avanzar.</p></div></div>
            <div class="status-funnel status-funnel--executive">
                <?php foreach (($applicationsByStatus ?? []) as $row): ?>
                    <?php
                        $percent = ((int) $row['total'] / $statusTotal) * 100;
                        $average = $averageTimesMap[(string) ($row['label'] ?? '')] ?? null;
                        $hasAverage = $average !== null && (int) ($average['transitions'] ?? 0) > 0;
                    ?>
                    <div class="status-funnel__row" style="--status-color: <?= h($row['color'] ?? '#071D7A') ?>">
                        <div>
                            <strong><?= h($row['label']) ?></strong>
                            <small><?= h($row['total']) ?> · <?= h((string) round($percent)) ?>%</small>
                            <?php if ($hasAverage): ?>
                                <small class="status-average">Promedio etapa: <?= h(dashboardDuration((int) $average['average_stage_seconds'])) ?> · Acumulado: <?= h(dashboardDuration((int) $average['average_total_seconds'])) ?></small>
                            <?php else: ?>
                                <small class="status-average muted">Sin cambios suficientes para calcular el promedio</small>
                            <?php endif; ?>
                        </div>
                        <span><i style="width: <?= h((string) $percent) ?>%"></i></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($applicationsByStatus)): ?><p class="muted-text">Sin estados registrados.</p><?php endif; ?>
            </div>
        </article>

        <article class="report-card report-card--large">
            <div class="report-card__head"><div><h3>Últimos postulantes</h3><p>Listado breve para gestión diaria.</p></div></div>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table dashboard-table--recent">
                    <thead><tr><th>Postulante</th><th>Curso</th><th>Estado</th><th>Ingreso</th></tr></thead>
                    <tbody>
                        <?php foreach (($latestApplications ?? []) as $item): ?>
                            <tr>
                                <td><span class="student-avatar"><?= h(substr((string) ($item['student_name'] ?? 'P'), 0, 1)) ?></span><strong><?= h($item['student_name']) ?></strong></td>
                                <td><?= h($item['course']) ?></td>
                                <td><span class="dashboard-status" style="--status-color: <?= h($item['status_color'] ?? '#071D7A') ?>"><?= h($item['status_name'] ?? 'Sin estado') ?></span></td>
                                <td><?= h($item['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($latestApplications)): ?><tr><td colspan="4" class="empty">Aún no hay postulantes recientes.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <article class="report-card accepted-timeline-card">
        <div class="report-card__head">
            <div>
                <h3>Línea de tiempo de postulaciones aceptadas</h3>
                <p>Tiempo transcurrido entre la recepción de la postulación y el cambio de estado a Aceptada.</p>
            </div>
            <span class="accepted-timeline-card__count"><?= h((string) count($acceptedApplicationTimelines ?? [])) ?> registros</span>
        </div>
        <div class="accepted-timeline-list">
            <?php foreach (($acceptedApplicationTimelines ?? []) as $timeline): ?>
                <?php
                    $receivedAt = !empty($timeline['received_at']) ? strtotime((string) $timeline['received_at']) : false;
                    $acceptedAt = !empty($timeline['accepted_at']) ? strtotime((string) $timeline['accepted_at']) : false;
                    $hasAcceptedDate = $acceptedAt !== false;
                ?>
                <div class="accepted-timeline-item">
                    <div class="accepted-timeline-person">
                        <span class="student-avatar"><?= h(substr((string) ($timeline['student_name'] ?? 'P'), 0, 1)) ?></span>
                        <div>
                            <strong><?= h($timeline['student_name'] ?? 'Postulante') ?></strong>
                            <small><?= h($timeline['course'] ?? 'Sin curso') ?> · #<?= h($timeline['id'] ?? '') ?></small>
                        </div>
                    </div>
                    <div class="accepted-timeline-track <?= $hasAcceptedDate ? '' : 'is-pending-history' ?>">
                        <div class="accepted-timeline-point">
                            <i></i>
                            <span>Recepción</span>
                            <strong><?= h($receivedAt !== false ? date('d/m/Y H:i', $receivedAt) : 'Sin fecha') ?></strong>
                        </div>
                        <div class="accepted-timeline-elapsed">
                            <span><?= $hasAcceptedDate ? h(dashboardDuration((int) $timeline['elapsed_seconds'])) : 'Historial no disponible' ?></span>
                        </div>
                        <div class="accepted-timeline-point accepted">
                            <i></i>
                            <span>Aceptada</span>
                            <strong><?= h($hasAcceptedDate ? date('d/m/Y H:i', $acceptedAt) : 'Fecha no registrada') ?></strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($acceptedApplicationTimelines)): ?>
                <p class="muted-text accepted-timeline-empty">Todavía no existen postulaciones aceptadas para mostrar.</p>
            <?php endif; ?>
        </div>
    </article>
</section>
