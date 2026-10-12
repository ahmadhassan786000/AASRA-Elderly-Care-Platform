/* AASRA homepage motion: scroll reveals, in-page navigation and scroll-spy.
   Progressive enhancement only: the pages work without this file (native #anchors + CSS smooth scroll). */
(function () {
  'use strict';
  window.AASRA_MOTION = true; // tells the head.php fail-safe that this script loaded
  const root = document.documentElement;
  const reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  const norm = p => p.replace(/\/+$/, '');

  function ready(fn) { document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn(); }

  ready(function () {
    // ---------- scroll reveal ----------
    const items = document.querySelectorAll('.reveal');
    if (!reduce && 'IntersectionObserver' in window && root.classList.contains('js-motion')) {
      const io = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add('is-visible'); obs.unobserve(en.target); }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -8% 0px' });
      items.forEach(function (el) { io.observe(el); });
    } else {
      items.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // ---------- in-page links: smooth scroll, close the mobile menu, keep focus sensible ----------
    document.addEventListener('click', function (e) {
      const a = e.target.closest('a[href*="#"]');
      if (!a || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      let u; try { u = new URL(a.href, location.href); } catch (_) { return; }
      if (!u.hash || u.origin !== location.origin || norm(u.pathname) !== norm(location.pathname) || u.search !== location.search) return;
      const id = decodeURIComponent(u.hash.slice(1));
      const target = document.getElementById(id);
      if (!target) return;
      e.preventDefault();

      const nav = document.getElementById('siteNav');
      if (nav && nav.classList.contains('show') && window.bootstrap) window.bootstrap.Collapse.getOrCreateInstance(nav).hide();

      const behavior = reduce ? 'auto' : 'smooth';
      if (id === 'home') window.scrollTo({ top: 0, behavior: behavior });
      else target.scrollIntoView({ behavior: behavior, block: 'start' });

      if (history.pushState && location.hash !== u.hash) history.pushState(null, '', u.hash);
      if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });
    });

    // ---------- scroll-spy for the public header (Home / Services / Guides) ----------
    const links = Array.prototype.slice.call(document.querySelectorAll('.site-header a[data-spy]'));
    const sections = Array.prototype.slice.call(document.querySelectorAll('[data-spy-section]'));
    if (links.length && sections.length) {
      let queued = false;
      const update = function () {
        queued = false;
        const probe = window.innerHeight * 0.35;
        let current = sections[0];
        sections.forEach(function (s) { if (s.getBoundingClientRect().top <= probe) current = s; });
        const key = current.getAttribute('data-spy-section');
        links.forEach(function (l) { l.classList.toggle('active', l.getAttribute('data-spy') === key); });
      };
      const onScroll = function () { if (!queued) { queued = true; requestAnimationFrame(update); } };
      window.addEventListener('scroll', onScroll, { passive: true });
      window.addEventListener('resize', onScroll);
      update();
    }
  });
})();
