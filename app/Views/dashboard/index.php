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
$metrics = $admissionMetrics ?? ['total' => 0, 'new_this_week' => 0, 'contact_rate' => 0, 'acceptance_rate' => 0];
$totalApplicants = max(1, (int) ($metrics['total'] ?? 0));
$courseMax = dashboardMax($applicationsByCourse ?? []);
$statusTotal = max(1, dashboardTotal($applicationsByStatus ?? []));
$genderTotal = max(1, dashboardTotal($applicationsByGender ?? []));
$ageMax = dashboardMax($applicationsByAgeRange ?? []);
$trendMax = dashboardMax($applicationsTrend ?? []);
$acceptedAngle = min(100, (float) ($metrics['acceptance_rate'] ?? 0)) * 3.6;
$contactAngle = min(100, (float) ($metrics['contact_rate'] ?? 0)) * 3.6;
$genderOffset = 0;
?>
<section class="admission-dashboard">
    <div class="admission-dashboard__hero">
        <div>
            <p class="eyebrow light">Admisión 2027 · KPI</p>
            <h2>Panel profesional de postulantes</h2>
            <p>Indicadores ejecutivos, gráficos y tablas para controlar captación, seguimiento por estado, demanda por curso y composición de los postulantes.</p>
        </div>
        <div class="admission-dashboard__actions">
            <?php if (Auth::can('configurar_postulaciones')): ?>
                <a class="btn primary" href="<?= App::url('/admissions/export') ?>">Exportar informe</a>
                <a class="btn secondary" href="<?= App::url('/admissions') ?>">Gestionar postulantes</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-summary-grid">
        <article class="summary-card summary-card--total">
            <span>Total postulantes</span>
            <strong><?= h($metrics['total']) ?></strong>
            <small>Proceso escolar 2027</small>
        </article>
        <article class="summary-card">
            <span>Nuevos 7 días</span>
            <strong><?= h($metrics['new_this_week']) ?></strong>
            <small>Ingresos recientes</small>
        </article>
        <article class="summary-card summary-card--ring" style="--ring-angle: <?= h((string) $contactAngle) ?>deg">
            <span>Contactabilidad</span>
            <strong><?= h($metrics['contact_rate']) ?>%</strong>
            <small>Contactada + aceptada</small>
        </article>
        <article class="summary-card summary-card--ring accent-ring" style="--ring-angle: <?= h((string) $acceptedAngle) ?>deg">
            <span>Aceptación</span>
            <strong><?= h($metrics['acceptance_rate']) ?>%</strong>
            <small>Postulantes aceptados</small>
        </article>
    </div>

    <div class="dashboard-report-grid">
        <article class="report-card report-card--large">
            <div class="report-card__head">
                <div><h3>Postulantes por curso</h3><p>Ranking de demanda y participación sobre el total.</p></div>
                <span><?= h((string) ($metrics['total'] ?? 0)) ?> postulantes</span>
            </div>
            <div class="course-ranking">
                <?php foreach (($applicationsByCourse ?? []) as $row): ?>
                    <?php $percent = ((int) $row['total'] / $totalApplicants) * 100; $width = ((int) $row['total'] / $courseMax) * 100; ?>
                    <div class="course-ranking__row">
                        <div><strong><?= h($row['label']) ?></strong><small><?= h($row['total']) ?> postulantes · <?= h((string) round($percent)) ?>%</small></div>
                        <span><i style="width: <?= h((string) $width) ?>%"></i></span>
                        <b><?= h($row['total']) ?></b>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($applicationsByCourse)): ?><p class="muted-text">Aún no hay postulaciones para graficar.</p><?php endif; ?>
            </div>
        </article>

        <article class="report-card report-card--chart">
            <div class="report-card__head"><div><h3>Ingresos diarios</h3><p>Últimos días con postulaciones.</p></div></div>
            <div class="spark-columns">
                <?php foreach (($applicationsTrend ?? []) as $row): ?>
                    <?php $height = max(10, ((int) $row['total'] / $trendMax) * 100); ?>
                    <div title="<?= h($row['label']) ?> · <?= h($row['total']) ?> postulaciones"><span style="height: <?= h((string) $height) ?>%"></span><small><?= h(date('d/m', strtotime($row['label']))) ?></small></div>
                <?php endforeach; ?>
                <?php if (empty($applicationsTrend)): ?><p class="muted-text">Sin datos recientes.</p><?php endif; ?>
            </div>
        </article>

        <article class="report-card report-card--chart">
            <div class="report-card__head"><div><h3>Niño / Niña</h3><p>Distribución declarada.</p></div></div>
            <div class="gender-donut-wrap">
                <div class="gender-donut">
                    <?php foreach (($applicationsByGender ?? []) as $row): ?>
                        <?php $slice = ((int) $row['total'] / $genderTotal) * 100; ?>
                        <i style="--start: <?= h((string) $genderOffset) ?>%; --end: <?= h((string) ($genderOffset + $slice)) ?>%;"></i>
                        <?php $genderOffset += $slice; ?>
                    <?php endforeach; ?>
                    <strong><?= h(empty($applicationsByGender) ? 0 : $genderTotal) ?></strong>
                </div>
                <div class="dashboard-legend">
                    <?php foreach (($applicationsByGender ?? []) as $index => $row): ?>
                        <div><span class="tone-<?= h((string) (($index % 3) + 1)) ?>"></span><b><?= h($row['label']) ?></b><em><?= h($row['total']) ?></em></div>
                    <?php endforeach; ?>
                    <?php if (empty($applicationsByGender)): ?><p class="muted-text">Sin datos de sexo todavía.</p><?php endif; ?>
                </div>
            </div>
        </article>

        <article class="report-card">
            <div class="report-card__head"><div><h3>Embudo por estado</h3><p>Avance del seguimiento.</p></div></div>
            <div class="status-funnel">
                <?php foreach (($applicationsByStatus ?? []) as $row): ?>
                    <?php $percent = ((int) $row['total'] / $statusTotal) * 100; ?>
                    <div class="status-funnel__row" style="--status-color: <?= h($row['color'] ?? '#071D7A') ?>">
                        <div><strong><?= h($row['label']) ?></strong><small><?= h($row['total']) ?> casos</small></div>
                        <span><i style="width: <?= h((string) $percent) ?>%"></i></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($applicationsByStatus)): ?><p class="muted-text">Sin estados registrados.</p><?php endif; ?>
            </div>
        </article>

        <article class="report-card">
            <div class="report-card__head"><div><h3>Edades</h3><p>Rangos calculados por nacimiento.</p></div></div>
            <div class="age-distribution">
                <?php foreach (($applicationsByAgeRange ?? []) as $row): ?>
                    <?php $percent = ((int) $row['total'] / $ageMax) * 100; ?>
                    <div><label><b><?= h($row['label']) ?></b><small><?= h($row['total']) ?></small></label><span><i style="width: <?= h((string) $percent) ?>%"></i></span></div>
                <?php endforeach; ?>
                <?php if (empty($applicationsByAgeRange)): ?><p class="muted-text">Sin fechas de nacimiento registradas.</p><?php endif; ?>
            </div>
        </article>

        <article class="report-card report-card--large">
            <div class="report-card__head"><div><h3>Últimos postulantes</h3><p>Tabla rápida para gestión diaria.</p></div></div>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
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
</section>
