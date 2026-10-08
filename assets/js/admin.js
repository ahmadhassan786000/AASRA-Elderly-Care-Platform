/* AASRA admin: dropdown fix, AJAX actions, live status updates */
document.addEventListener('DOMContentLoaded', () => {
  const A = window.AASRA;
  // Dropdowns inside scrolling tables must not be clipped.
  document.querySelectorAll('.table-responsive [data-bs-toggle="dropdown"]').forEach(t =>
    new bootstrap.Dropdown(t, { popperConfig: c => ({ ...c, strategy: 'fixed' }) }));

  const rejectModalEl = document.getElementById('rejectModal');
  const rejectModal = rejectModalEl ? new bootstrap.Modal(rejectModalEl) : null;
  let pendingReject = null;

  async function run(el, extra) {
    const payload = Object.assign({ action: el.dataset.ajaxAction, id: el.dataset.id, value: el.dataset.value || '' }, extra || {});
    el.classList.add('disabled');
    const r = await A.post('admin/api', payload);
    el.classList.remove('disabled');
    A.notify(r.ok ? 'success' : 'danger', r.message);
    if (!r.ok) return;
    if (r.removed) {
      const row = el.closest('[data-row]');
      if (row) { row.style.transition = 'opacity .25s'; row.style.opacity = 0; setTimeout(() => row.remove(), 260); }
      else if (r.redirect) location.href = A.url(r.redirect);
      return;
    }
    if (r.key) {
      document.querySelectorAll('[data-live-key="' + r.key + '"]').forEach(n => n.innerHTML = r.badge);
      document.querySelectorAll('[data-live-pill="' + r.key + '"]').forEach(n => { n.textContent = r.status; n.dataset.status = r.status; });
      document.querySelectorAll('[data-live-when="' + r.key + '"]').forEach(n => n.classList.toggle('d-none', n.dataset.when !== r.status));
    }
    (r.extra || []).forEach(x => document.querySelectorAll('[data-live-key="' + x.key + '"]').forEach(n => n.innerHTML = x.badge));
  }

  document.addEventListener('click', e => {
    const el = e.target.closest('[data-ajax-action]');
    if (!el) return;
    e.preventDefault();
    if (el.dataset.ajaxAction === 'verification' && el.dataset.value === 'Rejected' && rejectModal) {
      pendingReject = el; document.getElementById('rejectNote').value = ''; rejectModal.show(); return;
    }
    if (el.dataset.confirm && !confirm(el.dataset.confirm)) return;
    run(el);
  });
  document.getElementById('rejectConfirm')?.addEventListener('click', () => {
    if (pendingReject) { const el = pendingReject; pendingReject = null; rejectModal.hide(); run(el, { note: document.getElementById('rejectNote').value }); }
  });
});
