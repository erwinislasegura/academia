<section class="hero-card">
    <div>
        <p class="eyebrow">Admisión pública</p>
        <h2>Configuración de postulaciones</h2>
        <p>Define el correo que recibirá las postulaciones y el mensaje HTML automático para cada postulante.</p>
    </div>
    <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique">
</section>

<form class="panel-card form-grid" method="post" action="<?= App::url('/admission-settings') ?>">
    <?php foreach (($errors ?? []) as $error): ?><div class="field-error span-2"><?= h($error) ?></div><?php endforeach; ?>

    <label class="span-2">Correo receptor de postulaciones
        <input type="email" name="notification_email" value="<?= h($settings['notification_email'] ?? '') ?>" required>
        <small>Cuando una familia postule, el detalle de la postulación llegará a este correo.</small>
    </label>

    <label class="span-2">Asunto del correo al postulante
        <input type="text" name="applicant_subject" value="<?= h($settings['applicant_subject'] ?? '') ?>" required>
    </label>

    <label class="span-2">Mensaje HTML para el postulante
        <textarea name="applicant_html" rows="12" required><?= h($settings['applicant_html'] ?? '') ?></textarea>
        <small>Variables disponibles: {{nombres_apoderado}}, {{apellidos_apoderado}}, {{nombre_apoderado}}, {{email}}, {{telefono}}, {{estudiante}}, {{curso}}, {{mensaje}}.</small>
    </label>

    <div class="panel-card span-2" style="background:#f8faff; box-shadow:none;">
        <div class="section-head"><h3>Vista previa</h3></div>
        <div class="muted-text">El contenido se enviará como correo HTML. Usa etiquetas simples como &lt;p&gt;, &lt;strong&gt; y &lt;br&gt;.</div>
    </div>

    <div class="span-2 form-actions"><button class="btn primary">Guardar configuración</button></div>
</form>
