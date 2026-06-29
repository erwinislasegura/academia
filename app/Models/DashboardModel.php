<?php

final class DashboardModel extends Model
{
    private array $tableCache = [];
    private array $columnCache = [];

    public function filters(array $input = []): array
    {
        return [
            'fecha_desde' => $this->dateOrNull($input['fecha_desde'] ?? null),
            'fecha_hasta' => $this->dateOrNull($input['fecha_hasta'] ?? null),
            'supervisor' => trim((string) ($input['supervisor'] ?? '')),
            'estado' => trim((string) ($input['estado'] ?? '')),
            'origen' => trim((string) ($input['origen'] ?? '')),
        ];
    }

    public function getDashboardData(array $filters = []): array
    {
        $filters = $this->filters($filters);
        $cablesPorEstado = $this->getCablesPorEstado($filters);
        $informesPorEstado = $this->getInformesPorEstado($filters);
        $stockBajo = $this->getMaterialesStockBajo($filters);
        return [
            'filters' => $filters,
            'filterOptions' => $this->getFilterOptions(),
            'kpis' => [
                'total_cables' => $this->getTotalCables($filters),
                'cables_disponibles' => $cablesPorEstado['disponible'] ?? 0,
                'cables_reparacion' => $cablesPorEstado['en_reparacion'] ?? 0,
                'cables_entregados' => $cablesPorEstado['entregado'] ?? 0,
                'cables_baja' => $cablesPorEstado['dado_de_baja'] ?? 0,
                'informes_hoy' => $this->getInformesHoy(),
                'informes_pendientes' => $this->getInformesPendientes($filters),
                'informes_finalizados' => $informesPorEstado['finalizado'] ?? 0,
                'stock_bajo' => count($stockBajo),
                'materiales_mes' => $this->getMaterialesUtilizadosMes(),
                'entregas_activas' => count($this->getEntregasPendientes($filters)),
                'reparaciones_realizadas' => $this->getReparacionesRealizadas($filters),
            ],
            'cablesPorEstado' => $cablesPorEstado,
            'informesPorEstado' => $informesPorEstado,
            'materialesStockBajo' => $stockBajo,
            'materialesCriticos' => $this->getMaterialesCriticos($filters),
            'materialesMasUsados' => $this->getMaterialesMasUsados($filters),
            'fallasMasFrecuentes' => $this->getFallasMasFrecuentes($filters),
            'causasMasFrecuentes' => $this->getCausasMasFrecuentes($filters),
            'cablesMasReparados' => $this->getCablesMasReparados($filters),
            'ultimosInformes' => $this->getUltimosInformes($filters),
            'entregasPendientes' => $this->getEntregasPendientes($filters),
            'rendimientoSupervisores' => $this->getRendimientoSupervisores($filters),
            'actividadReciente' => $this->getActividadReciente(),
            'alertasOperativas' => $this->getAlertasOperativas($filters),
        ];
    }

    public function getTotalCables(array $filters = []): int { return $this->countRows('cables', $this->whereCable($filters)); }
    public function getInformesHoy(): int { return $this->countRows('informes_cables', $this->whereDate('informes_cables', 'created_at', date('Y-m-d'), date('Y-m-d'))); }
    public function getInformesPendientes(array $filters = []): int { $col = $this->hasColumn('informes_cables','estado_informe') ? 'estado_informe' : ($this->hasColumn('informes_cables','estado') ? 'estado' : null); return $this->countRows('informes_cables', $this->whereInforme($filters, $col ? ["LOWER(COALESCE({$col}, 'borrador')) IN ('borrador','pendiente','sin finalizar')"] : [])); }
    public function getMaterialesStockBajo(array $filters = []): array { return $this->materialRows('stock_actual <= stock_minimo', 8); }
    public function getMaterialesCriticos(array $filters = []): array { return $this->materialRows('stock_actual <= 0', 8); }
    public function getMaterialesUtilizadosMes(): int { return $this->sumRows('informe_materiales', 'cantidad', $this->whereDate('informe_materiales', 'created_at', date('Y-m-01'), date('Y-m-d'))); }
    public function getReparacionesRealizadas(array $filters = []): int
    {
        $cols = ['mufas_termocontraible_ingreso','mufa_union_ingreso','chaquetas_ingreso','mufas_termocontraible_salida','mufa_union_salida','chaquetas_salida'];
        return $this->sumExistingColumns('informes_cables', $cols, $this->whereInforme($filters));
    }

