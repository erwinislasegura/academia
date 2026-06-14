<section class="hero-card">
    <div>
        <p class="eyebrow">Postulación pública</p>
        <h2>Configuración de postulaciones</h2>
        <p>Define el correo que recibirá las postulaciones y el mensaje HTML automático para cada postulante.</p>
    </div>
    <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique">
</section>

<form class="panel-card form-grid" method="post" action="<?= App::url('/admission-settings') ?>">
    <?php foreach (($errors ?? []) as $error): ?><div class="field-error span-2"><?= h($error) ?></div><?php endforeach; ?>

    <label class="span-2">Correo receptor de postulaciones
        <input type="email" name="notification_email" value="<?= h($settings['notification_email'] ?? '') ?>" required>
        <small>Cuando una familia postule, el detalle de la postulación llegará a este correo. Para enviar el correo HTML al postulante, el servidor debe tener SMTP configurado con MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS y MAIL_FROM_NAME.</small>
    </label>

    <label class="span-2">Asunto del correo al postulante
        <input type="text" name="applicant_subject" value="<?= h($settings['applicant_subject'] ?? '') ?>" required>
    </label>

    <section class="span-2 email-template-workspace" aria-labelledby="email-template-title">
        <div class="email-template-editor panel-card">
            <div class="section-head email-template-head">
                <div>
                    <h3 id="email-template-title">Mensaje HTML para el postulante</h3>
                    <p>Edita el correo en un editor claro y amplio. Puedes dar formato automático antes de guardar.</p>
                </div>
                <button class="btn secondary" type="button" id="format-applicant-html">Formatear HTML</button>
            </div>
            <label class="code-editor-label">Editor HTML
                <textarea class="code-editor-textarea" name="applicant_html" rows="30" spellcheck="false" wrap="off" required><?= h($settings['applicant_html'] ?? '') ?></textarea>
                <small>Variables disponibles: {{nombres_apoderado}}, {{apellidos_apoderado}}, {{nombre_apoderado}}, {{email}}, {{telefono}}, {{estudiante}}, {{curso}}, {{mensaje}}. Compatibles con el mensaje predeterminado: {name-2-first-name}, {name-2-last-name}, {email-1}, {phone-1}, {select-1}, {consent-1}, {site_url}.</small>
            </label>
        </div>

        <aside class="email-preview-panel panel-card" aria-labelledby="email-preview-title">
            <div class="section-head email-preview-head">
                <div>
                    <h3 id="email-preview-title">Vista previa del correo</h3>
                    <p>Siempre visible junto al código. Se actualiza mientras editas con datos de ejemplo.</p>
                </div>
            </div>
            <div class="email-subject-preview-card">
                <span>Asunto</span>
                <strong id="applicant-email-subject-preview"><?= h($settings['applicant_subject'] ?? '') ?></strong>
            </div>
            <div class="email-preview-frame-card">
                <div class="email-preview-frame-head">
                    <strong>Correo al postulante</strong>
                    <span>Vista HTML</span>
                </div>
                <iframe id="applicant-email-preview" title="Vista previa del correo al postulante" sandbox="" srcdoc="<?= h($applicantPreviewHtml ?? '') ?>"></iframe>
            </div>
        </aside>
    </section>

    <div class="panel-card span-2" style="background:#f8faff; box-shadow:none;">
        <div class="section-head"><h3>WhatsApp automático por WhatsApp Cloud API</h3></div>
        <label style="display:flex;gap:10px;align-items:center;margin-bottom:14px;">
            <input type="checkbox" name="whatsapp_enabled" value="1" <?= !empty($settings['whatsapp_enabled']) ? 'checked' : '' ?>>
            Enviar un WhatsApp al teléfono informado cuando se registre la postulación.
        </label>
        <div class="form-grid" style="padding:0;">
            <label>URL base de WhatsApp Cloud API
                <input type="text" name="whatsapp_base_url" value="<?= h($settings['whatsapp_base_url'] ?? '') ?>" placeholder="https://graph.facebook.com/v25.0">
                <small>Opcional. Por defecto se usa https://graph.facebook.com/v25.0.</small>
            </label>
            <label>Phone Number ID
                <input type="text" name="whatsapp_phone_number_id" value="<?= h($settings['whatsapp_phone_number_id'] ?? '') ?>" placeholder="637971779395576">
                <small>ID del número telefónico configurado en Meta/WhatsApp Cloud API.</small>
            </label>
            <label>WhatsApp Business Account ID
                <input type="text" name="whatsapp_business_account_id" value="<?= h($settings['whatsapp_business_account_id'] ?? '') ?>" placeholder="646043211679831">
                <small>Referencia del WABA asociado al número.</small>
            </label>
            <label>Token de acceso de Meta
                <input type="password" name="whatsapp_api_key" value="" autocomplete="off" placeholder="<?= !empty($settings['whatsapp_api_key']) ? 'Token ya configurado' : '' ?>">
                <small>Déjala en blanco para mantener la clave actual. Debe tener permisos para enviar mensajes por WhatsApp Cloud API.</small>
            </label>
            <label>Template de confirmación
                <input type="text" name="whatsapp_template_name" value="<?= h($settings['whatsapp_template_name'] ?? 'hello_world') ?>" placeholder="hello_world">
                <small>Debe existir y estar aprobado en Meta si decides enviar plantillas.</small>
            </label>
            <label>Idioma del template
                <input type="text" name="whatsapp_template_language" value="<?= h($settings['whatsapp_template_language'] ?? 'en_US') ?>" placeholder="en_US">
                <small>Debe coincidir exactamente con el idioma aprobado para la plantilla.</small>
            </label>
            <label class="span-2">Mensaje de texto libre (fallback, sólo ventana 24h)
                <textarea name="whatsapp_message_template" rows="5"><?= h($settings['whatsapp_message_template'] ?? '') ?></textarea>
                <small>Para hello_world no se envían parámetros, replicando el cURL oficial de Meta. Si configuras una plantilla propia de admisión, se enviarán {{1}} Nombre completo del apoderado, {{2}} Nombre del estudiante, {{3}} Curso y {{4}} Fecha de postulación. Este texto se usa sólo como fallback dentro de la ventana de 24 horas.</small>
            </label>
        </div>
    </div>

    <div class="span-2 form-actions"><button class="btn primary">Guardar configuración</button></div>
