<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Proceso de postulación 2027 | Academia Iquique</title>

  <meta name="description" content="Proceso de postulación 2027 de Academia Iquique. Postula y conoce nuestro proyecto educativo." />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --azul: #071D7A;
      --azul-profundo: #031052;
      --azul-suave: #EEF3FF;
      --rojo: #D7192A;
      --rojo-suave: #FFF1F3;
      --blanco: #FFFFFF;
      --fondo: #F6F8FC;
      --gris: #EEF2F7;
      --texto: #1E293B;
      --texto-suave: #64748B;
      --borde: #E2E8F0;
      --borde-oscuro: rgba(255,255,255,.16);
      --sombra: 0 18px 45px rgba(15, 23, 42, .08);
      --sombra-suave: 0 10px 28px rgba(15, 23, 42, .06);
      --radio: 18px;
      --radio-sm: 12px;
      --contenedor: 1180px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: var(--fondo);
      color: var(--texto);
      line-height: 1.65;
      font-weight: 400;
      -webkit-font-smoothing: antialiased;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    img {
      max-width: 100%;
      display: block;
    }

    button,
    input,
    select,
    textarea {
      font: inherit;
    }

    .container {
      width: min(var(--contenedor), calc(100% - 40px));
      margin-inline: auto;
    }

    .topbar {
      background: var(--azul-profundo);
      color: rgba(255,255,255,.82);
      font-size: 13px;
    }

    .topbar__inner {
      width: min(var(--contenedor), calc(100% - 40px));
      margin-inline: auto;
      min-height: 38px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
    }

    .topbar__info {
      display: flex;
      align-items: center;
      gap: 22px;
      flex-wrap: wrap;
    }

    .topbar__item {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      white-space: nowrap;
    }

    .topbar__social {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .topbar__social a {
      width: 26px;
      height: 26px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      border: 1px solid rgba(255,255,255,.16);
      color: rgba(255,255,255,.78);
      font-size: 12px;
      transition: .2s ease;
    }

    .topbar__social a:hover {
      background: rgba(255,255,255,.1);
      color: #fff;
    }

    .site-header {
      position: sticky;
      top: 0;
      z-index: 50;
      background: rgba(255,255,255,.92);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid rgba(226,232,240,.9);
    }

    .nav {
      width: min(var(--contenedor), calc(100% - 40px));
      margin-inline: auto;
      height: 82px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 26px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-shrink: 0;
    }

    .logo img {
      height: 54px;
      width: auto;
      object-fit: contain;
    }

    .menu {
      display: flex;
      align-items: center;
      gap: 4px;
      list-style: none;
      margin-left: auto;
    }

    .menu a {
      display: inline-flex;
      align-items: center;
      min-height: 38px;
      padding: 0 13px;
      border-radius: 999px;
      color: #334155;
      font-size: 14px;
      font-weight: 600;
      transition: .2s ease;
    }

    .menu a:hover,
    .menu a.is-active {
      color: var(--azul);
      background: var(--azul-suave);
    }

    .nav__actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-shrink: 0;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 44px;
      padding: 0 18px;
      border-radius: 6px;
      border: 1px solid transparent;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      line-height: 1;
      transition: .22s ease;
      white-space: nowrap;
    }

    .btn--primary {
      background: var(--azul);
      color: #fff;
      box-shadow: 0 10px 22px rgba(7, 29, 122, .18);
    }

    .btn--primary:hover {
      background: var(--azul-profundo);
      transform: translateY(-1px);
    }

    .btn--red {
      background: var(--rojo);
      color: #fff;
      box-shadow: 0 10px 22px rgba(215, 25, 42, .18);
    }

    .btn--red:hover {
      background: #bd1424;
      transform: translateY(-1px);
    }

    .btn--ghost {
      background: #fff;
      color: var(--azul);
      border-color: var(--borde);
    }

    .btn--ghost:hover {
      border-color: rgba(7,29,122,.25);
      background: var(--azul-suave);
    }

    .menu-toggle {
      display: none;
      width: 42px;
      height: 42px;
      border: 1px solid var(--borde);
      background: #fff;
      border-radius: 12px;
      color: var(--azul);
      cursor: pointer;
      align-items: center;
      justify-content: center;
    }

    .menu-toggle span {
      width: 18px;
      height: 2px;
      background: currentColor;
      display: block;
      position: relative;
    }

    .menu-toggle span::before,
    .menu-toggle span::after {
      content: "";
      position: absolute;
      left: 0;
      width: 18px;
      height: 2px;
      background: currentColor;
    }

    .menu-toggle span::before {
      top: -6px;
    }

    .menu-toggle span::after {
      top: 6px;
    }

    .hero {
      background:
        linear-gradient(180deg, #fff 0%, #F6F8FC 100%);
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: "";
      position: absolute;
      right: -160px;
      top: -180px;
      width: 460px;
      height: 460px;
      border-radius: 50%;
      background: rgba(7, 29, 122, .06);
    }

    .hero::after {
      content: "";
      position: absolute;
      left: -140px;
      bottom: -220px;
      width: 420px;
      height: 420px;
      border-radius: 50%;
      background: rgba(215, 25, 42, .045);
    }

    .hero__inner {
      position: relative;
      z-index: 1;
      width: min(var(--contenedor), calc(100% - 40px));
      margin-inline: auto;
      min-height: 560px;
      display: grid;
      grid-template-columns: 1.02fr .98fr;
      gap: 50px;
      align-items: center;
      padding: 58px 0 66px;
    }

    .label {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 8px 13px;
      border-radius: 999px;
      border: 1px solid rgba(7, 29, 122, .13);
      background: #fff;
      color: var(--azul);
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 22px;
      box-shadow: 0 8px 22px rgba(15,23,42,.04);
    }

    .label::before {
      content: "";
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--rojo);
    }

    .hero h1 {
      max-width: 650px;
      color: var(--azul-profundo);
      font-size: clamp(34px, 4.4vw, 48px);
      line-height: 1.12;
      letter-spacing: -0.035em;
      font-weight: 600;
      margin-bottom: 18px;
    }

    .hero h1 span {
      color: var(--azul);
    }

    .hero__text {
      max-width: 585px;
      color: var(--texto-suave);
      font-size: 17px;
      line-height: 1.8;
      margin-bottom: 30px;
    }

    .hero__actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 34px;
    }

    .hero__note {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      max-width: 520px;
      color: #475569;
      font-size: 14px;
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: var(--radio-sm);
      padding: 14px 16px;
      box-shadow: var(--sombra-suave);
    }

    .hero__note strong {
      color: var(--azul);
      font-weight: 700;
    }

    .hero__note-mark {
      width: 28px;
      height: 28px;
      border-radius: 9px;
      background: var(--azul-suave);
      color: var(--azul);
      display: grid;
      place-items: center;
      flex: 0 0 auto;
      font-weight: 800;
      font-size: 13px;
    }

    .hero__visual {
      position: relative;
    }

    .image-card {
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 24px;
      padding: 14px;
      box-shadow: var(--sombra);
    }

    .image-card__photo {
      min-height: 395px;
      border-radius: 18px;
      background:
        linear-gradient(rgba(3,16,82,.05), rgba(3,16,82,.12)),
        url("<?= App::asset('/images/imagen1.png') ?>") center/cover;
    }

    .image-card__bottom {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      padding-top: 14px;
    }

    .mini-stat {
      border: 1px solid var(--borde);
      background: #fff;
      border-radius: 14px;
      padding: 14px 12px;
    }

    .mini-stat strong {
      display: block;
      color: var(--azul);
      font-size: 18px;
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 3px;
    }

    .mini-stat span {
      display: block;
      color: var(--texto-suave);
      font-size: 12px;
      font-weight: 600;
    }

    .floating-card {
      position: absolute;
      left: -26px;
      bottom: 78px;
      width: 230px;
      background: rgba(255,255,255,.94);
      backdrop-filter: blur(12px);
      border: 1px solid var(--borde);
      border-radius: 18px;
      padding: 16px;
      box-shadow: var(--sombra);
    }

    .floating-card small {
      display: block;
      color: var(--rojo);
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 6px;
    }

    .floating-card strong {
      display: block;
      color: var(--azul-profundo);
      font-size: 16px;
      font-weight: 700;
      line-height: 1.35;
    }

    .section {
      padding: 72px 0;
    }

    .section--white {
      background: #fff;
    }

    .section--soft {
      background: var(--fondo);
    }

    .section--form {
      background: #fff;
      padding-top: 64px;
    }

    .section-head {
      max-width: 720px;
      margin-bottom: 38px;
    }

    .eyebrow {
      color: var(--rojo);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
      margin-bottom: 10px;
    }

    .section-head h2 {
      color: var(--azul-profundo);
      font-size: clamp(24px, 3vw, 34px);
      line-height: 1.22;
      letter-spacing: -0.025em;
      font-weight: 500;
      margin-bottom: 14px;
    }

    .section-head p {
      color: var(--texto-suave);
      font-size: 16.5px;
      line-height: 1.8;
    }

    .trust-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
      margin-top: -28px;
      position: relative;
      z-index: 5;
    }

    .trust-item {
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 18px;
      padding: 20px;
      box-shadow: var(--sombra-suave);
    }

    .trust-item strong {
      display: block;
      color: var(--azul);
      font-size: 18px;
      font-weight: 600;
      line-height: 1.1;
      margin-bottom: 6px;
    }

    .trust-item span {
      color: var(--texto-suave);
      font-size: 13.5px;
      font-weight: 500;
    }

    .project-layout {
      display: grid;
      grid-template-columns: .9fr 1.1fr;
      gap: 48px;
      align-items: start;
    }

    .project-copy {
      padding-top: 8px;
    }

    .project-copy p {
      color: var(--texto-suave);
      font-size: 16.5px;
      line-height: 1.85;
    }

    .project-copy p + p {
      margin-top: 18px;
    }

    .features {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
    }

    .feature {
      display: grid;
      grid-template-columns: 82px 1fr;
      gap: 18px;
      align-items: center;
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 18px;
      padding: 18px;
      transition: .2s ease;
    }

    .feature:hover {
      transform: translateY(-2px);
      box-shadow: var(--sombra-suave);
      border-color: rgba(7,29,122,.18);
    }

    .feature__image {
      width: 82px;
      height: 82px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      background: var(--azul-suave);
      border: 1px solid rgba(7,29,122,.08);
      overflow: hidden;
    }

    .feature__image img {
      width: 72px;
      height: 72px;
      object-fit: contain;
    }

    .feature:nth-child(2) .feature__image {
      background: var(--rojo-suave);
      border-color: rgba(215,25,42,.10);
    }

    .feature h3 {
      color: var(--azul-profundo);
      font-size: 17px;
      font-weight: 600;
      letter-spacing: -0.015em;
      margin-bottom: 5px;
    }

    .feature p {
      color: var(--texto-suave);
      font-size: 14.5px;
      line-height: 1.65;
    }

    .process {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    .step-card {
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 18px;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }

    .step-card::before {
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 3px;
      background: var(--azul);
      opacity: .9;
    }

    .step-card:nth-child(2)::before {
      background: var(--rojo);
    }

    .step-illustration {
      width: 86px;
      height: 82px;
      display: grid;
      place-items: center;
      margin-bottom: 18px;
      border-radius: 18px;
      background: var(--azul-suave);
      border: 1px solid rgba(7,29,122,.08);
    }

    .step-illustration img {
      width: 70px;
      height: 70px;
      object-fit: contain;
    }

    .step-card:nth-child(2) .step-illustration {
      background: var(--rojo-suave);
      border-color: rgba(215,25,42,.10);
    }

    .step-number {
      position: absolute;
      right: 18px;
      top: 18px;
      width: 36px;
      height: 36px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: var(--azul);
      color: #fff;
      font-size: 14px;
      font-weight: 800;
      box-shadow: 0 10px 22px rgba(7,29,122,.18);
    }

    .step-card:nth-child(2) .step-number {
      background: var(--rojo);
      box-shadow: 0 10px 22px rgba(215,25,42,.18);
    }

    .step-card h3 {
      color: var(--azul-profundo);
      font-size: 17px;
      font-weight: 600;
      letter-spacing: -.015em;
      margin-bottom: 8px;
    }

    .step-card p {
      color: var(--texto-suave);
      font-size: 14.5px;
      line-height: 1.7;
    }

    .admission-wrap {
      display: grid;
      grid-template-columns: .78fr 1.22fr;
      gap: 28px;
      align-items: start;
    }

    .admission-aside {
      position: sticky;
      top: 112px;
      background: var(--azul-profundo);
      color: #fff;
      border-radius: 22px;
      padding: 28px;
      overflow: hidden;
    }

    .admission-aside::after {
      content: "";
      position: absolute;
      right: -80px;
      bottom: -90px;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: rgba(215,25,42,.22);
    }

    .admission-aside > * {
      position: relative;
      z-index: 1;
    }

    .admission-aside .eyebrow {
      color: rgba(255,255,255,.72);
    }

    .admission-aside h2 {
      font-size: 24px;
      line-height: 1.24;
      letter-spacing: -.02em;
      font-weight: 500;
      margin-bottom: 14px;
    }

    .admission-aside p {
      color: rgba(255,255,255,.76);
      font-size: 15.5px;
      line-height: 1.75;
      margin-bottom: 24px;
    }

    .contact-list {
      display: grid;
      gap: 12px;
      margin-top: 24px;
    }

    .contact-item {
      display: grid;
      grid-template-columns: 34px 1fr;
      gap: 12px;
      align-items: start;
      padding: 13px;
      border: 1px solid var(--borde-oscuro);
      border-radius: 14px;
      background: rgba(255,255,255,.06);
    }

    .contact-item__icon {
      width: 34px;
      height: 34px;
      border-radius: 11px;
      background: rgba(255,255,255,.09);
      display: grid;
      place-items: center;
      color: #fff;
      font-size: 14px;
      font-weight: 800;
    }

    .contact-item strong {
      display: block;
      color: #fff;
      font-size: 13.5px;
      margin-bottom: 2px;
    }

    .contact-item span {
      display: block;
      color: rgba(255,255,255,.72);
      font-size: 13.5px;
      line-height: 1.45;
    }

    .form-card {
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 22px;
      padding: 30px;
      box-shadow: var(--sombra);
    }

    .form-card__head {
      padding-bottom: 22px;
      margin-bottom: 24px;
      border-bottom: 1px solid var(--borde);
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 18px;
    }

    .form-card__head h2 {
      color: var(--azul-profundo);
      font-size: 24px;
      line-height: 1.24;
      letter-spacing: -.02em;
      font-weight: 500;
      margin-bottom: 6px;
    }

    .form-card__head p {
      color: var(--texto-suave);
      font-size: 14.5px;
    }

    .form-badge {
      flex: 0 0 auto;
      display: inline-flex;
      align-items: center;
      padding: 8px 11px;
      border-radius: 6px;
      background: var(--rojo-suave);
      color: var(--rojo);
      font-size: 12px;
      font-weight: 700;
    }

    .form {
      display: grid;
      gap: 18px;
    }

    .form-row {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .field {
      display: grid;
      gap: 7px;
    }

    label {
      color: #334155;
      font-size: 13.5px;
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
      border-radius: 13px;
      padding: 13px 14px;
      color: var(--texto);
      outline: none;
      transition: .18s ease;
      font-size: 14.5px;
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
      min-height: 112px;
    }

    .field-help {
      color: var(--texto-suave);
      font-size: 12.5px;
      line-height: 1.5;
    }

    .consent {
      display: grid;
      grid-template-columns: 18px 1fr;
      gap: 12px;
      align-items: start;
      padding: 15px;
      border: 1px solid var(--borde);
      border-radius: 14px;
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
      font-size: 13.5px;
      line-height: 1.6;
    }

    .form-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      padding-top: 4px;
    }

    .form-actions small {
      color: var(--texto-suave);
      font-size: 12.5px;
      max-width: 330px;
      line-height: 1.55;
    }

    .cta {
      background: var(--azul-profundo);
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .cta::before {
      content: "";
      position: absolute;
      right: 12%;
      top: -150px;
      width: 360px;
      height: 360px;
      border-radius: 50%;
      background: rgba(215,25,42,.18);
    }

    .cta__inner {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 32px;
      align-items: center;
    }

    .cta h2 {
      font-size: clamp(24px, 3vw, 34px);
      line-height: 1.22;
      letter-spacing: -.025em;
      font-weight: 500;
      margin-bottom: 10px;
    }

    .cta p {
      color: rgba(255,255,255,.72);
      font-size: 16px;
      max-width: 620px;
    }

    .footer {
      background: #020B36;
      color: #fff;
      padding: 68px 0 24px;
    }

    .footer__grid {
      display: grid;
      grid-template-columns: 1.15fr .7fr .7fr 1fr;
      gap: 38px;
      padding-bottom: 38px;
      border-bottom: 1px solid rgba(255,255,255,.12);
    }

    .footer__logo {
      height: 56px;
      width: auto;
      margin-bottom: 18px;
      background: #fff;
      border-radius: 8px;
      padding: 6px;
    }

    .footer__about p {
      color: rgba(255,255,255,.68);
      font-size: 14.5px;
      line-height: 1.75;
      max-width: 360px;
    }

    .footer h3 {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 16px;
    }

    .footer ul {
      list-style: none;
      display: grid;
      gap: 9px;
    }

    .footer li,
    .footer a {
      color: rgba(255,255,255,.68);
      font-size: 14px;
      line-height: 1.55;
      transition: .18s ease;
    }

    .footer a:hover {
      color: #fff;
    }

    .footer__bottom {
      display: flex;
      justify-content: space-between;
      gap: 18px;
      padding-top: 22px;
      color: rgba(255,255,255,.52);
      font-size: 13px;
    }

    .back-to-top {
      position: fixed;
      right: 22px;
      bottom: 22px;
      z-index: 40;
      width: 44px;
      height: 44px;
      border-radius: 14px;
      display: grid;
      place-items: center;
      background: var(--azul);
      color: #fff;
      box-shadow: 0 14px 28px rgba(7,29,122,.24);
      font-weight: 800;
      transition: .2s ease;
    }

    .back-to-top:hover {
      background: var(--azul-profundo);
      transform: translateY(-2px);
    }

    @media (max-width: 1040px) {
      .topbar__social {
        display: none;
      }

      .nav__actions {
        margin-left: auto;
      }

      .menu-toggle {
        display: flex;
      }

      .menu {
        position: fixed;
        top: 132px;
        left: 20px;
        right: 20px;
        display: none;
        flex-direction: column;
        align-items: stretch;
        background: #fff;
        border: 1px solid var(--borde);
        border-radius: 18px;
        box-shadow: var(--sombra);
        padding: 10px;
      }

      .menu.is-open {
        display: flex;
      }

      .menu a {
        border-radius: 12px;
        min-height: 44px;
      }

      .hero__inner,
      .project-layout,
      .admission-wrap {
        grid-template-columns: 1fr;
      }

      .hero__inner {
        min-height: auto;
      }

      .admission-aside {
        position: relative;
        top: auto;
      }

      .trust-grid,
      .process {
        grid-template-columns: repeat(2, 1fr);
      }

      .footer__grid {
        grid-template-columns: 1fr 1fr;
      }

      .cta__inner {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 720px) {
      .container,
      .topbar__inner,
      .nav,
      .hero__inner {
        width: min(100% - 28px, var(--contenedor));
      }

      .topbar {
        display: none;
      }

      .nav {
        height: 74px;
      }

      .logo img {
        height: 46px;
      }

      .nav__actions .btn {
        display: none;
      }

      .menu {
        top: 88px;
        left: 14px;
        right: 14px;
      }

      .hero__inner {
        padding: 42px 0 52px;
        gap: 34px;
      }

      .hero h1 {
        font-size: 34px;
        letter-spacing: -.03em;
      }

      .hero__text {
        font-size: 15.5px;
      }

      .hero__actions {
        align-items: stretch;
      }

      .hero__actions .btn {
        width: 100%;
      }

      .image-card__photo {
        min-height: 285px;
      }

      .image-card__bottom,
      .trust-grid,
      .process,
      .form-row,
      .footer__grid {
        grid-template-columns: 1fr;
      }

      .floating-card {
        position: static;
        width: auto;
        margin-top: 12px;
      }

      .section {
        padding: 54px 0;
      }

      .section-head {
        margin-bottom: 28px;
      }

      .section-head h2 {
        font-size: 26px;
      }

      .feature {
        grid-template-columns: 1fr;
      }

      .form-card {
        padding: 22px;
        border-radius: 18px;
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

      .footer__bottom {
        flex-direction: column;
      }

      .back-to-top {
        right: 14px;
        bottom: 14px;
      }
    }

    .public-alert {
      border-radius: 16px;
      padding: 16px 18px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      font-size: 14px;
    }

    .public-alert strong {
      display: block;
      margin-bottom: 6px;
    }

    .public-alert ul {
      margin: 8px 0 0 18px;
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

  </style>
</head>

<body id="inicio">

  <div class="topbar">
    <div class="topbar__inner">
      <div class="topbar__info">
        <span class="topbar__item">Tel. +56 57 2247188</span>
        <span class="topbar__item">contacto@academiaiquique.cl</span>
        <span class="topbar__item">Lunes a viernes · 08.00 a 16.00 hrs</span>
      </div>

      <div class="topbar__social">
        <a href="#" aria-label="Facebook">f</a>
        <a href="#" aria-label="Instagram">ig</a>
      </div>
    </div>
  </div>

  <header class="site-header">
    <nav class="nav" aria-label="Navegación principal">
      <a href="#inicio" class="logo" aria-label="Academia Iquique">
        <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique">
      </a>

      <ul class="menu" id="mainMenu">
        <li><a href="#inicio">Inicio</a></li>
        <li><a href="#formulario" class="is-active">Proceso de postulación 2027</a></li>
        <li><a href="#proceso">Proceso</a></li>
        <li><a href="#proyecto">Proyecto educativo</a></li>
        <li><a href="#contacto">Contacto</a></li>
      </ul>

      <div class="nav__actions">
        <a href="#formulario" class="btn btn--primary">Postular ahora</a>

        <button class="menu-toggle" id="menuToggle" type="button" aria-label="Abrir menú" aria-expanded="false">
          <span></span>
        </button>
      </div>
    </nav>
  </header>

  <main>

    <section class="hero">
      <div class="hero__inner">
        <div class="hero__content">
          <div class="label">Proceso de postulación 2027 abierto</div>

          <h1>
            Un proceso de postulación claro para ser parte de <span>Academia Iquique</span>
          </h1>

          <p class="hero__text">
            Te invitamos a conocer nuestro proyecto educativo y completar tu postulación para el año escolar 2027.
            Acompañamos a cada familia con información clara, orientación cercana y un proceso ordenado.
          </p>

          <div class="hero__actions">
            <a href="#formulario" class="btn btn--red">Completar postulación</a>
            <a href="#proceso" class="btn btn--ghost">Ver el proceso</a>
          </div>

          <div class="hero__note">
            <div class="hero__note-mark">AI</div>
            <div>
              <strong>Atención a familias postulantes.</strong>
              Completa el formulario y nuestro equipo de postulación se pondrá en contacto para orientar los siguientes pasos.
            </div>
          </div>
        </div>

        <div class="hero__visual">
          <div class="image-card">
            <div class="image-card__photo" role="img" aria-label="Estudiantes en ambiente educativo"></div>

            <div class="image-card__bottom">
              <div class="mini-stat">
                <strong>2005</strong>
                <span>Trayectoria institucional</span>
              </div>

              <div class="mini-stat">
                <strong>2027</strong>
                <span>Proceso de postulación</span>
              </div>

              <div class="mini-stat">
                <strong>Iquique</strong>
                <span>Comunidad educativa</span>
              </div>
            </div>
          </div>

          <div class="floating-card">
            <small>Proyecto educativo</small>
            <strong>Formación integral, cercanía y compromiso con cada estudiante.</strong>
          </div>
        </div>
      </div>
    </section>

    <div class="container">
      <div class="trust-grid">
        <div class="trust-item">
          <strong>Desde 2005</strong>
          <span>Experiencia educativa en Iquique</span>
        </div>

        <div class="trust-item">
          <strong>Kínder a 8º</strong>
          <span>Postulación a enseñanza básica</span>
        </div>

        <div class="trust-item">
          <strong>Familias</strong>
          <span>Acompañamiento durante el proceso</span>
        </div>

        <div class="trust-item">
          <strong>2027</strong>
          <span>Nuevo año escolar</span>
        </div>
      </div>
    </div>

    <section class="section section--form" id="formulario">
      <div class="container">
        <div class="admission-wrap">

          <aside class="admission-aside" id="contacto">
            <div class="eyebrow">Proceso de postulación 2027</div>
            <h2>¿Tienes dudas sobre el proceso?</h2>
            <p>
              Nuestro equipo de postulación puede orientarte y entregar información sobre disponibilidad,
              requisitos y etapas de postulación.
            </p>

            <div class="contact-list">
              <div class="contact-item">
                <div class="contact-item__icon">T</div>
                <div>
                  <strong>Teléfono</strong>
                  <span>+56 57 2247188</span>
                </div>
              </div>

              <div class="contact-item">
                <div class="contact-item__icon">@</div>
                <div>
                  <strong>Correo electrónico</strong>
                  <span>contacto@academiaiquique.cl</span>
                </div>
              </div>

              <div class="contact-item">
                <div class="contact-item__icon">D</div>
                <div>
                  <strong>Direcciones</strong>
                  <span>Bulnes 767, Iquique, Chile<br>Orella 738, Iquique, Chile</span>
                </div>
              </div>

              <div class="contact-item">
                <div class="contact-item__icon">H</div>
                <div>
                  <strong>Horario de atención</strong>
                  <span>Lunes a viernes · 08.00 a 16.00 hrs</span>
                </div>
              </div>
            </div>
          </aside>

          <?php require App::root('app/Views/admissions/_application_form.php'); ?>
        </div>
      </div>
    </section>

    <section class="section section--soft" id="proceso">
      <div class="container">
        <div class="section-head">
          <div class="eyebrow">Proceso de postulación</div>
          <h2>Postular es simple y ordenado</h2>
          <p>
            Hemos estructurado el proceso para que cada familia pueda entregar sus datos,
            recibir orientación y avanzar con claridad en su postulación.
          </p>
        </div>

        <div class="process">
          <article class="step-card">
            <div class="step-number">1</div>
            <div class="step-illustration"><img src="<?= App::asset('/images/documentos.png') ?>" alt="Formulario de postulación"></div>
            <h3>Completa el formulario</h3>
            <p>
              Ingresa los datos del apoderado, estudiante, curso al que postula y la información de contacto.
            </p>
          </article>

          <article class="step-card">
            <div class="step-number">2</div>
            <div class="step-illustration"><img src="<?= App::asset('/images/proceso.png') ?>" alt="Revisión de solicitud"></div>
            <h3>Revisión de solicitud</h3>
            <p>
              Nuestro equipo revisará la información enviada para continuar con las etapas correspondientes.
            </p>
          </article>

          <article class="step-card">
            <div class="step-number">3</div>
            <div class="step-illustration"><img src="<?= App::asset('/images/programa.png') ?>" alt="Contacto institucional"></div>
            <h3>Contacto institucional</h3>
            <p>
              Te contactaremos para entregar orientación, disponibilidad y los siguientes pasos del proceso.
            </p>
          </article>
        </div>
      </div>
    </section>

    <section class="section section--white" id="proyecto">
      <div class="container">
        <div class="project-layout">
          <div class="project-copy">
            <div class="eyebrow">Nuestro proyecto educativo</div>

            <div class="section-head">
              <h2>Un espacio educativo pensado para acompañar cada etapa del aprendizaje</h2>
            </div>

            <p>
              Academia Iquique promueve una experiencia educativa cercana, ordenada y formativa,
              donde cada estudiante puede desarrollar sus capacidades académicas, sociales y personales
              en un ambiente de respeto y comunidad.
            </p>

            <p>
              Nuestro proceso de postulación busca entregar información clara a las familias,
              facilitando una postulación responsable y acompañada por el equipo institucional.
            </p>
          </div>

          <div class="features">
            <article class="feature">
              <div class="feature__image"><img src="<?= App::asset('/images/proyecto.png') ?>" alt="Proyecto educativo"></div>
              <div>
                <h3>Formación académica</h3>
                <p>
                  Una propuesta educativa orientada al aprendizaje, la responsabilidad y el desarrollo progresivo de habilidades.
                </p>
              </div>
            </article>

            <article class="feature">
              <div class="feature__image"><img src="<?= App::asset('/images/programa.png') ?>" alt="Acompañamiento educativo"></div>
              <div>
                <h3>Acompañamiento cercano</h3>
                <p>
                  Un equipo atento a las necesidades de los estudiantes y sus familias durante las distintas etapas escolares.
                </p>
              </div>
            </article>

            <article class="feature">
              <div class="feature__image"><img src="<?= App::asset('/images/documentos.png') ?>" alt="Comunidad y valores"></div>
              <div>
                <h3>Comunidad y valores</h3>
                <p>
                  Un entorno que promueve el respeto, la participación y el sentido de pertenencia institucional.
                </p>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="section cta">
      <div class="container">
        <div class="cta__inner">
          <div>
            <h2>Comienza hoy el proceso de postulación</h2>
            <p>
              Completa el formulario y nuestro equipo se pondrá en contacto contigo para orientar los siguientes pasos.
            </p>
          </div>

          <a href="#formulario" class="btn btn--red">Postular ahora</a>
        </div>
      </div>
    </section>

  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer__grid">

        <div class="footer__about">
          <img src="<?= App::asset('/images/logo.png') ?>" alt="Academia Iquique" class="footer__logo">
          <p>
            Academia Iquique es una comunidad educativa comprometida con la formación integral,
            el acompañamiento de sus estudiantes y la participación activa de las familias.
          </p>
        </div>

        <div>
          <h3>Menú</h3>
          <ul>
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#formulario">Proceso de postulación 2027</a></li>
            <li><a href="#proceso">Proceso</a></li>
            <li><a href="#proyecto">Proyecto educativo</a></li>
          </ul>
        </div>

        <div>
          <h3>Accesos</h3>
          <ul>
            <li><a href="#formulario">Postular ahora</a></li>
            <li><a href="#proceso">Etapas del proceso</a></li>
            <li><a href="#proyecto">Conocer el proyecto</a></li>
            <li><a href="#contacto">Datos de contacto</a></li>
          </ul>
        </div>

        <div>
          <h3>Contacto</h3>
          <ul>
            <li>Bulnes 767, Iquique, Chile</li>
            <li>Orella 738, Iquique, Chile</li>
            <li>+56 57 2247188</li>
            <li>contacto@academiaiquique.cl</li>
            <li>Lunes a viernes · 08.00 a 16.00 hrs</li>
          </ul>
        </div>

      </div>

      <div class="footer__bottom">
        <span>© 2027 Academia Iquique. Todos los derechos reservados.</span>
        <span>Proceso de postulación 2027</span>
      </div>
    </div>
  </footer>

  <a class="back-to-top" href="#inicio" aria-label="Volver al inicio">↑</a>

  <script>
    const menuToggle = document.getElementById("menuToggle");
    const mainMenu = document.getElementById("mainMenu");

    menuToggle.addEventListener("click", () => {
      const isOpen = mainMenu.classList.toggle("is-open");
      menuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });

    document.querySelectorAll("#mainMenu a").forEach((link) => {
      link.addEventListener("click", () => {
        mainMenu.classList.remove("is-open");
        menuToggle.setAttribute("aria-expanded", "false");
      });
    });
  </script>

</body>
</html>