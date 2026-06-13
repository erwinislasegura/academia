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
$courseMax = dashboardMax($applicationsByCourse ?? []);
$statusTotal = max(1, dashboardTotal($applicationsByStatus ?? []));
$genderTotal = max(1, dashboardTotal($applicationsByGender ?? []));
$ageMax = dashboardMax($applicationsByAgeRange ?? []);
$trendMax = dashboardMax($applicationsTrend ?? []);
$genderOffset = 0;
?>
<section class="admission-command">
    <div class="command-copy">
        <p class="eyebrow">Centro de control · Admisión 2027</p>
        <h2>Dashboard ejecutivo de postulantes</h2>
        <p>Vista sobria para revisar captación, avance del proceso, cursos con mayor demanda y composición de postulantes por edad y sexo.</p>
    </div>
    <div class="command-actions">
        <?php if (Auth::can('configurar_postulaciones')): ?>
            <a class="btn primary" href="<?= App::url('/admissions/export') ?>">Exportar informe</a>
            <a class="btn secondary" href="<?= App::url('/admissions') ?>">Ver postulantes</a>
        <?php endif; ?>
    </div>
</section>

<section class="executive-kpis">
    <article class="kpi-card primary-kpi"><span>Total postulantes</span><strong><?= h($metrics['total']) ?></strong><small>Proceso escolar 2027</small></article>
    <article class="kpi-card"><span>Últimos 7 días</span><strong><?= h($metrics['new_this_week']) ?></strong><small>Nuevas solicitudes</small></article>
    <article class="kpi-card"><span>Contactabilidad</span><strong><?= h($metrics['contact_rate']) ?>%</strong><small>Contactada + aceptada</small></article>
    <article class="kpi-card"><span>Aceptación</span><strong><?= h($metrics['acceptance_rate']) ?>%</strong><small>Postulantes aceptados</small></article>
</section>

<section class="dashboard-layout">
    <article class="analytics-card wide-card">
        <div class="analytics-head">
            <div><h3>Postulantes por curso</h3><p>Tabla ejecutiva con volumen y peso relativo por curso.</p></div>
            <a class="mini-link" href="<?= App::url('/admissions/export') ?>">Descargar Excel</a>
        </div>
        <div class="course-table-wrap">
            <table class="course-report-table">
                <thead><tr><th>Curso</th><th>Postulantes</th><th>Demanda</th><th>% máx.</th></tr></thead>
                <tbody>
                    <?php foreach (($applicationsByCourse ?? []) as $row): ?>
                        <?php $percent = ((int) $row['total'] / $courseMax) * 100; ?>
                        <tr>
                            <td><strong><?= h($row['label']) ?></strong></td>
                            <td><?= h($row['total']) ?></td>
                            <td><div class="table-bar"><span style="width: <?= h((string) $percent) ?>%"></span></div></td>
                            <td><?= h((string) round($percent)) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($applicationsByCourse)): ?><tr><td colspan="4" class="empty">Aún no hay postulaciones para graficar.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="analytics-card chart-card">
        <div class="analytics-head"><div><h3>Tendencia diaria</h3><p>Ingresos recientes al formulario.</p></div></div>
        <div class="column-chart" aria-label="Tendencia diaria de postulaciones">
            <?php foreach (($applicationsTrend ?? []) as $row): ?>
                <?php $height = max(8, ((int) $row['total'] / $trendMax) * 100); ?>
                <div class="column-item"><span style="height: <?= h((string) $height) ?>%"></span><small><?= h(date('d/m', strtotime($row['label']))) ?></small></div>
            <?php endforeach; ?>
            <?php if (empty($applicationsTrend)): ?><p class="muted-text">Sin datos recientes.</p><?php endif; ?>
        </div>
    </article>

    <article class="analytics-card chart-card">
        <div class="analytics-head"><div><h3>Distribución niño/niña</h3><p>Composición declarada por familia.</p></div></div>
        <div class="donut-panel">
            <div class="donut-chart" aria-hidden="true">
                <?php foreach (($applicationsByGender ?? []) as $row): ?>
                    <?php $slice = ((int) $row['total'] / $genderTotal) * 100; ?>
                    <i style="--start: <?= h((string) $genderOffset) ?>%; --end: <?= h((string) ($genderOffset + $slice)) ?>%;"></i>
                    <?php $genderOffset += $slice; ?>
                <?php endforeach; ?>
                <strong><?= h($genderTotal === 1 && empty($applicationsByGender) ? 0 : $genderTotal) ?></strong>
            </div>
            <div class="chart-legend">
                <?php foreach (($applicationsByGender ?? []) as $index => $row): ?>
                    <div><span class="legend-dot tone-<?= h((string) (($index % 4) + 1)) ?>"></span><b><?= h($row['label']) ?></b><small><?= h($row['total']) ?></small></div>
                <?php endforeach; ?>
                <?php if (empty($applicationsByGender)): ?><p class="muted-text">Sin datos de sexo todavía.</p><?php endif; ?>
            </div>
        </div>
    </article>

    <article class="analytics-card chart-card">
        <div class="analytics-head"><div><h3>Embudo por estado</h3><p>Seguimiento de avance operativo.</p></div></div>
        <div class="funnel-list">
            <?php foreach (($applicationsByStatus ?? []) as $row): ?>
                <?php $percent = ((int) $row['total'] / $statusTotal) * 100; ?>
                <div class="funnel-row" style="--status-color: <?= h($row['color'] ?? '#64748B') ?>">
                    <div><strong><?= h($row['label']) ?></strong><small><?= h($row['total']) ?> casos · <?= h((string) round($percent)) ?>%</small></div>
                    <span><i style="width: <?= h((string) $percent) ?>%"></i></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($applicationsByStatus)): ?><p class="muted-text">Sin estados registrados.</p><?php endif; ?>
        </div>
    </article>

    <article class="analytics-card chart-card">
        <div class="analytics-head"><div><h3>Rangos de edad</h3><p>Edad calculada desde fecha de nacimiento.</p></div></div>
        <div class="age-bars">
            <?php foreach (($applicationsByAgeRange ?? []) as $row): ?>
                <?php $percent = ((int) $row['total'] / $ageMax) * 100; ?>
                <div class="age-row"><div><strong><?= h($row['label']) ?></strong><small><?= h($row['total']) ?> postulantes</small></div><span><i style="width: <?= h((string) $percent) ?>%"></i></span></div>
            <?php endforeach; ?>
            <?php if (empty($applicationsByAgeRange)): ?><p class="muted-text">Sin fechas de nacimiento registradas.</p><?php endif; ?>
        </div>
    </article>

    <article class="analytics-card wide-card">
        <div class="analytics-head"><div><h3>Últimos postulantes</h3><p>Listado compacto para seguimiento inmediato.</p></div></div>
        <div class="latest-table-wrap">
            <table class="latest-report-table">
                <thead><tr><th>Postulante</th><th>Curso</th><th>Estado</th><th>Fecha ingreso</th></tr></thead>
                <tbody>
                    <?php foreach (($latestApplications ?? []) as $item): ?>
                        <tr>
                            <td><span class="avatar-dot"><?= h(substr((string) ($item['student_name'] ?? 'P'), 0, 1)) ?></span><strong><?= h($item['student_name']) ?></strong></td>
                            <td><?= h($item['course']) ?></td>
                            <td><span class="status-pill" style="--status-color: <?= h($item['status_color'] ?? '#64748B') ?>"><?= h($item['status_name'] ?? 'Sin estado') ?></span></td>
                            <td><?= h($item['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($latestApplications)): ?><tr><td colspan="4" class="empty">Aún no hay postulantes recientes.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
