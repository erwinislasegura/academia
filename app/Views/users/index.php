<div class="section-head"><div><h2>Usuarios</h2><p>Administra accesos, estados y roles del equipo interno.</p></div><?php if (Auth::can('crear_usuarios')): ?><a class="btn primary" href="/users/create">+ Nuevo usuario</a><?php endif; ?></div>
<form class="filters-card" method="get">
    <input name="q" placeholder="Buscar por nombre o email" value="<?= h($filters['q'] ?? '') ?>">
    <select name="status"><option value="">Todos los estados</option><option value="active" <?= ($filters['status'] ?? '')==='active'?'selected':'' ?>>Activos</option><option value="inactive" <?= ($filters['status'] ?? '')==='inactive'?'selected':'' ?>>Inactivos</option></select>
    <select name="role_id"><option value="">Todos los roles</option><?php foreach ($roles as $role): ?><option value="<?= h($role['id']) ?>" <?= (string)($filters['role_id'] ?? '')===(string)$role['id']?'selected':'' ?>><?= h($role['name']) ?></option><?php endforeach; ?></select>
    <button class="btn secondary">Filtrar</button>
</form>
<div class="table-card"><table class="modern-table"><thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($users as $row): ?><tr>
<td><strong><?= h($row['name']) ?></strong><span><?= h($row['email']) ?></span></td>
<td><span class="badge role"><?= h($row['role_name']) ?></span></td>
<td><span class="badge <?= $row['status']==='active'?'ok':'off' ?>"><?= $row['status']==='active'?'Activo':'Inactivo' ?></span></td>
<td><?= h($row['last_login_at'] ?? 'Sin acceso') ?></td>
<td class="actions"><a href="/users/show/<?= h($row['id']) ?>">Ver</a><?php if (Auth::can('editar_usuarios')): ?><a href="/users/edit/<?= h($row['id']) ?>">Editar</a><form method="post" action="/users/status/<?= h($row['id']) ?>"><button>Estado</button></form><?php endif; ?><?php if (Auth::can('eliminar_usuarios')): ?><form method="post" action="/users/delete/<?= h($row['id']) ?>" data-confirm="¿Eliminar usuario?"><button class="danger">Eliminar</button></form><?php endif; ?></td>
</tr><?php endforeach; ?>
<?php if (!$users): ?><tr><td colspan="5" class="empty">No hay usuarios para los filtros seleccionados.</td></tr><?php endif; ?>
</tbody></table></div>
