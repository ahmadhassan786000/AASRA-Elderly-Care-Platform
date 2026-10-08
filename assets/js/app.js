/* AASRA global behaviour */
window.AASRA = window.AASRA || {};
(function () {
  const A = window.AASRA;
  A.url = p => (A.base || '') + '/' + String(p).replace(/^\//, '');
  A.esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  // ---------- theme toggle (persisted) ----------
  function setTheme(t) {
    document.documentElement.setAttribute('data-bs-theme', t);
    try { localStorage.setItem('aasra-theme', t); } catch (e) {}
    document.querySelectorAll('#themeToggle i').forEach(i => i.className = 'bi ' + (t === 'dark' ? 'bi-sun' : 'bi-moon-stars'));
  }
  document.addEventListener('DOMContentLoaded', () => {
    setTheme(document.documentElement.getAttribute('data-bs-theme') || 'light');
    document.querySelectorAll('#themeToggle').forEach(b => b.addEventListener('click', () =>
      setTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark')));

    // ---------- every alert gets a close button ----------
    const ensureClose = el => {
      if (el.matches('.alert') && !el.querySelector('.btn-close') && !el.hasAttribute('data-permanent')) {
        el.classList.add('alert-dismissible', 'fade', 'show');
        const b = document.createElement('button');
        b.type = 'button'; b.className = 'btn-close'; b.setAttribute('data-bs-dismiss', 'alert'); b.setAttribute('aria-label', 'Close');
        el.appendChild(b);
      }
    };
    document.querySelectorAll('.alert').forEach(ensureClose);
    new MutationObserver(m => m.forEach(x => x.addedNodes.forEach(n => { if (n.nodeType === 1) { ensureClose(n); n.querySelectorAll && n.querySelectorAll('.alert').forEach(ensureClose); } })))
      .observe(document.body, { childList: true, subtree: true });

    // ---------- confirm, validation, password toggle ----------
    document.addEventListener('click', e => {
      const c = e.target.closest('[data-confirm]:not([data-ajax-action])');
      if (c && !confirm(c.dataset.confirm)) { e.preventDefault(); e.stopPropagation(); }
    }, true);
    document.querySelectorAll('form[data-validate]').forEach(f => f.addEventListener('submit', e => {
      if (!f.checkValidity()) { e.preventDefault(); e.stopPropagation(); } f.classList.add('was-validated');
    }));
    document.querySelectorAll('.pw-toggle').forEach(b => b.addEventListener('click', () => {
      const i = b.parentElement.querySelector('input'); const show = i.type === 'password';
      i.type = show ? 'text' : 'password'; b.querySelector('i').className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye');
    }));

    // ---------- favourites (user area) ----------
    document.querySelectorAll('[data-fav]').forEach(b => b.addEventListener('click', async e => {
      e.preventDefault(); e.stopPropagation();
      const r = await A.post('user/api', { action: 'favorite', provider_id: b.dataset.fav });
      if (r.ok) { b.classList.toggle('on', r.on); b.querySelector('i').className = 'bi ' + (r.on ? 'bi-heart-fill text-danger' : 'bi-heart'); if (b.dataset.remove && !r.on) b.closest('[data-fav-card]')?.remove(); }
      A.notify(r.ok ? 'success' : 'danger', r.message);
    }));
  });

  // ---------- AJAX helpers ----------
  A.post = async function (path, data) {
    const fd = new FormData(); Object.entries(data || {}).forEach(([k, v]) => fd.append(k, v));
    try {
      const res = await fetch(A.url(path), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': A.csrf, 'Accept': 'application/json' }, credentials: 'same-origin' });
      return await res.json();
    } catch (e) { return { ok: false, message: 'Network error. Please try again.' }; }
  };
  // dismissible notification
  A.notify = function (type, msg) {
    if (!msg) return;
    let host = document.getElementById('alertHost');
    if (!host || !host.closest('.app-content')) {   // public/user pages: floating notification
      host = document.getElementById('toastHost');
      if (!host) { host = document.createElement('div'); host.id = 'toastHost'; host.className = 'toast-host'; document.body.appendChild(host); }
    }
    const icons = { success: 'check-circle-fill', danger: 'exclamation-triangle-fill', warning: 'exclamation-circle-fill', info: 'info-circle-fill' };
    const d = document.createElement('div');
    d.className = 'alert alert-' + type + ' alert-dismissible fade show d-flex align-items-start gap-2'; d.setAttribute('role', 'alert');
    d.innerHTML = '<i class="bi bi-' + (icons[type] || icons.info) + ' mt-1"></i><div class="flex-grow-1"></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    d.children[1].textContent = msg;
    host.prepend(d);
    if (host.closest('.app-content')) window.scrollTo({ top: 0, behavior: 'smooth' });
  };
})();
