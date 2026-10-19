/* AASRA global behaviour */
window.AASRA = window.AASRA || {};
(function () {
  const A = window.AASRA;
  A.url = (p) => (A.base || "") + "/" + String(p).replace(/^\//, "");
  A.esc = (s) =>
    String(s ?? "").replace(
      /[&<>"']/g,
      (c) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[c],
    );

  document.addEventListener("DOMContentLoaded", () => {
    // ---------- every alert gets a close button ----------
    const ensureClose = (el) => {
      if (
        el.matches(".alert") &&
        !el.querySelector(".btn-close") &&
        !el.hasAttribute("data-permanent")
      ) {
        el.classList.add("alert-dismissible", "fade", "show");
        const b = document.createElement("button");
        b.type = "button";
        b.className = "btn-close";
        b.setAttribute("data-bs-dismiss", "alert");
        b.setAttribute("aria-label", "Close");
        el.appendChild(b);
      }
    };

    // ---------- alerts disappear by themselves after 3 seconds ----------
    // Applies to every .alert (server flash messages, alert_html(), A.notify() toasts and alerts added later by scripts).
    // The close button keeps working. An alert that is a permanent on-page note opts out with data-no-autodismiss.
    // Each alert is armed once (WeakSet), so re-adding or re-scanning the same element never creates a second timer.
    const AUTO_DISMISS_MS = 3000;
    const armed = new WeakSet();
    const reduceMotion = () =>
      window.matchMedia && matchMedia("(prefers-reduced-motion: reduce)").matches;
    const removeAlert = (el) => {
      if (window.bootstrap && bootstrap.Alert) {
        bootstrap.Alert.getOrCreateInstance(el).close(); // fires close.bs.alert / closed.bs.alert like a manual close
      } else {
        el.remove();
      }
    };
    const dismissSmoothly = (el) => {
      if (!el.isConnected) return; // already closed by the user or replaced by a newer message
      if (reduceMotion()) return removeAlert(el);
      // fade out, then collapse the height so the content below slides up instead of jumping
      el.style.maxHeight = el.offsetHeight + "px";
      el.style.overflow = "hidden";
      void el.offsetHeight; // commit the starting values before the transition begins
      el.style.transition =
        "opacity .3s ease, max-height .3s ease .1s, margin .3s ease .1s, padding .3s ease .1s, border-width .3s ease .1s";
      el.style.opacity = "0";
      el.style.maxHeight = "0";
      el.style.marginTop = el.style.marginBottom = "0";
      el.style.paddingTop = el.style.paddingBottom = "0";
      el.style.borderWidth = "0";
      setTimeout(() => el.isConnected && removeAlert(el), 450);
    };
    const armAlert = (el) => {
      if (
        !el.matches ||
        !el.matches(".alert") ||
        el.hasAttribute("data-no-autodismiss") ||
        el.hasAttribute("data-permanent") ||
        armed.has(el)
      )
        return;
      armed.add(el);
      setTimeout(() => dismissSmoothly(el), AUTO_DISMISS_MS);
    };
    const prepareAlert = (el) => {
      ensureClose(el);
      armAlert(el);
    };
    document.querySelectorAll(".alert").forEach(prepareAlert);
    new MutationObserver((m) =>
      m.forEach((x) =>
        x.addedNodes.forEach((n) => {
          if (n.nodeType === 1) {
            prepareAlert(n);
            n.querySelectorAll &&
              n.querySelectorAll(".alert").forEach(prepareAlert);
          }
        }),
      ),
    ).observe(document.body, { childList: true, subtree: true });

    // ---------- booking requirements: See More / See Less ----------
    // The full text is always in the page (clipped to 2 lines by CSS). The button is shown only when the text overflows.
    const initReq = (box) => {
      const t = box.querySelector(".req-text");
      const b = box.querySelector(".req-toggle");
      if (!t || !b) return;
      if (t.classList.contains("clamp-2")) b.hidden = !(t.scrollHeight > t.clientHeight + 1);
    };
    document.querySelectorAll(".req-box").forEach(initReq);
    window.addEventListener("resize", () => document.querySelectorAll(".req-box").forEach(initReq));
    document.addEventListener("click", (e) => {
      const b = e.target.closest && e.target.closest(".req-toggle");
      if (!b) return;
      const t = document.getElementById(b.getAttribute("aria-controls"));
      if (!t) return;
      const open = b.getAttribute("aria-expanded") === "true";
      t.classList.toggle("clamp-2", open);
      b.setAttribute("aria-expanded", open ? "false" : "true");
      b.textContent = open ? b.dataset.more : b.dataset.less;
    });

    // ---------- confirm, validation, password toggle ----------
    document.addEventListener(
      "click",
      (e) => {
        const c = e.target.closest("[data-confirm]:not([data-ajax-action])");
        if (c && !confirm(c.dataset.confirm)) {
          e.preventDefault();
          e.stopPropagation();
        }
      },
      true,
    );
    // Bootstrap 5 form validation: any <form class="needs-validation" novalidate>.
    // One delegated listener (works for forms added later). AJAX forms simply do not use the class.
    const matchTarget = (el) => (el.dataset.match ? document.querySelector(el.dataset.match) : null);
    const syncMatch = (el) =>
      el.setCustomValidity(matchTarget(el) && el.value !== matchTarget(el).value ? "Does not match." : "");
    document.addEventListener(
      "submit",
      (e) => {
        const f = e.target;
        if (!(f instanceof HTMLFormElement) || !f.classList.contains("needs-validation")) return;
        f.querySelectorAll("[data-match]").forEach(syncMatch); // e.g. confirm password
        if (!f.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
          f.querySelector(":invalid")?.focus();
        }
        f.classList.add("was-validated");
      },
      true,
    );
    document.addEventListener("input", (e) => {
      const el = e.target;
      if (!(el instanceof HTMLElement) || !el.closest(".needs-validation")) return;
      if (el.matches("[data-match]")) syncMatch(el);
      document.querySelectorAll('[data-match="#' + el.id + '"]').forEach(syncMatch);
      // a server-side error disappears once the user edits the field; Bootstrap's own state takes over
      if (el.classList.contains("is-invalid")) {
        el.classList.remove("is-invalid");
        const fb = (el.closest(".pw-wrap") || el).parentElement.querySelector(".invalid-feedback[data-default]");
        if (fb) fb.textContent = fb.dataset.default;
      }
    });
    document.querySelectorAll(".pw-toggle").forEach((b) =>
      b.addEventListener("click", () => {
        const i = b.parentElement.querySelector("input");
        const show = i.type === "password";
        i.type = show ? "text" : "password";
        b.querySelector("i").className =
          "bi " + (show ? "bi-eye-slash" : "bi-eye");
      }),
    );

    // ---------- favourites (user area) ----------
    document.querySelectorAll("[data-fav]").forEach((b) =>
      b.addEventListener("click", async (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (b.disabled) return;
        b.disabled = true; // one request per click; no double toggling
        const r = await A.post("user/api", {
          action: b.dataset.remove ? "unfavorite" : "favorite",
          provider_id: b.dataset.fav,
        });
        b.disabled = false;
        if (r.ok) {
          b.classList.toggle("on", r.on);
          b.querySelector("i").className =
            "bi " + (r.on ? "bi-heart-fill text-danger" : "bi-heart");
          if (b.dataset.remove && !r.on) {
            b.closest("[data-fav-card]")?.remove();
            // last favourite removed: reload so the normal empty state is shown
            if (!document.querySelector("[data-fav-card]")) location.reload();
          }
        }
        A.notify(r.ok ? "success" : "danger", r.message);
      }),
    );
  });

  // ---------- AJAX helpers ----------
  A.post = async function (path, data) {
    const fd = new FormData();
    Object.entries(data || {}).forEach(([k, v]) => fd.append(k, v));
    try {
      const res = await fetch(A.url(path), {
        method: "POST",
        body: fd,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-Token": A.csrf,
          Accept: "application/json",
        },
        credentials: "same-origin",
      });
      return await res.json();
    } catch (e) {
      return { ok: false, message: "Network error. Please try again." };
    }
  };
  // dismissible notification
  A.notify = function (type, msg) {
    if (!msg) return;
    let host = document.getElementById("alertHost");
    if (!host || !host.closest(".app-content")) {
      // public/user pages: floating notification
      host = document.getElementById("toastHost");
      if (!host) {
        host = document.createElement("div");
        host.id = "toastHost";
        host.className = "toast-host";
        document.body.appendChild(host);
      }
    }
    host.querySelectorAll(".alert").forEach((oldAlert) => oldAlert.remove());
    const icons = {
      success: "check-circle-fill",
      danger: "exclamation-triangle-fill",
      warning: "exclamation-circle-fill",
      info: "info-circle-fill",
    };
    const d = document.createElement("div");
    d.className =
      "alert alert-" +
      type +
      " alert-dismissible fade show d-flex align-items-start gap-2";
    d.setAttribute("role", "alert");
    d.innerHTML =
      '<i class="bi bi-' +
      (icons[type] || icons.info) +
      ' mt-1"></i><div class="flex-grow-1"></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    d.children[1].textContent = msg;
    host.prepend(d);
    if (host.closest(".app-content"))
      window.scrollTo({ top: 0, behavior: "smooth" });
  };
})();