</form>

<form class="panel-card form-grid" method="post" action="<?= App::url('/whatsapp/test-settings') ?>" style="margin-top:18px;">
    <div class="span-2 section-head">
        <div>
            <h3>Probar API de WhatsApp</h3>
            <p>Envía un mensaje de prueba con la configuración guardada de WhatsApp Cloud API. Si cambiaste credenciales o plantillas, guarda primero la configuración.</p>
            <p>Template actual: <strong><?= h($settings['whatsapp_template_name'] ?? '') ?></strong> · idioma configurado: <strong><?= h($settings['whatsapp_template_language'] ?? '') ?></strong>. Si Meta informa que no existe la traducción, el sistema intentará detectar otro idioma aprobado del mismo template.</p>
        </div>
    </div>
    <label>Teléfono destinatario
        <input type="text" name="to" placeholder="+56 9 1234 5678" required>
        <small>Usa un celular chileno habilitado para WhatsApp.</small>
    </label>
    <label>Tipo de envío
        <select name="send_mode">
            <option value="template">Template configurado (recomendado)</option>
            <option value="text">Texto libre (sólo ventana 24h)</option>
        </select>
        <small>La prueba por template usa la misma plantilla configurada para el envío automático al completar el formulario.</small>
    </label>
    <label>URL base para esta prueba
        <input type="text" name="test_base_url" value="<?= h($settings['whatsapp_base_url'] ?? 'https://graph.facebook.com/v25.0') ?>">
        <small>Por defecto usa la misma URL base guardada para el envío automático.</small>
    </label>
    <label>Template de prueba
        <input type="text" name="test_template_name" value="<?= h($settings['whatsapp_template_name'] ?? 'hello_world') ?>">
        <small>Por defecto usa la misma plantilla que se enviará al llenar el formulario.</small>
    </label>
    <label>Idioma del template de prueba
        <input type="text" name="test_template_language" value="<?= h($settings['whatsapp_template_language'] ?? 'en_US') ?>">
        <small>Debe coincidir con el idioma aprobado en Meta para la plantilla configurada.</small>
    </label>
    <label>Mensaje de prueba para texto libre
        <input type="text" name="message" value="Mensaje de prueba desde el panel de administración de Academia Iquique.">
        <small>Este campo se usa sólo si eliges texto libre; WhatsApp Cloud API puede rechazarlo si no hay una conversación reciente.</small>
    </label>
    <label>Nombre apoderado de prueba
        <input type="text" name="guardian_name" value="Nombre completo del apoderado">
    </label>
    <label>Estudiante de prueba
        <input type="text" name="student_name" value="Nombre del estudiante">
    </label>
    <label>Curso de prueba
        <input type="text" name="course" value="Curso">
        <small>Estos datos completan {{1}}, {{2}} y {{3}} en plantillas propias; hello_world se envía sin parámetros.</small>
    </label>
    <div class="span-2 form-actions">
        <button class="btn secondary" type="submit">Enviar prueba por WhatsApp</button>
    </div>
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

    const formatHtml = (html) => {
        const voidTags = new Set(['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr']);
        const tokens = String(html || '')
            .replace(/>\s*</g, '><')
            .match(/<!--([\s\S]*?)-->|<![^>]*>|<\/?[^>]+>|[^<]+/g) || [];
        let level = 0;

        return tokens.map((token) => token.trim()).filter(Boolean).map((token) => {
            const closingTag = token.match(/^<\s*\/([a-z0-9:-]+)/i);
            const openingTag = token.match(/^<\s*([a-z0-9:-]+)/i);
            const isCommentOrDoctype = /^<!(--)?/i.test(token);
            const isSelfClosing = /\/\s*>$/.test(token);
            const tagName = openingTag?.[1]?.toLowerCase();

            if (closingTag) {
                level = Math.max(level - 1, 0);
            }

            const line = `${'  '.repeat(level)}${token}`;

            if (openingTag && !closingTag && !isCommentOrDoctype && !isSelfClosing && !voidTags.has(tagName)) {
                level += 1;
            }

            return line;
        }).join('\n');
    };

    const updatePreview = () => {
        if (previewFrame && htmlInput) {
            previewFrame.srcdoc = renderTemplate(htmlInput.value);
        }
        if (subjectPreview && subjectInput) {
            subjectPreview.textContent = subjectInput.value || 'Sin asunto';
        }
    };

    document.getElementById('format-applicant-html')?.addEventListener('click', () => {
        if (!htmlInput) return;
        htmlInput.value = formatHtml(htmlInput.value);
        htmlInput.focus();
        updatePreview();
    });

    if (htmlInput) {
        htmlInput.addEventListener('input', updatePreview);
    }
    if (subjectInput) {
        subjectInput.addEventListener('input', updatePreview);
    }
    updatePreview();
})();
</script>
