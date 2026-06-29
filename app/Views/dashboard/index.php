<?php
$labels = [
    'total_cables' => ['Total de cables registrados', 'Activos en sistema'], 'cables_disponibles' => ['Cables disponibles', 'Estado disponible'], 'cables_reparacion' => ['Cables en reparación', 'Proceso vigente'], 'cables_entregados' => ['Cables entregados', 'Informe finalizado'],
    'cables_baja' => ['Cables dados de baja', 'Fuera de servicio'], 'informes_hoy' => ['Informes creados hoy', date('d/m/Y')], 'informes_pendientes' => ['Informes pendientes', 'Borrador / sin finalizar'], 'informes_finalizados' => ['Informes finalizados', 'Rango filtrado'],
    'stock_bajo' => ['Materiales con stock bajo', '≤ mínimo'], 'materiales_mes' => ['Materiales utilizados este mes', 'Unidades'], 'entregas_activas' => ['Entregas activas', 'Pendientes de rendición'], 'reparaciones_realizadas' => ['Reparaciones realizadas', 'Mufas y chaquetas'],
];
$role = Auth::role() ?? 'visualizador';
$chartPayload = [
    'cables' => $cablesPorEstado ?? [], 'informes' => $informesPorEstado ?? [],
    'fallas' => $fallasMasFrecuentes ?? [], 'causas' => $causasMasFrecuentes ?? [],
    'materiales' => array_map(static fn($m) => ['label' => $m['nombre'] ?? 'Material', 'total' => (int)($m['cantidad_utilizada'] ?? 0)], $materialesMasUsados ?? []),
];
function dashEstadoStock(array $m): string { $s=(float)($m['stock_actual']??0); $min=(float)($m['stock_minimo']??0); return $s<=0?'Crítico':($s<=$min?'Bajo':'Normal'); }
?>
<section class="mining-dashboard" data-dashboard>
    <div class="mining-hero">
        <div><p class="eyebrow light">Mantención de cables mineros · Panel operativo</p><h2>Dashboard principal avanzado</h2><p>Control compacto para supervisores, bodega, administración y gerencia. Los indicadores se calculan desde la base de datos real y muestran estados vacíos cuando no hay registros.</p></div>
        <div class="quick-actions"><a class="mini-btn primary" href="<?= App::url('/informes/create') ?>">Nuevo informe cable</a><a class="mini-btn" href="<?= App::url('/cables/create') ?>">Crear cable</a><a class="mini-btn" href="<?= App::url('/entregas-materiales/create') ?>">Entregar materiales</a><a class="mini-btn" href="<?= App::url('/recepciones-materiales/create') ?>">Recepcionar materiales</a><a class="mini-btn" href="<?= App::url('/materiales/create') ?>">Crear material</a><a class="mini-btn" href="<?= App::url('/reportes') ?>">Ver reportes</a></div>
    </div>

    <form class="dashboard-filters" method="get" action="<?= App::url('/dashboard') ?>">
        <label>Fecha desde<input type="date" name="fecha_desde" value="<?= h($filters['fecha_desde'] ?? '') ?>"></label>
        <label>Fecha hasta<input type="date" name="fecha_hasta" value="<?= h($filters['fecha_hasta'] ?? '') ?>"></label>
        <label>Supervisor<select name="supervisor"><option value="">Todos</option><?php foreach(($filterOptions['supervisores']??[]) as $o): ?><option <?= ($filters['supervisor']??'')===$o?'selected':'' ?>><?= h($o) ?></option><?php endforeach; ?></select></label>
        <label>Estado cable<select name="estado"><option value="">Todos</option><?php foreach(($filterOptions['estados']??[]) as $o): ?><option <?= ($filters['estado']??'')===$o?'selected':'' ?>><?= h($o) ?></option><?php endforeach; ?></select></label>
        <label>Origen cable<select name="origen"><option value="">Todos</option><?php foreach(($filterOptions['origenes']??[]) as $o): ?><option <?= ($filters['origen']??'')===$o?'selected':'' ?>><?= h($o) ?></option><?php endforeach; ?></select></label>
        <button class="mini-btn primary" type="submit">Filtrar</button><a class="mini-btn" href="<?= App::url('/dashboard') ?>">Limpiar</a>
    </form>

    <div class="kpi-grid">
        <?php foreach($labels as $key => [$titleKpi, $hint]): ?><article class="kpi-card"><span><?= h($titleKpi) ?></span><strong data-kpi="<?= h($key) ?>"><?= h((string)($kpis[$key] ?? 0)) ?></strong><small><?= h($hint) ?></small></article><?php endforeach; ?>
    </div>

    <div class="ops-grid">
        <article class="ops-card"><h3>Estado de cables</h3><canvas id="chartCables"></canvas></article>
        <article class="ops-card"><h3>Informes por estado</h3><canvas id="chartInformes"></canvas></article>
        <article class="ops-card ops-card--wide"><h3>Fallas más frecuentes</h3><canvas id="chartFallas"></canvas></article>
        <article class="ops-card ops-card--wide"><h3>Causas probables más comunes</h3><canvas id="chartCausas"></canvas></article>
    </div>

    <div class="ops-grid">
        <article class="ops-card ops-card--wide"><h3>Cables con mayor cantidad de reparaciones</h3><div class="dashboard-table-wrap"><table class="dashboard-table compact"><thead><tr><th>Número cable</th><th>Marca</th><th>Calibre</th><th>Informes</th><th>Reparaciones</th><th>Última reparación</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach(($cablesMasReparados??[]) as $r): ?><tr><td><?= h($r['numero']??'S/N') ?></td><td><?= h($r['marca']??'') ?></td><td><?= h($r['calibre']??'') ?></td><td><?= h($r['total_informes']??0) ?></td><td><?= h($r['total_reparaciones']??0) ?></td><td><?= h($r['ultima_reparacion']??'') ?></td><td><span class="badge"><?= h($r['estado']??'') ?></span></td><td><a class="table-action" href="<?= App::url('/cables/historial') ?>">Ver historial</a></td></tr><?php endforeach; ?><?php if(empty($cablesMasReparados)): ?><tr><td colspan="8" class="empty">Sin reparaciones registradas.</td></tr><?php endif; ?></tbody></table></div></article>
        <article class="ops-card ops-card--wide"><h3>Últimos informes de cable</h3><div class="dashboard-table-wrap"><table class="dashboard-table compact"><thead><tr><th>Recepción</th><th>Entrega</th><th>Cable</th><th>Supervisor</th><th>Origen</th><th>Estado operativo</th><th>Informe</th><th></th></tr></thead><tbody><?php foreach(($ultimosInformes??[]) as $i): ?><tr><td><?= h($i['fecha_recepcion']??'') ?></td><td><?= h($i['fecha_entrega']??'') ?></td><td><?= h($i['cable']??'') ?></td><td><?= h($i['supervisor']??'') ?></td><td><?= h($i['origen']??'') ?></td><td><span class="badge"><?= h($i['estado_operativo']??'') ?></span></td><td><?= h($i['estado_informe']??'') ?></td><td><a class="table-action" href="<?= App::url('/informes/show/'.($i['id']??0)) ?>">Ver</a> <a class="table-action" href="<?= App::url('/informes/print/'.($i['id']??0)) ?>">Imprimir</a></td></tr><?php endforeach; ?><?php if(empty($ultimosInformes)): ?><tr><td colspan="8" class="empty">No hay informes recientes.</td></tr><?php endif; ?></tbody></table></div></article>
    </div>

    <div class="ops-grid">
        <article class="ops-card"><h3>Alertas importantes</h3><?php foreach(['red'=>'Rojas','yellow'=>'Amarillas','green'=>'Verdes'] as $tone=>$name): ?><h4><?= $name ?></h4><div class="alert-list"><?php foreach(($alertasOperativas[$tone]??[]) as $a): ?><div class="op-alert <?= h($tone) ?>"><strong><?= h($a['title']) ?></strong><span><?= h($a['detail']) ?></span></div><?php endforeach; ?><?php if(empty($alertasOperativas[$tone])): ?><p class="empty">Sin alertas <?= h(strtolower($name)) ?>.</p><?php endif; ?></div><?php endforeach; ?></article>
        <article class="ops-card"><h3>Materiales más usados</h3><canvas id="chartMateriales"></canvas></article>
        <article class="ops-card ops-card--wide"><h3>Stock bajo</h3><div class="dashboard-table-wrap"><table class="dashboard-table compact"><thead><tr><th>Foto</th><th>Código</th><th>Material</th><th>Stock</th><th>Mínimo</th><th>Unidad</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach(($materialesStockBajo??[]) as $m): ?><tr><td><span class="thumb"></span></td><td><?= h($m['codigo']??'') ?></td><td><?= h($m['nombre']??'') ?></td><td><?= h($m['stock_actual']??0) ?></td><td><?= h($m['stock_minimo']??0) ?></td><td><?= h($m['unidad']??'') ?></td><td><span class="badge stock-<?= strtolower(dashEstadoStock($m)) ?>"><?= h(dashEstadoStock($m)) ?></span></td><td><a class="table-action" href="<?= App::url('/materiales/edit/'.($m['id']??0)) ?>">Editar</a></td></tr><?php endforeach; ?><?php if(empty($materialesStockBajo)): ?><tr><td colspan="8" class="empty">No existen materiales con stock bajo.</td></tr><?php endif; ?></tbody></table></div></article>
    </div>

    <div class="ops-grid">
        <article class="ops-card ops-card--wide"><h3>Entregas de materiales pendientes</h3><div class="dashboard-table-wrap"><table class="dashboard-table compact"><thead><tr><th>Fecha</th><th>Usuario receptor</th><th>Materiales</th><th>Entregado</th><th>Usado</th><th>Pendiente</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach(($entregasPendientes??[]) as $e): ?><tr><td><?= h($e['fecha_entrega']??'') ?></td><td><?= h($e['usuario_receptor']??'') ?></td><td><?= h($e['materiales_entregados']??'') ?></td><td><?= h($e['cantidad_total']??0) ?></td><td><?= h($e['cantidad_usada']??0) ?></td><td><?= h($e['cantidad_pendiente']??0) ?></td><td><span class="badge"><?= h($e['estado']??'') ?></span></td><td><a class="table-action" href="<?= App::url('/entregas-materiales/show/'.($e['id']??0)) ?>">Ver entrega</a></td></tr><?php endforeach; ?><?php if(empty($entregasPendientes)): ?><tr><td colspan="8" class="empty">Sin entregas pendientes.</td></tr><?php endif; ?></tbody></table></div></article>
        <article class="ops-card"><h3>Rendimiento por supervisor</h3><div class="dashboard-table-wrap"><table class="dashboard-table compact"><thead><tr><th>Supervisor</th><th>Creados</th><th>Finalizados</th><th>Entregados</th><th>Reparaciones</th><th>Materiales</th></tr></thead><tbody><?php foreach(($rendimientoSupervisores??[]) as $s): ?><tr><td><?= h($s['supervisor']??'') ?></td><td><?= h($s['informes_creados']??0) ?></td><td><?= h($s['informes_finalizados']??0) ?></td><td><?= h($s['cables_entregados']??0) ?></td><td><?= h($s['reparaciones_registradas']??0) ?></td><td><?= h($s['materiales_usados']??0) ?></td></tr><?php endforeach; ?><?php if(empty($rendimientoSupervisores)): ?><tr><td colspan="6" class="empty">Sin actividad por supervisor.</td></tr><?php endif; ?></tbody></table></div></article>
        <?php if(in_array($role, ['super-administrador','administrador'], true)): ?><article class="ops-card"><h3>Actividad reciente</h3><div class="activity-list"><?php foreach(($actividadReciente??[]) as $a): ?><div><strong><?= h($a['usuario']??'Sistema') ?></strong><span><?= h(($a['accion']??'').' · '.($a['modulo']??'')) ?></span><small><?= h($a['descripcion']??'') ?> — <?= h($a['fecha']??'') ?></small></div><?php endforeach; ?><?php if(empty($actividadReciente)): ?><p class="empty">Sin movimientos de auditoría.</p><?php endif; ?></div></article><?php endif; ?>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const dashboardCharts = <?= json_encode($chartPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const palette = ['#f59e0b','#22c55e','#38bdf8','#ef4444','#a78bfa','#f97316','#14b8a6','#eab308'];
function emptyDataset(data){ return Object.values(data || {}).every(v => Number(v.total ?? v) === 0); }
function makeChart(id, type, labels, values, indexAxis){ const el=document.getElementById(id); if(!el) return; new Chart(el,{type,data:{labels,datasets:[{data:values,backgroundColor:palette,borderColor:'#0f172a',borderWidth:1,borderRadius: type==='bar'?8:0}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:indexAxis||'x',plugins:{legend:{display:type!=='bar',labels:{color:'#cbd5e1'}},tooltip:{enabled:true}},scales:type==='bar'?{x:{ticks:{color:'#94a3b8'},grid:{color:'rgba(148,163,184,.12)'}},y:{ticks:{color:'#94a3b8'},grid:{color:'rgba(148,163,184,.12)'}}}:{}}}); }
makeChart('chartCables','doughnut',Object.keys(dashboardCharts.cables),Object.values(dashboardCharts.cables));
makeChart('chartInformes','doughnut',Object.keys(dashboardCharts.informes),Object.values(dashboardCharts.informes));
makeChart('chartFallas','bar',dashboardCharts.fallas.map(i=>i.label),dashboardCharts.fallas.map(i=>i.total));
makeChart('chartCausas','bar',dashboardCharts.causas.map(i=>i.label),dashboardCharts.causas.map(i=>i.total),'y');
makeChart('chartMateriales','bar',dashboardCharts.materiales.map(i=>i.label),dashboardCharts.materiales.map(i=>i.total),'y');
</script>
