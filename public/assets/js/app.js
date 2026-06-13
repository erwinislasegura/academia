document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const toggle = document.getElementById('sidebarToggle');
  const close = () => { sidebar?.classList.remove('open'); backdrop?.classList.remove('open'); };
  toggle?.addEventListener('click', () => { sidebar?.classList.toggle('open'); backdrop?.classList.toggle('open'); });
  backdrop?.addEventListener('click', close);
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
      if (!confirm(form.dataset.confirm || '¿Confirmar acción?')) event.preventDefault();
    });
  });
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    button.textContent = isPassword ? 'Ocultar' : 'Ver';
  });
});

// Admissions message modal.
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('admission-message-modal');
  if (!modal) return;

  const title = document.getElementById('admission-message-title');
  const meta = document.getElementById('admission-message-meta');
  const body = document.getElementById('admission-message-body');

  document.querySelectorAll('.message-modal-trigger').forEach((button) => {
    button.addEventListener('click', () => {
      if (title) title.textContent = button.dataset.applicant || 'Detalle del mensaje';
      if (meta) meta.textContent = button.dataset.student ? `Estudiante: ${button.dataset.student}` : 'Postulación sin estudiante informado';
      if (body) body.textContent = button.dataset.message || 'Sin mensaje adicional';

      if (typeof modal.showModal === 'function') {
        modal.showModal();
      } else {
        alert(button.dataset.message || 'Sin mensaje adicional');
      }
    });
  });

  modal.querySelectorAll('[data-message-modal-close]').forEach((button) => {
    button.addEventListener('click', () => modal.close());
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) modal.close();
  });
});