    public function getCablesPorEstado(array $filters = []): array { return $this->statusCounts('cables', 'estado', ['disponible','en_reparacion','entregado','dado_de_baja'], $this->whereCable($filters)); }
    public function getInformesPorEstado(array $filters = []): array { return $this->statusCounts('informes_cables', $this->hasColumn('informes_cables','estado_informe') ? 'estado_informe' : 'estado', ['borrador','finalizado','anulado'], $this->whereInforme($filters)); }
    public function getFallasMasFrecuentes(array $filters = []): array
    {
        $labels = ['Corto Circuito','Tracción','Aplastamiento','Corte en Chaqueta','Sin Enchufe Macho','Cabezal Quebrado','Cuerpo enchufe Quebrado','Loza Quebrada','Sin Pines','Sin Piloto','Pines sulfatados'];
        return $this->catalogCounts(['informe_fallas_chaquetas','informe_fallas_enchufe','informe_lugares_falla','informe_causas_probables'], $labels);
    }
    public function getCausasMasFrecuentes(array $filters = []): array { return $this->catalogCounts(['informe_causas_probables'], ['Tracción','Sobrecorriente','Aplastamiento','Cortocircuito','Pines sueltos','Impacto','Enrolla cable','Roca tronadura','Fatiga de material','Agua / Humedad','Manilla enrollada']); }

    public function getMaterialesMasUsados(array $filters = []): array
    {
        if (!$this->hasTable('informe_materiales')) return [];
        $join = $this->hasTable('materiales') && $this->hasColumn('informe_materiales','material_id') ? ' LEFT JOIN materiales m ON m.id = im.material_id' : '';
        $sql = "SELECT COALESCE(m.nombre, im.material_nombre, 'Material') nombre, COALESCE(m.categoria, '') categoria, COALESCE(m.foto, '') foto, SUM(COALESCE(im.cantidad,0)) cantidad_utilizada, COALESCE(MAX(m.stock_actual),0) stock_actual, COALESCE(MAX(m.stock_minimo),0) stock_minimo FROM informe_materiales im{$join} GROUP BY nombre, categoria, foto ORDER BY cantidad_utilizada DESC LIMIT 8";
        return $this->queryRows($sql);
    }

