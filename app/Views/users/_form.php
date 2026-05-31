<form class="panel-card form-grid" method="post" action="<?= isset($user['id']) ? '/users/update/' . h($user['id']) : '/users/store' ?>">
    <?php foreach (($errors ?? []) as $error): ?><div class="field-error span-2"><?= h($error) ?></div><?php endforeach; ?>
    <label>Nombre<input name="name" value="<?= h($user['name'] ?? '') ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= h($user['email'] ?? '') ?>" required></label>
    <label>Rol<select name="role_id" required><option value="">Seleccionar</option><?php foreach ($roles as $role): ?><option value="<?= h($role['id']) ?>" <?= (string)($user['role_id'] ?? '')===(string)$role['id']?'selected':'' ?>><?= h($role['name']) ?></option><?php endforeach; ?></select></label>
    <label>Estado<select name="status" required><option value="active" <?= ($user['status'] ?? '')==='active'?'selected':'' ?>>Activo</option><option value="inactive" <?= ($user['status'] ?? '')==='inactive'?'selected':'' ?>>Inactivo</option></select></label>
    <label class="span-2">Contraseña <?= isset($user['id']) ? '<small>Dejar en blanco para mantener</small>' : '' ?><input type="password" name="password" minlength="8"></label>
    <div class="span-2 form-actions"><button class="btn primary">Guardar usuario</button></div>
</form>
