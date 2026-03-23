/**
 * system-alerts.js
 * - Positions #system-message-container just below #t3-header (dynamic height)
 * - Bounce-in on appear (handled by CSS animation)
 * - Auto-dismisses each alert after AUTO_DISMISS ms with a fade-out
 * - Intercepts close-button clicks to play fade-out before hiding
 */
(function () {
  'use strict';

  var AUTO_DISMISS = 6000; // ms before auto-fade-out

  function init() {
    var container = document.getElementById('system-message-container');
    if (!container) return;

    /* ── Position below header ──────────────────────────────── */
    function updateTop() {
      var header = document.getElementById('t3-header');
      if (header) {
        container.style.top = header.offsetHeight + 'px';
      }
    }
    updateTop();
    window.addEventListener('resize', updateTop);

    /* ── Hide an alert with fade-out animation ──────────────── */
    function hideAlert(el, delay) {
      if (el._vrcHiding) return;
      setTimeout(function () {
        if (el._vrcHiding) return;
        el._vrcHiding = true;
        el.classList.add('vrc-hiding');
        el.addEventListener('animationend', function () {
          el.style.display = 'none';
        }, { once: true });
        // Safety fallback in case animationend never fires
        setTimeout(function () { el.style.display = 'none'; }, 600);
      }, delay || 0);
    }

    /* ── Attach behaviour to a single alert element ─────────── */
    function processAlert(el) {
      if (el.dataset.vrcInit) return;
      el.dataset.vrcInit = '1';

      // Auto-dismiss
      hideAlert(el, AUTO_DISMISS);

      // Intercept close button
      var btn = el.querySelector(
        '.joomla-alert--close, .close, [data-dismiss="alert"]'
      );
      if (btn) {
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopImmediatePropagation();
          hideAlert(el, 0);
        }, true /* capture phase — runs before the component's own handler */);
      }
    }

    /* ── Process all alerts currently in the container ──────── */
    function processAll() {
      container.querySelectorAll('joomla-alert, .alert').forEach(processAlert);
    }
    processAll();

    /* ── Watch for alerts added after page load ─────────────── */
    var obs = new MutationObserver(processAll);
    obs.observe(container, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
