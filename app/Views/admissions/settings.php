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
        <small>Variables disponibles: {{nombres_apoderado}}, {{apellidos_apoderado}}, {{nombre_apoderado}}, {{email}}, {{telefono}}, {{estudiante}}, {{curso}}, {{mensaje}}. Compatibles con el mensaje predeterminado: {name-2-first-name}, {name-2-last-name}, {email-1}, {phone-1}, {select-1}, {consent-1}, {site_url}.</small>
    </label>

    <div class="panel-card span-2" style="background:#f8faff; box-shadow:none;">
        <div class="section-head"><h3>WhatsApp automático</h3></div>
        <label style="display:flex;gap:10px;align-items:center;margin-bottom:14px;">
            <input type="checkbox" name="whatsapp_enabled" value="1" <?= !empty($settings['whatsapp_enabled']) ? 'checked' : '' ?>>
            Enviar un WhatsApp al teléfono informado cuando se registre la postulación.
        </label>
        <div class="form-grid" style="padding:0;">
            <label>ID del número de WhatsApp Business
                <input type="text" name="whatsapp_phone_number_id" value="<?= h($settings['whatsapp_phone_number_id'] ?? '') ?>" placeholder="Ej: 123456789012345">
                <small>Corresponde al Phone Number ID de WhatsApp Cloud API.</small>
            </label>
            <label>Token de acceso
                <input type="password" name="whatsapp_access_token" value="" autocomplete="off" placeholder="<?= !empty($settings['whatsapp_access_token']) ? 'Token ya configurado' : '' ?>">
                <small>Déjalo en blanco para mantener el token actual. Debe tener permiso para enviar mensajes desde el número configurado.</small>
            </label>
            <label class="span-2">Mensaje de WhatsApp
                <textarea name="whatsapp_message_template" rows="5"><?= h($settings['whatsapp_message_template'] ?? '') ?></textarea>
                <small>Variables disponibles: {{nombres_apoderado}}, {{apellidos_apoderado}}, {{nombre_apoderado}}, {{email}}, {{telefono}}, {{estudiante}}, {{curso}}, {{mensaje}}. Para mensajes iniciados por la institución, WhatsApp puede exigir una plantilla aprobada.</small>
            </label>
        </div>
    </div>

    <div class="panel-card span-2" style="background:#f8faff; box-shadow:none;">
        <div class="section-head"><h3>Vista previa</h3></div>
        <div class="muted-text">El correo se enviará como HTML. El WhatsApp se enviará como texto plano al teléfono de contacto normalizado a formato internacional cuando sea posible.</div>
    </div>

    <div class="span-2 form-actions"><button class="btn primary">Guardar configuración</button></div>
</form>
