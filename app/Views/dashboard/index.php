<?php
if (!function_exists('dashboardMax')) {
    function dashboardMax(array $rows): int
    {
        $values = array_map(static fn ($row) => (int) ($row['total'] ?? 0), $rows);
        return max([1, ...$values]);
    }
}
$metrics = $admissionMetrics ?? ['total' => 0, 'new_this_week' => 0, 'contact_rate' => 0, 'acceptance_rate' => 0];
$courseMax = dashboardMax($applicationsByCourse ?? []);
$statusMax = dashboardMax($applicationsByStatus ?? []);
$genderMax = dashboardMax($applicationsByGender ?? []);
$ageMax = dashboardMax($applicationsByAgeRange ?? []);
?>
<section class="hero-card dashboard-hero">
    <div>
        <p class="eyebrow">Panel ejecutivo de admisión</p>
        <h2>Dashboard de KPIs de postulantes 2027</h2>
        <p>Monitorea demanda por curso, estado del embudo, edades y distribución por niño/niña para tomar decisiones rápidas del proceso de postulación.</p>
    </div>
    <div class="hero-actions">
        <?php if (Auth::can('configurar_postulaciones')): ?>
            <a class="btn primary" href="<?= App::url('/admissions/export') ?>">Exportar informe</a>
            <a class="btn secondary" href="<?= App::url('/admissions') ?>">Gestionar postulantes</a>
        <?php endif; ?>
    </div>
</section>

<div class="stats-grid dashboard-kpis">
    <article class="stat-card accent"><span>Total postulantes</span><strong><?= h($metrics['total']) ?></strong><em>● Proceso 2027</em></article>
    <article class="stat-card"><span>Nuevos últimos 7 días</span><strong><?= h($metrics['new_this_week']) ?></strong><em>● Captación reciente</em></article>
    <article class="stat-card"><span>Tasa contactada</span><strong><?= h($metrics['contact_rate']) ?>%</strong><em>● Contactada + aceptada</em></article>
    <article class="stat-card"><span>Tasa aceptación</span><strong><?= h($metrics['acceptance_rate']) ?>%</strong><em>● Postulantes aceptados</em></article>
</div>

<div class="dashboard-grid">
    <section class="panel-card dashboard-panel span-2">
        <div class="section-head">
            <div><h3>Postulantes por curso</h3><p>Ranking de cursos con mayor interés.</p></div>
            <a class="mini-link" href="<?= App::url('/admissions/export') ?>">Descargar Excel</a>
        </div>
        <div class="bar-list">
            <?php foreach (($applicationsByCourse ?? []) as $row): ?>
                <?php $percent = ((int) $row['total'] / $courseMax) * 100; ?>
                <div class="bar-row">
                    <div><strong><?= h($row['label']) ?></strong><span><?= h($row['total']) ?> postulantes</span></div>
                    <div class="bar-track"><span style="width: <?= h((string) $percent) ?>%"></span></div>
                    <b><?= h($row['total']) ?></b>
                </div>
            <?php endforeach; ?>
            <?php if (empty($applicationsByCourse)): ?><p class="muted-text">Aún no hay postulaciones para graficar.</p><?php endif; ?>
        </div>
    </section>

    <section class="panel-card dashboard-panel">
        <div class="section-head"><div><h3>Embudo por estado</h3><p>Avance operativo del proceso.</p></div></div>
        <div class="status-kpi-list">
            <?php foreach (($applicationsByStatus ?? []) as $row): ?>
                <?php $percent = ((int) $row['total'] / $statusMax) * 100; ?>
                <div class="status-kpi" style="--status-color: <?= h($row['color'] ?? '#94A3B8') ?>">
                    <span></span><div><strong><?= h($row['label']) ?></strong><small><?= h($row['total']) ?> casos</small><i style="width: <?= h((string) $percent) ?>%"></i></div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($applicationsByStatus)): ?><p class="muted-text">Sin estados registrados.</p><?php endif; ?>
        </div>
    </section>

    <section class="panel-card dashboard-panel">
        <div class="section-head"><div><h3>Niño / Niña</h3><p>Distribución declarada en formulario.</p></div></div>
        <div class="donut-list">
            <?php foreach (($applicationsByGender ?? []) as $row): ?>
                <?php $percent = ((int) $row['total'] / $genderMax) * 100; ?>
                <div class="mini-metric"><span><?= h($row['label']) ?></span><strong><?= h($row['total']) ?></strong><div><i style="width: <?= h((string) $percent) ?>%"></i></div></div>
            <?php endforeach; ?>
            <?php if (empty($applicationsByGender)): ?><p class="muted-text">Sin datos de sexo todavía.</p><?php endif; ?>
        </div>
    </section>

    <section class="panel-card dashboard-panel">
        <div class="section-head"><div><h3>Edades</h3><p>Rangos etarios calculados por fecha de nacimiento.</p></div></div>
        <div class="donut-list">
            <?php foreach (($applicationsByAgeRange ?? []) as $row): ?>
                <?php $percent = ((int) $row['total'] / $ageMax) * 100; ?>
                <div class="mini-metric"><span><?= h($row['label']) ?></span><strong><?= h($row['total']) ?></strong><div><i style="width: <?= h((string) $percent) ?>%"></i></div></div>
            <?php endforeach; ?>
            <?php if (empty($applicationsByAgeRange)): ?><p class="muted-text">Sin fechas de nacimiento registradas.</p><?php endif; ?>
        </div>
    </section>

    <section class="panel-card dashboard-panel">
        <div class="section-head"><div><h3>Últimos postulantes</h3><p>Solicitudes más recientes.</p></div></div>
        <div class="latest-list">
            <?php foreach (($latestApplications ?? []) as $item): ?>
                <div class="latest-item">
                    <span class="avatar-dot"><?= h(substr((string) ($item['student_name'] ?? 'P'), 0, 1)) ?></span>
                    <div><strong><?= h($item['student_name']) ?></strong><small><?= h($item['course']) ?> · <?= h($item['status_name'] ?? 'Sin estado') ?></small></div>
                    <em><?= h($item['created_at']) ?></em>
                </div>
            <?php endforeach; ?>
            <?php if (empty($latestApplications)): ?><p class="muted-text">Aún no hay postulantes recientes.</p><?php endif; ?>
        </div>
    </section>
</div>
