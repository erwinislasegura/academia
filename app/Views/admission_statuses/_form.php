<?php if (!empty($errors)): ?>
    <div class="alert error">
        <?php foreach ($errors as $error): ?><div><?= h($error) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="panel-card form-grid" method="post" action="<?= App::url(isset($status['id']) ? '/admission-statuses/update/' . h($status['id']) : '/admission-statuses/store') ?>">
    <label>Nombre
        <input name="name" value="<?= h($status['name'] ?? '') ?>" placeholder="Ej: En revisión" required>
    </label>
    <label>Slug
        <input name="slug" value="<?= h($status['slug'] ?? '') ?>" placeholder="en-revision" required>
        <small>Usa minúsculas, números y guiones para identificar el estado.</small>
    </label>
    <label>Color
        <input type="color" name="color" value="<?= h($status['color'] ?? '#071D7A') ?>">
    </label>
    <label>Orden
        <input type="number" name="sort_order" value="<?= h((string) ($status['sort_order'] ?? 0)) ?>" min="0" step="1">
    </label>
    <label class="span-2">Descripción
        <textarea name="description" rows="4" placeholder="Describe cuándo se utiliza este estado."><?= h($status['description'] ?? '') ?></textarea>
    </label>
    <label class="check-row span-2">
        <input type="checkbox" name="is_active" value="1" <?= !empty($status['is_active']) ? 'checked' : '' ?>>
        <span>Estado activo y disponible para las postulaciones</span>
    </label>
    <div class="span-2 form-actions"><button class="btn primary">Guardar estado</button></div>
</form>
