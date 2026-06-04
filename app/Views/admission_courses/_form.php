<?php if (!empty($errors)): ?>
    <div class="alert error">
        <?php foreach ($errors as $error): ?><div><?= h($error) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="panel-card form-grid" method="post" action="<?= App::url(isset($course['id']) ? '/admission-courses/update/' . h($course['id']) : '/admission-courses/store') ?>">
    <label>Nombre
        <input name="name" value="<?= h($course['name'] ?? '') ?>" placeholder="Ej: 1º Medio" required>
        <small>Este texto se mostrará al apoderado y quedará guardado en la postulación.</small>
    </label>
    <label>Slug
        <input name="slug" value="<?= h($course['slug'] ?? '') ?>" placeholder="1-medio" required>
        <small>Usa minúsculas, números y guiones. Debe ser único.</small>
    </label>
    <label>Orden
        <input type="number" name="sort_order" value="<?= h((string) ($course['sort_order'] ?? 0)) ?>" min="0" step="1">
        <small>Los cursos se muestran de menor a mayor orden.</small>
    </label>
    <label class="check-row">
        <input type="checkbox" name="is_active" value="1" <?= !empty($course['is_active']) ? 'checked' : '' ?>>
        <span>Curso habilitado en el formulario público</span>
    </label>
    <label class="check-row span-2">
        <input type="checkbox" name="is_new_slots" value="1" <?= !empty($course['is_new_slots']) ? 'checked' : '' ?>>
        <span>Marcar como nuevos cupos disponibles en el selector del formulario</span>
    </label>
    <div class="span-2 form-actions"><button class="btn primary">Guardar curso</button></div>
</form>
