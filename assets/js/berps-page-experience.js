(function () {
  'use strict';
  if (window.BerpsLoading) return;

  var root = document.documentElement;
  var progress;
  var status;
  var message;
  var sequence = 0;
  var active = 0;
  var observedNavigation = false;
  var timers = [];
  var scriptPath = document.currentScript ? new URL(document.currentScript.src, document.baseURI).pathname : '/';
  var sidebarKey = 'berps:sidebar-collapsed:' + scriptPath.replace(/assets\/js\/[^/]+$/, '');
  var hasNavigation = window.navigation && typeof window.navigation.addEventListener === 'function';

  // Storage may be disabled in private browsing. Navigation must still work.
  try {
    if (sessionStorage.getItem(sidebarKey) === '1') root.setAttribute('data-berps-sidebar-collapsed', '');
  } catch (ignore) {}

  function mount() {
    if (progress || !document.body) return;
    progress = document.createElement('div');
    progress.className = 'berps-page-progress';
    progress.hidden = true;
    progress.setAttribute('aria-hidden', 'true');
    status = document.createElement('div');
    status.className = 'berps-page-loading';
    status.hidden = true;
    status.innerHTML = '<span class="berps-page-loading__spinner" aria-hidden="true"></span>' +
      '<span role="status" aria-live="polite" aria-atomic="true"></span>' +
      '<button type="button" class="berps-page-loading__dismiss" aria-label="Dismiss loading indicator">&times;</button>';
    message = status.querySelector('[role="status"]');
    status.querySelector('button').addEventListener('click', function () { finish(); });
    document.body.appendChild(progress);
    document.body.appendChild(status);
  }

  function finish(token) {
    if (token && token !== active) return;
    timers.forEach(window.clearTimeout);
    timers = [];
    active = 0;
    root.removeAttribute('data-berps-loading');
    if (progress) progress.hidden = true;
    if (status) {
      status.hidden = true;
      message.textContent = '';
    }
  }

  function begin(label, immediate) {
    finish();
    var token = active = ++sequence;
    timers.push(window.setTimeout(function () {
      mount();
      root.setAttribute('data-berps-loading', '');
      if (progress) progress.hidden = false;
    }, immediate ? 80 : 220));
    timers.push(window.setTimeout(function () {
      mount();
      if (status) {
        status.hidden = false;
        message.textContent = label || 'Loading page…';
      }
    }, 650));
    timers.push(window.setTimeout(function () {
      if (message) message.textContent = 'Still loading…';
    }, 12000));
    // Non-document responses (e.g. an unmarked download) may never fire pageshow.
    // This is only a visual watchdog; it never cancels or repeats a request.
    timers.push(window.setTimeout(function () { finish(token); }, 60000));
    return token;
  }

  function eligibleURL(value) {
    var url;
    try { url = new URL(value, document.baseURI); } catch (ignore) { return false; }
    if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) return false;
    // Downloads never replace the current document. HTML print previews do.
    return !/(?:download|export|attachment|pdf)|\.(?:csv|xlsx?|zip|png|jpe?g|gif|docx?)(?:$|[?#])/i.test(url.pathname + url.search);
  }

  function optedOut(element) {
    return element && element.closest && element.closest('[data-berps-loading="off"]');
  }

  function sameWindow(target) {
    return !target || target.toLowerCase() === '_self';
  }

  function fallbackNavigation(event, value, label) {
    // Let all page-specific handlers, including confirmation dialogs, run first.
    timers.push(window.setTimeout(function () {
      if (!event.defaultPrevented && eligibleURL(value)) begin(label, true);
    }, 0));
  }

  if (hasNavigation) {
    // Observe real navigation (including reloads, JS redirects and form.submit()).
    // Never intercept it, fetch HTML, replay scripts, or resubmit a form.
    window.navigation.addEventListener('navigate', function (event) {
      observedNavigation = true;
      window.setTimeout(function () { observedNavigation = false; }, 0);
      if (event.defaultPrevented || event.hashChange || event.downloadRequest != null ||
          (event.destination.sameDocument && event.navigationType !== 'reload') ||
          optedOut(event.sourceElement) || !eligibleURL(event.destination.url)) return;
      var token = begin(event.navigationType === 'reload' ? 'Refreshing page…' : 'Loading page…', true);
      event.signal.addEventListener('abort', function () { finish(token); }, { once: true });
      window.setTimeout(function () { if (event.defaultPrevented) finish(token); }, 0);
    });
    window.navigation.addEventListener('navigateerror', function () { finish(); });
    window.navigation.addEventListener('navigatesuccess', function () { finish(); });
    // Browser-toolbar reloads can bypass the Navigation API. Observe them without
    // canceling the unload or requesting a leave-page prompt. Older browsers keep
    // the click/form fallback below and their normal Back/Forward caching behavior.
    window.addEventListener('beforeunload', function (event) {
      if (observedNavigation) return;
      var token = begin('Refreshing page…', true);
      window.setTimeout(function () {
        if (event.defaultPrevented || event.returnValue) finish(token);
      }, 0);
    });
  } else {
    document.addEventListener('click', function (event) {
      if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      var link = event.target.closest && event.target.closest('a[href]');
      if (!link || optedOut(link) || link.hasAttribute('download') ||
          !sameWindow(link.getAttribute('target') || (document.querySelector('base[target]') || {}).target)) return;
      var href = link.getAttribute('href');
      if (!href || href.charAt(0) === '#') return;
      var url = new URL(link.href);
      if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
      fallbackNavigation(event, link.href, 'Loading page…');
    });
    document.addEventListener('submit', function (event) {
      var form = event.target;
      var button = event.submitter;
      if (optedOut(form) || optedOut(button) || form.method === 'dialog') return;
      var target = (button && button.getAttribute('formtarget')) || form.target ||
        (document.querySelector('base[target]') || {}).target;
      if (!sameWindow(target)) return;
      var action = (button && button.getAttribute('formaction')) || form.action || location.href;
      fallbackNavigation(event, action, 'Loading page…');
    });
  }

  // Do not retain the spinner in a view-transition snapshot or in Back/Forward cache.
  window.addEventListener('pageswap', function () { finish(); });
  window.addEventListener('pagehide', function () { finish(); });
  window.addEventListener('pageshow', function (event) { finish(event.persisted ? undefined : initial); });
  window.addEventListener('beforeprint', function () { finish(); });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') finish();
  });

  function ready() {
    if (root.hasAttribute('data-berps-sidebar-collapsed')) document.body.classList.add('berps-sidebar-collapsed');
    // Finish after page-ready handlers have initialized tables, charts and widgets.
    window.setTimeout(function () { finish(initial); }, 0);
  }

  // Persist only the desktop preference; a mobile drawer always starts closed.
  document.addEventListener('click', function (event) {
    if (!event.target.closest || !event.target.closest('[data-berps-sidebar-toggle]') || window.innerWidth < 992) return;
    window.setTimeout(function () {
      var collapsed = document.body.classList.contains('berps-sidebar-collapsed');
      root.toggleAttribute('data-berps-sidebar-collapsed', collapsed);
      try { sessionStorage.setItem(sidebarKey, collapsed ? '1' : '0'); } catch (ignore) {}
    }, 0);
  });

  // Explicit opt-in for an asynchronous foreground operation. Polling stays silent.
  window.BerpsLoading = {
    start: function (label) {
      var token = begin(label);
      return function () { finish(token); };
    }
  };

  var initial = begin('Loading page…');
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready, { once: true });
  else ready();
})();
