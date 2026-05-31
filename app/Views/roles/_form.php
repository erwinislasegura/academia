<form class="panel-card form-grid" method="post" action="<?= App::url(isset($role['id']) ? '/roles/update/' . h($role['id']) : '/roles/store') ?>">
    <?php foreach (($errors ?? []) as $error): ?><div class="field-error span-2"><?= h($error) ?></div><?php endforeach; ?>
    <label>Nombre<input name="name" value="<?= h($role['name'] ?? '') ?>" required></label>
    <label>Slug<input name="slug" value="<?= h($role['slug'] ?? '') ?>" placeholder="coordinador" required></label>
    <label class="span-2">Descripción<textarea name="description" rows="3"><?= h($role['description'] ?? '') ?></textarea></label>
    <div class="span-2 permissions-board">
        <?php foreach ($permissions as $module => $items): ?>
            <section class="permission-group"><h3><?= h($module) ?></h3>
            <?php foreach ($items as $permission): ?><label class="check-row"><input type="checkbox" name="permissions[]" value="<?= h($permission['id']) ?>" <?= in_array((int)$permission['id'], array_map('intval', $selected ?? []), true) ? 'checked' : '' ?>><span><?= h($permission['name']) ?></span><small><?= h($permission['description']) ?></small></label><?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
    <div class="span-2 form-actions"><button class="btn primary">Guardar rol</button></div>
</form>
