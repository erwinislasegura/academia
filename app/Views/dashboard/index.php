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
$metrics = $admissionMetrics ?? ['total' => 0, 'new_this_week' => 0, 'contact_rate' => 0, 'acceptance_rate' => 0, 'girls' => 0, 'boys' => 0, 'without_gender' => 0];
$totalApplicants = max(1, (int) ($metrics['total'] ?? 0));
$courseMax = dashboardMax($applicationsByCourse ?? []);
$statusTotal = max(1, dashboardTotal($applicationsByStatus ?? []));
$trendMax = dashboardMax($applicationsTrend ?? []);
$contactAngle = min(100, (float) ($metrics['contact_rate'] ?? 0)) * 3.6;
$girlsPercent = round(((int) ($metrics['girls'] ?? 0) / $totalApplicants) * 100);
$boysPercent = round(((int) ($metrics['boys'] ?? 0) / $totalApplicants) * 100);
$withoutGenderPercent = round(((int) ($metrics['without_gender'] ?? 0) / $totalApplicants) * 100);
$withGenderPercent = round(((int) ($metrics['with_gender'] ?? 0) / $totalApplicants) * 100);
?>
<section class="admission-dashboard">
    <div class="admission-dashboard__hero">
        <div>
            <p class="eyebrow light">Admisión 2027 · Reporte ejecutivo</p>
            <h2>Dashboard de postulaciones</h2>
            <p>Vista compacta para cruzar cursos con sexo de postulantes, seguimiento por estado, demanda por nivel y calidad de datos.</p>
        </div>
        <div class="admission-dashboard__actions">
            <?php if (Auth::can('configurar_postulaciones')): ?>
                <a class="btn primary" href="<?= App::url('/admissions/export') ?>">Exportar informe</a>
                <a class="btn secondary" href="<?= App::url('/admissions') ?>">Gestionar postulantes</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-summary-grid dashboard-summary-grid--complete">
        <article class="summary-card summary-card--total">
            <span>Total postulantes</span>
            <strong><?= h($metrics['total']) ?></strong>
            <small>Proceso escolar 2027</small>
        </article>
        <article class="summary-card summary-card--gender">
            <span>Niñas</span>
            <strong><?= h($metrics['girls'] ?? 0) ?></strong>
            <small><?= h((string) $girlsPercent) ?>% del total</small>
        </article>
        <article class="summary-card summary-card--gender">
            <span>Niños</span>
            <strong><?= h($metrics['boys'] ?? 0) ?></strong>
            <small><?= h((string) $boysPercent) ?>% del total</small>
        </article>
        <article class="summary-card summary-card--alert">
            <span>Sin sexo informado</span>
            <strong><?= h($metrics['without_gender'] ?? 0) ?></strong>
            <small><?= h((string) $withoutGenderPercent) ?>% por completar</small>
        </article>
        <article class="summary-card summary-card--gender summary-card--girls">
            <span>Niñas</span>
            <strong><?= h($metrics['girls'] ?? 0) ?></strong>
            <small><?= h((string) $girlsPercent) ?>% del total</small>
        </article>
        <article class="summary-card summary-card--gender summary-card--boys">
            <span>Niños</span>
            <strong><?= h($metrics['boys'] ?? 0) ?></strong>
            <small><?= h((string) $boysPercent) ?>% del total</small>
        </article>
        <article class="summary-card summary-card--alert">
            <span>Sin sexo informado</span>
            <strong><?= h($metrics['without_gender'] ?? 0) ?></strong>
            <small><?= h((string) $withoutGenderPercent) ?>% por completar</small>
        </article>
        <article class="summary-card summary-card--ring" style="--ring-angle: <?= h((string) $contactAngle) ?>deg">
            <span>Contactabilidad</span>
            <strong><?= h($metrics['contact_rate']) ?>%</strong>
            <small>Contactada + aceptada</small>
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

        <article class="report-card report-card--gender-overview">
            <div class="report-card__head"><div><h3>Distribución por sexo</h3><p>Composición general y completitud del dato.</p></div></div>
            <div class="gender-overview">
                <div class="gender-meter" aria-label="<?= h((string) $withGenderPercent) ?>% con sexo informado">
                    <strong><?= h((string) $withGenderPercent) ?>%</strong>
                    <small>sexo informado</small>
                </div>
                <div class="dashboard-legend dashboard-legend--compact">
                    <?php foreach (($applicationsByGender ?? []) as $row): ?>
                        <?php $percent = round(((int) ($row['total'] ?? 0) / $totalApplicants) * 100); ?>
                        <div><span style="background: <?= h($row['color'] ?? '#071D7A') ?>"></span><b><?= h($row['label']) ?></b><em><?= h($row['total']) ?> · <?= h((string) $percent) ?>%</em></div>
                    <?php endforeach; ?>
                    <?php if (empty($applicationsByGender)): ?><p class="muted-text">Sin datos de sexo.</p><?php endif; ?>
                </div>
            </div>
        </article>

        <article class="report-card">
            <div class="report-card__head"><div><h3>Edades postulantes</h3><p>Rangos calculados desde fecha de nacimiento.</p></div></div>
            <div class="age-distribution">
                <?php foreach (($applicationsByAgeRange ?? []) as $row): ?>
                    <?php $percent = ((int) ($row['total'] ?? 0) / $totalApplicants) * 100; ?>
                    <label><b><?= h($row['label']) ?></b><small><?= h($row['total']) ?> · <?= h((string) round($percent)) ?>%</small></label>
                    <span><i style="width: <?= h((string) $percent) ?>%"></i></span>
                <?php endforeach; ?>
                <?php if (empty($applicationsByAgeRange)): ?><p class="muted-text">Sin edades para mostrar.</p><?php endif; ?>
            </div>
        </article>


        <article class="report-card report-card--large">
            <div class="report-card__head">
                <div><h3>Cruce curso / sexo</h3><p>Distribución de niñas, niños y registros sin dato por cada curso.</p></div>
                <span><?= h((string) ($metrics['girls'] ?? 0)) ?> niñas · <?= h((string) ($metrics['boys'] ?? 0)) ?> niños</span>
            </div>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table dashboard-table--compact">
                    <thead><tr><th>Curso</th><th>Total</th><th>Niñas</th><th>Niños</th><th>Sin dato</th><th>Composición</th></tr></thead>
                    <tbody>
                        <?php foreach (($applicationsByCourseAndGender ?? []) as $row): ?>
                            <?php
                                $rowTotal = max(1, (int) ($row['total'] ?? 0));
                                $girlsWidth = ((int) ($row['girls'] ?? 0) / $rowTotal) * 100;
                                $boysWidth = ((int) ($row['boys'] ?? 0) / $rowTotal) * 100;
                                $unknownWidth = max(0, 100 - $girlsWidth - $boysWidth);
                            ?>
                            <tr>
                                <td><strong><?= h($row['label']) ?></strong></td>
                                <td><?= h($row['total']) ?></td>
                                <td><?= h($row['girls'] ?? 0) ?></td>
                                <td><?= h($row['boys'] ?? 0) ?></td>
                                <td><?= h($row['without_gender'] ?? 0) ?></td>
                                <td><span class="gender-stack" title="Niñas <?= h((string) round($girlsWidth)) ?>% · Niños <?= h((string) round($boysWidth)) ?>% · Sin dato <?= h((string) round($unknownWidth)) ?>%"><i class="girls" style="width: <?= h((string) $girlsWidth) ?>%"></i><i class="boys" style="width: <?= h((string) $boysWidth) ?>%"></i><i class="unknown" style="width: <?= h((string) $unknownWidth) ?>%"></i></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($applicationsByCourseAndGender)): ?><tr><td colspan="6" class="empty">Aún no hay datos por curso y sexo.</td></tr><?php endif; ?>
                    </tbody>
                </table>
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

        <article class="report-card report-card--large">
            <div class="report-card__head"><div><h3>Estados por sexo</h3><p>Seguimiento cruzado para detectar brechas de gestión.</p></div></div>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table dashboard-table--compact dashboard-table--status-gender">
                    <thead><tr><th>Estado</th><th>Total</th><th>Niñas</th><th>Niños</th><th>Sin dato</th><th>Avance</th></tr></thead>
                    <tbody>
                        <?php foreach (($applicationsByStatusAndGender ?? []) as $row): ?>
                            <?php
                                $rowTotal = max(1, (int) ($row['total'] ?? 0));
                                $girlsWidth = ((int) ($row['girls'] ?? 0) / $rowTotal) * 100;
                                $boysWidth = ((int) ($row['boys'] ?? 0) / $rowTotal) * 100;
                                $unknownWidth = max(0, 100 - $girlsWidth - $boysWidth);
                            ?>
                            <tr>
                                <td><span class="dashboard-status" style="--status-color: <?= h($row['color'] ?? '#071D7A') ?>"><?= h($row['label']) ?></span></td>
                                <td><?= h($row['total']) ?></td>
                                <td><?= h($row['girls'] ?? 0) ?></td>
                                <td><?= h($row['boys'] ?? 0) ?></td>
                                <td><?= h($row['without_gender'] ?? 0) ?></td>
                                <td><span class="gender-stack"><i class="girls" style="width: <?= h((string) $girlsWidth) ?>%"></i><i class="boys" style="width: <?= h((string) $boysWidth) ?>%"></i><i class="unknown" style="width: <?= h((string) $unknownWidth) ?>%"></i></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($applicationsByStatusAndGender)): ?><tr><td colspan="6" class="empty">Sin estados para cruzar.</td></tr><?php endif; ?>
                    </tbody>
                </table>
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
