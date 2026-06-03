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
        <div class="section-head"><h3>Vista previa del correo</h3></div>
        <div class="muted-text" style="margin-bottom:14px;">Esta vista se actualiza mientras editas el asunto y el HTML. Se usan datos de ejemplo para reemplazar variables.</div>
        <div style="display:grid;gap:12px;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px;">
                <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:700;margin-bottom:6px;">Asunto</div>
                <div id="applicant-email-subject-preview" style="font-weight:700;color:#0f172a;"><?= h($settings['applicant_subject'] ?? '') ?></div>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;background:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:10px 14px;color:#475569;font-size:13px;">
                    <strong>Correo al postulante</strong>
                    <span>Vista HTML</span>
                </div>
                <iframe id="applicant-email-preview" title="Vista previa del correo al postulante" sandbox="" srcdoc="<?= h($applicantPreviewHtml ?? '') ?>" style="display:block;width:100%;min-height:560px;border:0;background:#fff;"></iframe>
            </div>
        </div>
    </div>

    <div class="span-2 form-actions"><button class="btn primary">Guardar configuración</button></div>
</form>

<?php
$previewReplacements = [];
foreach (($previewApplication ?? []) as $key => $value) {
    $previewReplacements['{{' . $key . '}}'] = (string) $value;
}
$previewReplacements['{{nombre_apoderado}}'] = trim(($previewApplication['nombres_apoderado'] ?? '') . ' ' . ($previewApplication['apellidos_apoderado'] ?? ''));
?>
<script>
(() => {
    const htmlInput = document.querySelector('[name="applicant_html"]');
    const subjectInput = document.querySelector('[name="applicant_subject"]');
    const previewFrame = document.getElementById('applicant-email-preview');
    const subjectPreview = document.getElementById('applicant-email-subject-preview');
    const replacements = <?= json_encode($previewReplacements, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const renderTemplate = (template) => {
        let rendered = template || '';
        Object.entries(replacements).forEach(([placeholder, value]) => {
            rendered = rendered.split(placeholder).join(escapeHtml(value));
        });
        return rendered;
    };

    const updatePreview = () => {
        if (previewFrame && htmlInput) {
            previewFrame.srcdoc = renderTemplate(htmlInput.value);
        }
        if (subjectPreview && subjectInput) {
            subjectPreview.textContent = subjectInput.value || 'Sin asunto';
        }
    };

    if (htmlInput) {
        htmlInput.addEventListener('input', updatePreview);
    }
    if (subjectInput) {
        subjectInput.addEventListener('input', updatePreview);
    }
    updatePreview();
})();
</script>