    public function getCablesMasReparados(array $filters = []): array
    {
        if (!$this->hasTable('informes_cables')) return [];
        $sum = $this->repairExpression('i');
        $join = $this->hasTable('cables') && $this->hasColumn('informes_cables','cable_id') ? ' LEFT JOIN cables c ON c.id = i.cable_id' : '';
        return $this->queryRows("SELECT COALESCE(c.numero, i.numero_cable, 'S/N') numero, COALESCE(c.marca,'') marca, COALESCE(c.calibre,'') calibre, COUNT(*) total_informes, {$sum} total_reparaciones, MAX(COALESCE(i.fecha_entrega, i.updated_at, i.created_at)) ultima_reparacion, COALESCE(c.estado, i.estado_operativo, '') estado FROM informes_cables i{$join} GROUP BY numero, marca, calibre, estado ORDER BY total_reparaciones DESC, total_informes DESC LIMIT 8");
    }
    public function getUltimosInformes(array $filters = []): array { return $this->hasTable('informes_cables') ? $this->queryRows("SELECT id, fecha_recepcion, fecha_entrega, COALESCE(numero_cable, cable, cable_id, 'S/N') cable, COALESCE(supervisor,'') supervisor, COALESCE(origen,'') origen, COALESCE(estado_operativo,'') estado_operativo, COALESCE(estado_informe, estado, 'borrador') estado_informe FROM informes_cables ORDER BY COALESCE(created_at, fecha_recepcion) DESC LIMIT 10") : []; }
    public function getEntregasPendientes(array $filters = []): array { return $this->hasTable('entregas_materiales') ? $this->queryRows("SELECT id, fecha_entrega, COALESCE(usuario_receptor, usuario, '') usuario_receptor, COALESCE(materiales_entregados, '') materiales_entregados, COALESCE(cantidad_total, cantidad, 0) cantidad_total, COALESCE(cantidad_usada,0) cantidad_usada, GREATEST(COALESCE(cantidad_total, cantidad,0)-COALESCE(cantidad_usada,0),0) cantidad_pendiente, COALESCE(estado,'pendiente') estado FROM entregas_materiales WHERE LOWER(COALESCE(estado,'pendiente')) NOT IN ('cerrada','finalizada','rendida') ORDER BY fecha_entrega DESC LIMIT 8") : []; }
    public function getRendimientoSupervisores(array $filters = []): array { return $this->hasTable('informes_cables') ? $this->queryRows("SELECT COALESCE(supervisor,'Sin supervisor') supervisor, COUNT(*) informes_creados, SUM(LOWER(COALESCE(estado_informe, estado,''))='finalizado') informes_finalizados, SUM(LOWER(COALESCE(estado_operativo,''))='entregado') cables_entregados, {$this->repairExpression()} reparaciones_registradas, 0 materiales_usados FROM informes_cables GROUP BY supervisor ORDER BY informes_creados DESC LIMIT 8") : []; }
    public function getActividadReciente(): array { return $this->hasTable('audit_logs') ? $this->queryRows("SELECT COALESCE(usuario, user_name, user_id, '') usuario, COALESCE(accion, action, '') accion, COALESCE(modulo, module, '') modulo, COALESCE(descripcion, description, '') descripcion, COALESCE(created_at, fecha) fecha FROM audit_logs ORDER BY COALESCE(created_at, fecha) DESC LIMIT 10") : []; }
    public function getAlertasOperativas(array $filters = []): array
    {
        return ['red' => array_merge($this->alertsFromMaterials('crítico', 'red'), $this->simpleAlert('Cables dados de baja', $this->getCablesPorEstado($filters)['dado_de_baja'] ?? 0, 'red')), 'yellow' => array_merge($this->simpleAlert('Informes en borrador', $this->getInformesPorEstado($filters)['borrador'] ?? 0, 'yellow'), $this->simpleAlert('Materiales próximos a mínimo', count($this->getMaterialesStockBajo($filters)), 'yellow')), 'green' => array_merge($this->simpleAlert('Informes finalizados hoy', $this->countRows('informes_cables', array_filter([($this->hasColumn('informes_cables','updated_at') ? "DATE(updated_at) = CURDATE()" : ($this->hasColumn('informes_cables','created_at') ? "DATE(created_at) = CURDATE()" : null)), ($this->hasColumn('informes_cables','estado_informe') ? "LOWER(COALESCE(estado_informe,'')) = 'finalizado'" : ($this->hasColumn('informes_cables','estado') ? "LOWER(COALESCE(estado,'')) = 'finalizado'" : null))])), 'green'), $this->simpleAlert('Stock actualizado correctamente', $this->hasTable('kardex_materiales') ? 1 : 0, 'green'))];
    }

