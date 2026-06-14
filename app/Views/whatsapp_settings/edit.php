<section class="hero-card">
    <div>
        <p class="eyebrow">Administración</p>
        <h2>Plantilla de WhatsApp</h2>
        <p>Configura sólo la plantilla aprobada en Meta y su idioma. Las credenciales de WhatsApp Cloud API quedan fijas en el sistema.</p>
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

    <div class="span-2 form-actions"><button class="btn primary">Guardar plantilla</button></div>
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
