<section class="hero-card">
    <div>
        <p class="eyebrow">Administración</p>
        <h2>Plantilla de WhatsApp</h2>
        <p>Configura la plantilla aprobada en Meta, su idioma y el token de acceso de WhatsApp Cloud API.</p>
    </div>
    <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique">
</section>

<form class="panel-card form-grid" method="post" action="<?= App::url('/whatsapp-settings') ?>">
    <?php foreach (($errors ?? []) as $error): ?><div class="field-error span-2"><?= h($error) ?></div><?php endforeach; ?>

    <div class="span-2 section-head">
        <div>
            <h3>Configuración de plantilla</h3>
            <p>Plantilla esperada: <strong>admision2027_final</strong> · idioma: <strong>en_US</strong>. La solicitud a Meta no incluye variables de cuerpo y usa la misma estructura que hello_world: sólo nombre de plantilla e idioma.</p>
        </div>
    </div>

    <label>Nombre de la plantilla
        <input type="text" name="whatsapp_template_name" value="<?= h($settings['whatsapp_template_name'] ?? 'admision2027_final') ?>" placeholder="admision2027_final" required>
    </label>

    <label>Idioma de la plantilla
        <input type="text" name="whatsapp_template_language" value="<?= h($settings['whatsapp_template_language'] ?? 'en_US') ?>" placeholder="en_US" required>
    </label>


    <label class="span-2">Token de acceso de Meta
        <div style="display:flex; gap:10px; align-items:center;">
            <input id="whatsapp-api-key" type="password" name="whatsapp_api_key" value="<?= h($settings['whatsapp_api_key'] ?? '') ?>" autocomplete="off" required style="flex:1;">
            <button class="btn secondary" type="button" id="toggle-whatsapp-token">Mostrar</button>
        </div>
        <small>Este token se guarda como admission_whatsapp_api_key y admission_whatsapp_access_token para la API de WhatsApp Cloud.</small>
    </label>

    <div class="span-2 form-actions"><button class="btn primary">Guardar configuración</button></div>
</form>

<form class="panel-card form-grid" method="post" action="<?= App::url('/whatsapp/test-settings') ?>" style="margin-top:18px;">
    <div class="span-2 section-head">
        <div>
            <h3>Probar plantilla de WhatsApp</h3>
            <p>Prueba la solicitud a Meta con la plantilla configurada. Si cambias la plantilla o el idioma, guarda primero.</p>
        </div>
    </div>
    <input type="hidden" name="send_mode" value="template">
    <input type="hidden" name="test_template_name" value="<?= h($settings['whatsapp_template_name'] ?? 'admision2027_final') ?>">
    <input type="hidden" name="test_template_language" value="<?= h($settings['whatsapp_template_language'] ?? 'en_US') ?>">

    <label>Teléfono destinatario
        <input type="text" name="to" value="56944627287" placeholder="+56 9 1234 5678" required>
        <small>Usa un celular chileno habilitado para WhatsApp.</small>
    </label>
    <div class="span-2 form-actions">
        <button class="btn secondary" type="submit">Probar solicitud WhatsApp</button>
    </div>
</form>

<script>
(() => {
    const tokenInput = document.getElementById('whatsapp-api-key');
    const toggleButton = document.getElementById('toggle-whatsapp-token');
    if (!tokenInput || !toggleButton) {
        return;
    }
    toggleButton.addEventListener('click', () => {
        const showing = tokenInput.type === 'text';
        tokenInput.type = showing ? 'password' : 'text';
        toggleButton.textContent = showing ? 'Mostrar' : 'Ocultar';
    });
})();
</script>