    public function getFilterOptions(): array { return ['supervisores' => $this->distinct('informes_cables','supervisor'), 'estados' => $this->distinct('cables','estado'), 'origenes' => $this->distinct('informes_cables','origen')]; }
    private function hasTable(string $table): bool { if (!isset($this->tableCache[$table])) { $st=$this->db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'); $st->execute([$table]); $this->tableCache[$table]=(bool)$st->fetchColumn(); } return $this->tableCache[$table]; }
    private function hasColumn(string $table,string $col): bool { $k="$table.$col"; if (!isset($this->columnCache[$k])) { if (!$this->hasTable($table)) return false; $st=$this->db->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'); $st->execute([$table,$col]); $this->columnCache[$k]=(bool)$st->fetchColumn(); } return $this->columnCache[$k]; }
    private function queryRows(string $sql,array $p=[]): array { try { $st=$this->db->prepare($sql); $st->execute($p); return $st->fetchAll() ?: []; } catch (Throwable) { return []; } }
    private function countRows(string $table,array $where=[]): int { if (!$this->hasTable($table)) return 0; $sql='SELECT COUNT(*) FROM '.$table.($where?' WHERE '.implode(' AND ',$where):''); return (int)($this->queryRows($sql)[0]['COUNT(*)'] ?? 0); }
    private function sumRows(string $table,string $col,array $where=[]): int { if (!$this->hasColumn($table,$col)) return 0; $sql="SELECT COALESCE(SUM({$col}),0) total FROM {$table}".($where?' WHERE '.implode(' AND ',$where):''); return (int)($this->queryRows($sql)[0]['total'] ?? 0); }
    private function sumExistingColumns(string $table,array $cols,array $where=[]): int { if (!$this->hasTable($table)) return 0; $expr=implode('+', array_map(fn($c)=>$this->hasColumn($table,$c)?"COALESCE({$c},0)":'0',$cols)); return (int)($this->queryRows("SELECT COALESCE(SUM({$expr}),0) total FROM {$table}".($where?' WHERE '.implode(' AND ',$where):''))[0]['total'] ?? 0); }
    private function statusCounts(string $table,string $col,array $labels,array $where=[]): array { $out=array_fill_keys($labels,0); if (!$this->hasColumn($table,$col)) return $out; foreach($this->queryRows("SELECT LOWER(REPLACE({$col}, ' ', '_')) label, COUNT(*) total FROM {$table}".($where?' WHERE '.implode(' AND ',$where):'')." GROUP BY label") as $r){ $out[$r['label']] = (int)$r['total']; } return $out; }
    private function materialRows(string $condition,int $limit): array { if (!$this->hasTable('materiales')) return []; return $this->queryRows("SELECT id, COALESCE(foto,'') foto, COALESCE(codigo,'') codigo, nombre, COALESCE(categoria,'') categoria, COALESCE(stock_actual,0) stock_actual, COALESCE(stock_minimo,0) stock_minimo, COALESCE(unidad,'') unidad FROM materiales WHERE {$condition} ORDER BY stock_actual ASC LIMIT {$limit}"); }
    private function catalogCounts(array $tables,array $labels): array { $out=array_map(fn($l)=>['label'=>$l,'total'=>0],$labels); foreach($tables as $t){ if(!$this->hasTable($t)) continue; $col=$this->firstColumn($t,['nombre','falla','causa','descripcion','lugar']); if(!$col) continue; foreach($this->queryRows("SELECT {$col} label, COUNT(*) total FROM {$t} GROUP BY {$col}") as $r){ foreach($out as &$o){ if(mb_strtolower($o['label'])===mb_strtolower((string)$r['label'])) $o['total']+=(int)$r['total']; } } } return $out; }
    private function firstColumn(string $t,array $cols): ?string { foreach($cols as $c) if($this->hasColumn($t,$c)) return $c; return null; }
    private function distinct(string $t,string $c): array { if(!$this->hasColumn($t,$c)) return []; return array_column($this->queryRows("SELECT DISTINCT {$c} value FROM {$t} WHERE {$c} IS NOT NULL AND {$c} <> '' ORDER BY {$c} LIMIT 80"),'value'); }
    private function whereDate(string $t,string $c,?string $from,?string $to): array { return $this->hasColumn($t,$c)&&$from&&$to ? ["DATE({$c}) BETWEEN ".$this->db->quote($from)." AND ".$this->db->quote($to)] : []; }
    private function whereCable(array $f): array { $w=[]; if(($f['estado']??'') && $this->hasColumn('cables','estado')) $w[]='estado='.$this->db->quote($f['estado']); if(($f['origen']??'') && $this->hasColumn('cables','origen')) $w[]='origen='.$this->db->quote($f['origen']); return $w; }
    private function whereInforme(array $f,array $extra=[]): array { $w=$extra; if(($f['fecha_desde']??null)||($f['fecha_hasta']??null)) $w=array_merge($w,$this->whereDate('informes_cables','created_at',$f['fecha_desde']?:'1900-01-01',$f['fecha_hasta']?:date('Y-m-d'))); foreach(['supervisor','origen'] as $c) if(($f[$c]??'') && $this->hasColumn('informes_cables',$c)) $w[]="{$c}=".$this->db->quote($f[$c]); return $w; }
    private function repairExpression(string $alias=''): string { $p=$alias?"{$alias}.":''; $cols=['mufas_termocontraible_ingreso','mufa_union_ingreso','chaquetas_ingreso','mufas_termocontraible_salida','mufa_union_salida','chaquetas_salida']; $parts=[]; foreach($cols as $c){ $parts[]=$this->hasColumn('informes_cables',$c)?"SUM(COALESCE({$p}{$c},0))":'0'; } return implode('+', $parts); }
    private function alertsFromMaterials(string $label,string $tone): array { return array_map(fn($m)=>['title'=>'Stock '.$label, 'detail'=>$m['nombre'].' · '.$m['stock_actual'].' '.$m['unidad'], 'tone'=>$tone], $this->getMaterialesCriticos()); }
    private function simpleAlert(string $title,int $count,string $tone): array { return $count>0 ? [['title'=>$title,'detail'=>$count.' registro(s) requieren revisión','tone'=>$tone]] : []; }
    private function dateOrNull(mixed $v): ?string { return is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$v) ? $v : null; }
}
