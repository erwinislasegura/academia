<section class="hero-card">
    <div>
        <p class="eyebrow">Notificaciones</p>
        <h2>Configuración de correo</h2>
        <p>Define el servidor SMTP usado para enviar avisos internos y el correo HTML al postulante.</p>
    </div>
    <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique">
</section>

<form class="panel-card form-grid" method="post" action="<?= App::url('/mail-settings') ?>">
    <?php foreach (($errors ?? []) as $error): ?><div class="field-error span-2"><?= h($error) ?></div><?php endforeach; ?>

    <label>Método de envío
        <select name="mailer">
            <option value="smtp" <?= ($settings['mailer'] ?? '') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
            <option value="mail" <?= ($settings['mailer'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP mail()</option>
        </select>
        <small>SMTP es recomendado para que los correos salgan autenticados.</small>
    </label>

    <label>Servidor SMTP
        <input type="text" name="host" value="<?= h($settings['host'] ?? '') ?>" placeholder="academia.gocreative.cl">
    </label>

    <label>Puerto SMTP
        <input type="number" name="port" value="<?= h($settings['port'] ?? 465) ?>" min="1">
    </label>

    <label>Cifrado
        <select name="encryption">
            <option value="ssl" <?= ($settings['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL/TLS</option>
            <option value="tls" <?= ($settings['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>STARTTLS</option>
            <option value="none" <?= ($settings['encryption'] ?? '') === 'none' ? 'selected' : '' ?>>Sin cifrado</option>
        </select>
    </label>

    <label class="span-2">Usuario SMTP
        <input type="email" name="username" value="<?= h($settings['username'] ?? '') ?>" placeholder="notificacion@academia.gocreative.cl">
    </label>

    <label class="span-2">Contraseña SMTP
        <input type="password" name="password" value="" autocomplete="new-password" placeholder="<?= !empty($settings['password']) ? 'Contraseña configurada' : '' ?>">
        <small>Déjala en blanco para mantener la contraseña actual.</small>
    </label>

    <label>Correo remitente
        <input type="email" name="from_address" value="<?= h($settings['from_address'] ?? '') ?>" placeholder="notificacion@academia.gocreative.cl">
    </label>

    <label>Nombre remitente
        <input type="text" name="from_name" value="<?= h($settings['from_name'] ?? '') ?>" placeholder="Academia Iquique">
    </label>

    <label class="span-2">Enviar correo de prueba a
        <input type="email" name="test_email" value="<?= h($testEmail ?? ($settings['from_address'] ?? '')) ?>" placeholder="correo@ejemplo.cl">
        <small>Guarda la configuración y envía un correo simple para confirmar que el SMTP funciona.</small>
    </label>

    <div class="panel-card span-2" style="background:#f8faff; box-shadow:none;">
        <div class="section-head"><h3>Valores recomendados cargados</h3></div>
        <div class="muted-text">Servidor saliente: academia.gocreative.cl · Puerto SMTP: 465 · Cifrado: SSL/TLS · Requiere autenticación.</div>
    </div>

    <div class="span-2 form-actions" style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn primary" type="submit" name="action" value="save">Guardar configuración</button>
        <button class="btn secondary" type="submit" name="action" value="test">Guardar y enviar prueba</button>
    </div>
</form>
