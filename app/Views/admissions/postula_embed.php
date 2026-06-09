<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Formulario de postulación | Academia Iquique</title>
  <meta name="description" content="Formulario de postulación de Academia Iquique para integrar en WordPress." />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --azul: #071D7A;
      --azul-profundo: #031052;
      --rojo: #D7192A;
      --rojo-suave: #FFF1F3;
      --fondo: #F6F8FC;
      --texto: #1E293B;
      --texto-suave: #64748B;
      --borde: #E2E8F0;
      --sombra: 0 18px 45px rgba(15, 23, 42, .08);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      background: transparent;
    }

    body {
      font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: transparent;
      color: var(--texto);
      line-height: 1.65;
      font-weight: 400;
      -webkit-font-smoothing: antialiased;
      padding: 0;
    }

    button,
    input,
    select,
    textarea {
      font: inherit;
    }

    .form-card {
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 16px;
      padding: 18px;
      box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
      max-width: 920px;
      margin: 0 auto;
    }

    .form-card__head {
      padding-bottom: 10px;
      margin-bottom: 12px;
      border-bottom: 1px solid var(--borde);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }

    .form-card__head h2 {
      color: var(--azul-profundo);
      font-size: 20px;
      line-height: 1.18;
      letter-spacing: -.02em;
      font-weight: 600;
      margin-bottom: 3px;
    }

    .form-card__head p {
      color: var(--texto-suave);
      font-size: 13px;
    }

    .form-badge {
      flex: 0 0 auto;
      display: inline-flex;
      align-items: center;
      padding: 6px 9px;
      border-radius: 6px;
      background: var(--rojo-suave);
      color: var(--rojo);
      font-size: 12px;
      font-weight: 700;
    }

    .form {
      display: grid;
      gap: 10px;
    }

    .form-row {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }

    .field {
      display: grid;
      gap: 4px;
    }

    label {
      color: #334155;
      font-size: 12.5px;
      font-weight: 600;
    }

    .required {
      color: var(--rojo);
    }

    input,
    select,
    textarea {
      width: 100%;
      border: 1px solid var(--borde);
      background: #fff;
      border-radius: 9px;
      padding: 8px 10px;
      color: var(--texto);
      outline: none;
      transition: .18s ease;
      font-size: 13px;
    }

    input::placeholder,
    textarea::placeholder {
      color: #94A3B8;
    }

    input:focus,
    select:focus,
    textarea:focus {
      border-color: rgba(7,29,122,.48);
      box-shadow: 0 0 0 4px rgba(7,29,122,.08);
    }

    textarea {
      resize: vertical;
      min-height: 58px;
    }

    .field-help {
      color: var(--texto-suave);
      display: none;
      font-size: 12px;
      line-height: 1.35;
    }

    .consent {
      display: grid;
      grid-template-columns: 18px 1fr;
      gap: 8px;
      align-items: start;
      padding: 9px 10px;
      border: 1px solid var(--borde);
      border-radius: 10px;
      background: var(--fondo);
    }

    .consent input {
      width: 18px;
      height: 18px;
      padding: 0;
      margin-top: 2px;
      accent-color: var(--azul);
    }

    .consent span {
      color: #475569;
      font-size: 12px;
      line-height: 1.35;
    }

    .form-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding-top: 0;
    }

    .form-actions small {
      color: var(--texto-suave);
      font-size: 11.5px;
      max-width: 440px;
      line-height: 1.35;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 38px;
      padding: 0 16px;
      border-radius: 6px;
      border: 1px solid transparent;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      line-height: 1;
      transition: .22s ease;
      white-space: nowrap;
    }

    .btn--red {
      background: var(--rojo);
      color: #fff;
      box-shadow: 0 10px 22px rgba(215,25,42,.18);
    }

    .btn--red:hover {
      background: #b91523;
      transform: translateY(-1px);
    }

    .public-alert {
      border-radius: 16px;
      padding: 10px 12px;
      margin-bottom: 10px;
      border: 1px solid transparent;
      font-size: 14px;
    }

    .public-alert strong {
      display: block;
      margin-bottom: 6px;
    }

    .public-alert ul {
      margin: 4px 0 0 18px;
    }

    .public-alert--success {
      background: #EAFBF1;
      border-color: #BDECCD;
      color: #166534;
    }

    .public-alert--error {
      background: #FFF1F3;
      border-color: #FFD0D6;
      color: #9F1239;
    }

    .hp-field {
      position: absolute;
      left: -10000px;
      width: 1px;
      height: 1px;
      opacity: 0;
    }

    @media (max-width: 720px) {
      .form-row {
        grid-template-columns: 1fr;
      }

      .form-card {
        padding: 14px;
        border-radius: 14px;
      }

      .form-card__head {
        flex-direction: column;
      }

      .form-actions {
        flex-direction: column;
        align-items: stretch;
      }

      .form-actions .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <?php $formAction = '/postula-embed.php'; ?>
  <?php require App::root('app/Views/admissions/_application_form.php'); ?>
</body>
</html>
