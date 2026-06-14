<section class="hero-card compact-hero">
    <div>
        <p class="eyebrow">Gestión de admisión</p>
        <h2>Editar postulación</h2>
        <p>Actualiza los datos del apoderado, postulante y curso solicitado.</p>
    </div>
    <div class="hero-actions">
        <a class="btn secondary" href="<?= App::url('/admissions') ?>">Volver a postulaciones</a>
    </div>
</section>

<?php if (!empty($errors)): ?>
    <div class="alert error">
        <strong>Revisa la información ingresada:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="panel-card form-grid" method="post" action="<?= App::url('/admissions/update/' . h($application['id'] ?? ($_GET['id'] ?? ''))) ?>">
    <label>Nombres del apoderado
        <input name="nombres_apoderado" value="<?= h($application['nombres_apoderado'] ?? '') ?>" required>
    </label>
    <label>Apellidos del apoderado
        <input name="apellidos_apoderado" value="<?= h($application['apellidos_apoderado'] ?? '') ?>" required>
    </label>
    <label>Correo electrónico
        <input type="email" name="email" value="<?= h($application['email'] ?? '') ?>" required>
    </label>
    <label>Teléfono
        <input type="tel" name="telefono" value="<?= h($application['telefono'] ?? '') ?>" required>
    </label>
    <label>Nombre del estudiante
        <input name="estudiante" value="<?= h($application['estudiante'] ?? '') ?>" required>
    </label>
    <label>Postulante
        <select name="sexo_estudiante" required>
            <option value="">Selecciona una opción</option>
            <option value="nina" <?= ($application['sexo_estudiante'] ?? '') === 'nina' ? 'selected' : '' ?>>Niña</option>
            <option value="nino" <?= ($application['sexo_estudiante'] ?? '') === 'nino' ? 'selected' : '' ?>>Niño</option>
        </select>
    </label>
    <label>Fecha de nacimiento
        <input type="date" name="fecha_nacimiento" value="<?= h($application['fecha_nacimiento'] ?? '') ?>" required>
    </label>
    <label>Curso al que postula
        <select name="curso" required>
            <option value="">Selecciona un curso</option>
            <?php foreach (($courses ?? []) as $course): ?>
                <option value="<?= h($course['slug']) ?>" <?= ($application['curso'] ?? '') === $course['slug'] ? 'selected' : '' ?>><?= h($course['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="span-2">Mensaje adicional
        <textarea name="mensaje" rows="5"><?= h($application['mensaje'] ?? '') ?></textarea>
    </label>
    <input type="hidden" name="autorizacion" value="1">
    <div class="span-2 form-actions">
        <button class="btn primary" type="submit">Guardar cambios</button>
    </div>
</form>
