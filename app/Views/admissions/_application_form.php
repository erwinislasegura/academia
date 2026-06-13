<?php if (!function_exists('h')) { function h(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); } } ?>
          <div class="form-card">
            <div class="form-card__head">
              <div>
                <h2>Formulario de postulación</h2>
                <p>Completa los datos solicitados para iniciar el proceso de postulación 2027.</p>
              </div>

              <span class="form-badge">Año escolar 2027</span>
            </div>

            <?php if (!empty($success)): ?>
              <div class="public-alert public-alert--success">
                <strong>Se envió su postulación con éxito.</strong>
              </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
              <div class="public-alert public-alert--error">
                <strong>Revisa la información ingresada:</strong>
                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            <form class="form" action="<?= App::url($formAction ?? '/postula') ?>" method="POST">
              <input type="text" name="website" value="" autocomplete="off" tabindex="-1" aria-hidden="true" class="hp-field">
              <div class="form-row">
                <div class="field">
                  <label for="nombres_apoderado">Nombres del apoderado <span class="required">*</span></label>
                  <input type="text" id="nombres_apoderado" name="nombres_apoderado" value="<?= h($old['nombres_apoderado'] ?? '') ?>" placeholder="Ej: María José" required>
                </div>

                <div class="field">
                  <label for="apellidos_apoderado">Apellidos del apoderado <span class="required">*</span></label>
                  <input type="text" id="apellidos_apoderado" name="apellidos_apoderado" value="<?= h($old['apellidos_apoderado'] ?? '') ?>" placeholder="Ej: González Pérez" required>
                </div>
              </div>

              <div class="form-row">
                <div class="field">
                  <label for="email">Correo electrónico <span class="required">*</span></label>
                  <input type="email" id="email" name="email" value="<?= h($old['email'] ?? '') ?>" placeholder="correo@ejemplo.cl" required>
                  <span class="field-help">Usaremos este correo para contactarte durante el proceso.</span>
                </div>

                <div class="field">
                  <label for="telefono">Teléfono de contacto <span class="required">*</span></label>
                  <input type="tel" id="telefono" name="telefono" value="<?= h($old['telefono'] ?? '') ?>" placeholder="+56 9 1234 5678" inputmode="tel" autocomplete="tel" pattern="^(?:\+?56\s*)?9\s*\d{4}\s*\d{4}$|^\d{8}$" required>
                  <span class="field-help">Ingresa un celular chileno con WhatsApp. Ejemplos válidos: +56 9 1234 5678, 9 1234 5678 o 12345678.</span>
                </div>
              </div>

              <div class="form-row">
                <div class="field">
                  <label for="estudiante">Nombre del estudiante <span class="required">*</span></label>
                  <input type="text" id="estudiante" name="estudiante" value="<?= h($old['estudiante'] ?? '') ?>" placeholder="Nombre completo del estudiante" required>
                </div>

                <div class="field">
                  <label for="sexo_estudiante">Postulante <span class="required">*</span></label>
                  <select id="sexo_estudiante" name="sexo_estudiante" required>
                    <option value="">Selecciona una opción</option>
                    <option value="nina" <?= ($old['sexo_estudiante'] ?? '') === 'nina' ? 'selected' : '' ?>>Niña</option>
                    <option value="nino" <?= ($old['sexo_estudiante'] ?? '') === 'nino' ? 'selected' : '' ?>>Niño</option>
                  </select>
                </div>

                <div class="field">
                  <label for="fecha_nacimiento">Fecha de nacimiento <span class="required">*</span></label>
                  <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= h($old['fecha_nacimiento'] ?? '') ?>" required>
                  <span class="field-help">Nos permite calcular edad y preparar informes de admisión.</span>
                </div>
              </div>

              <div class="form-row">
                <div class="field">
                  <label for="curso">Curso al que postula <span class="required">*</span></label>
                  <select id="curso" name="curso" required>
                    <option value="">Selecciona un curso</option>
                    <?php foreach (($courses ?? []) as $course): ?>
                      <?php $courseLabel = $course['name'] . (!empty($course['is_new_slots']) ? ' · Nuevos cupos' : ''); ?>
                      <option value="<?= h($course['slug']) ?>" <?= ($old['curso'] ?? '') === $course['slug'] ? 'selected' : '' ?>><?= h($courseLabel) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (empty($courses)): ?>
                    <span class="field-help">Por ahora no hay cursos habilitados para nuevas postulaciones.</span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="field">
                <label for="mensaje">Mensaje adicional</label>
                <textarea id="mensaje" name="mensaje" placeholder="Puedes agregar información adicional sobre tu postulación o consulta."><?= h($old['mensaje'] ?? '') ?></textarea>
              </div>

              <label class="consent" for="autorizacion">
                <input type="checkbox" id="autorizacion" name="autorizacion" value="1" <?= !empty($old['autorizacion']) ? 'checked' : '' ?> required>
                <span>
                  Autorizo a Academia Iquique a contactarme por teléfono, WhatsApp o correo electrónico
                  para entregar información relacionada con el proceso de postulación 2027.
                </span>
              </label>

              <div class="form-actions">
                <small>
                  Los campos marcados con asterisco son obligatorios. La información será utilizada únicamente
                  para gestionar tu solicitud de postulación.
                </small>

                <button class="btn btn--red" type="submit">Enviar postulación</button>
              </div>
            </form>
          </div>
