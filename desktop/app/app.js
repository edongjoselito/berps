/* BERPS Desk — native app shell.
 * Talks to the portal's /api/mobile REST API through the main-process bridge
 * (window.berps). The bearer token never reaches the renderer.
 */
(function () {
  'use strict';

  var B = window.berps;
  var view = document.getElementById('view');
  var appEl = document.getElementById('app');
  var bootEl = document.getElementById('boot');

  var state = {
    user: null,
    server: '',
    route: 'home',
    dash: null,
    tasks: { status: 'open', scope: 'all', q: '', list: [], sel: null, detail: null, projects: [], staff: [], prios: [] },
    notifs: null,
    clock: null,
  };

  /* ---------------------------------------------------------------- utils */

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function pad2(n) { return String(n).padStart(2, '0'); }

  // Local calendar date (YYYY-MM-DD). toISOString() is UTC and is off by a day
  // in the morning for UTC+ timezones (e.g. Manila before 8am).
  function ymd(d) {
    d = d || new Date();
    return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
  }

  // Notes written in the web portal are stored as rich-text HTML.
  function htmlToText(html) {
    var s = String(html || '');
    if (!/<[a-z][\s\S]*>/i.test(s)) return s;
    var doc = new DOMParser().parseFromString(
      s.replace(/<br\s*\/?>/gi, '\n').replace(/<\/(p|div|li|h[1-6]|tr)>/gi, '$&\n'), 'text/html');
    return (doc.body.textContent || '').replace(/\u00a0/g, ' ').replace(/\n{3,}/g, '\n\n').trim();
  }

  function textToHtml(text) {
    return String(text || '').split(/\n/).map(function (line) {
      return '<p>' + (line.trim() ? esc(line) : '<br>') + '</p>';
    }).join('');
  }

  function errorBox(host, msg, retry) {
    if (!host || !host.isConnected) return;
    host.innerHTML = '<div class="empty empty-err"><div class="empty-title">' + esc(msg || 'Could not load this.') + '</div>' +
      (retry ? '<button class="btn btn-ghost" data-retry style="margin-top:10px">Try again</button>' : '') + '</div>';
    var b = host.querySelector('[data-retry]');
    if (b) b.addEventListener('click', retry);
  }

  function busy(btn, on, label) {
    if (!btn) return;
    if (on) {
      btn.dataset.label = btn.innerHTML;
      btn.disabled = true;
      btn.classList.add('is-busy');
      if (label) btn.textContent = label;
    } else {
      btn.disabled = false;
      btn.classList.remove('is-busy');
      if (btn.dataset.label != null) { btn.innerHTML = btn.dataset.label; delete btn.dataset.label; }
    }
  }

  function fmtDate(d) {
    if (!d || d === '0000-00-00') return '—';
    var t = new Date(String(d).replace(' ', 'T'));
    return isNaN(t) ? d : t.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function toast(msg, kind) {
    var host = document.getElementById('toasts');
    var el = document.createElement('div');
    el.className = 'toast toast--' + (kind || 'info');
    el.textContent = msg;
    host.appendChild(el);
    setTimeout(function () { el.classList.add('is-in'); }, 10);
    setTimeout(function () { el.classList.remove('is-in'); setTimeout(function () { el.remove(); }, 250); }, 3400);
  }

  function confirmBox(title, msg, okLabel, danger) {
    return new Promise(function (resolve) {
      var host = document.getElementById('modal');
      host.innerHTML =
        '<div class="modal-backdrop"></div>' +
        '<div class="modal-card">' +
          '<h3 class="modal-title">' + esc(title) + '</h3>' +
          '<p class="modal-msg">' + esc(msg) + '</p>' +
          '<div class="modal-actions">' +
            '<button class="btn btn-ghost" data-x="0">Cancel</button>' +
            '<button class="btn ' + (danger ? 'btn-danger-soft' : 'btn-primary') + '" data-x="1">' + esc(okLabel || 'Confirm') + '</button>' +
          '</div>' +
        '</div>';
      host.hidden = false;
      var done = function (yes) {
        document.removeEventListener('keydown', onKey, true);
        host.hidden = true; host.innerHTML = '';
        resolve(yes);
      };
      var onKey = function (e) {
        if (e.key === 'Escape') { e.preventDefault(); done(false); }
        // Destructive dialogs never confirm on Enter — explicit click only.
        else if (e.key === 'Enter' && !danger) { e.preventDefault(); done(true); }
      };
      document.addEventListener('keydown', onKey, true);
      host.querySelector('.modal-backdrop').addEventListener('click', function () { done(false); });
      host.querySelectorAll('[data-x]').forEach(function (b) {
        b.addEventListener('click', function () { done(b.getAttribute('data-x') === '1'); });
      });
      var ok = host.querySelector('[data-x="1"]');
      var cancel = host.querySelector('[data-x="0"]');
      ((danger ? cancel : ok) || ok || cancel).focus();
    });
  }

  // Centered dialog with a form. onSubmit(form) returns a promise resolving
  // true (close) / false (keep open), or nothing when validation failed.
  function formModal(o) {
    var host = document.getElementById('modal');
    host.innerHTML =
      '<div class="modal-backdrop"></div>' +
      '<form class="modal-card modal-form" novalidate autocomplete="off">' +
        '<h3 class="modal-title">' + esc(o.title) + '</h3>' +
        (o.sub ? '<p class="modal-sub">' + esc(o.sub) + '</p>' : '') +
        '<div class="modal-body">' + o.body + '</div>' +
        '<div class="modal-actions">' +
          '<button type="button" class="btn btn-ghost" data-x="0">Cancel</button>' +
          '<button type="submit" class="btn ' + (o.okClass || 'btn-primary') + '" data-x="1">' + esc(o.okLabel || 'Save') + '</button>' +
        '</div>' +
      '</form>';
    host.hidden = false;
    var form = host.querySelector('form');
    var ok = form.querySelector('[data-x="1"]');
    var close = function () {
      document.removeEventListener('keydown', onKey, true);
      host.hidden = true; host.innerHTML = '';
    };
    var onKey = function (e) {
      if (e.key === 'Escape') { e.preventDefault(); close(); }
      else if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) { e.preventDefault(); form.requestSubmit(); }
    };
    document.addEventListener('keydown', onKey, true);
    host.querySelector('.modal-backdrop').addEventListener('click', close);
    form.querySelector('[data-x="0"]').addEventListener('click', close);
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (ok.disabled) return;
      var p = o.onSubmit(form);
      if (!p || !p.then) return;
      busy(ok, true);
      p.then(function (done) { if (done) close(); else busy(ok, false); });
    });
    if (o.onOpen) o.onOpen(form);
    var first = form.querySelector('.picker-q, textarea, input');
    if (first) first.focus();
    return close;
  }

  function initialsOf(name) {
    return String(name || '?').trim().split(/[\s,]+/).filter(Boolean).slice(0, 2).map(function (w) { return w[0].toUpperCase(); }).join('');
  }

  function pickerHtml(users, label) {
    return '<div class="f-field"><label class="f-label">' + esc(label || 'Person') + '</label>' +
      '<div class="picker">' +
        '<div class="search">' + I.search + '<input class="picker-q" placeholder="Search ' + users.length + ' people…"></div>' +
        '<div class="picker-list">' + users.map(function (u) {
          return '<button type="button" class="picker-item" data-uid="' + u.user_id + '" data-name="' + esc(String(u.name).toLowerCase()) + '">' +
            '<span class="picker-av">' + esc(initialsOf(u.name)) + '</span><span class="picker-name">' + esc(u.name) + '</span>' +
            '<span class="picker-tick">' + I.check + '</span></button>';
        }).join('') + '</div>' +
      '</div></div>';
  }

  function bindPicker(form) {
    var root = form.querySelector('.picker');
    if (!root) return;
    var items = [].slice.call(root.querySelectorAll('.picker-item'));
    var pick = function (it) {
      items.forEach(function (x) { x.classList.toggle('is-sel', x === it); });
      root.dataset.uid = it ? it.getAttribute('data-uid') : '';
    };
    items.forEach(function (it) {
      it.addEventListener('click', function () { pick(it); });
      it.addEventListener('dblclick', function () { pick(it); form.requestSubmit(); });
    });
    var q = root.querySelector('.picker-q');
    q.addEventListener('input', function () {
      var v = q.value.trim().toLowerCase();
      items.forEach(function (it) { it.hidden = v && it.getAttribute('data-name').indexOf(v) === -1; });
    });
    q.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' || e.metaKey || e.ctrlKey) return;
      e.preventDefault();
      var vis = items.filter(function (it) { return !it.hidden; });
      if (vis.length) pick(vis[0]);
    });
  }

  function autoGrow(ta) {
    var fit = function () { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight + 2, 160) + 'px'; };
    ta.addEventListener('input', fit);
    fit();
  }

  /* ------------------------------------------------------------------ api */

  function api(method, path, body) {
    return B.api(method, path, body).then(function (res) {
      if (res.status === 401 || res.status === 403) {
        showLogin('Your session expired. Please sign in again.');
        return null;
      }
      if (res.status === 0) {
        updateConnUI(false);
        toast(res.json && res.json.message ? res.json.message : 'Cannot reach the server.', 'err');
        return null;
      }
      updateConnUI(true);
      sbSync();
      if (!res.json) {
        toast('The server had a problem (' + res.status + '). Please try again.', 'err');
        return null;
      }
      return res.json;
    });
  }

  /* ------------------------------------------------------------ svg icons */

  var I = {
    check: '<svg viewBox="0 0 24 24"><path d="m4.5 12.5 5 5 10-11"/></svg>',
    search: '<svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6"/><path d="m15.5 15.5 5 5"/></svg>',
    inbox: '<svg viewBox="0 0 24 24"><path d="M4 13.5V19a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 20 19v-5.5L17 5H7Z"/><path d="M4 13.5h5a3 3 0 0 0 6 0h5"/></svg>',
    logout: '<svg viewBox="0 0 24 24"><path d="M14 4H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7"/><path d="m17 8 4 4-4 4M9 12h12"/></svg>',
    refresh: '<svg viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.34-5.66M20 4v4h-4"/></svg>',
    box: '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12.5 2.5 2.5 5-5.5"/></svg>',
    lifebuoy: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="3.5"/><path d="m6 6 4 4m4 4 4 4m0-12-4 4m-4 4-4 4"/></svg>',
    bell: '<svg viewBox="0 0 24 24"><path d="M6 9.5a6 6 0 0 1 12 0c0 4 1.5 5.5 2 6.5H4c.5-1 2-2.5 2-6.5Z"/><path d="M9.8 19a2.3 2.3 0 0 0 4.4 0"/></svg>',
    pointer: '<svg viewBox="0 0 24 24"><path d="m8 3 12 10-7 1.5L16 20l-3 1-3-5.5L5.5 19 8 3Z"/></svg>',
    back: '<svg viewBox="0 0 24 24"><path d="m14 6-6 6 6 6"/></svg>',
    star: '<svg viewBox="0 0 24 24"><path d="m12 3 2.7 5.7 6.3.8-4.6 4.3 1.2 6.2-5.6-3-5.6 3 1.2-6.2L3 9.5l6.3-.8Z"/></svg>',
    trash: '<svg viewBox="0 0 24 24"><path d="M5 7h14M9 7V5h6v2m-8 0 1 13h8l1-13"/></svg>',
    send: '<svg viewBox="0 0 24 24"><path d="m4 11.5 16-7-7 16-2.5-6.5Z"/></svg>',
    alarm: '<svg viewBox="0 0 24 24"><circle cx="12" cy="13" r="7.5"/><path d="M12 9.5V13l2.5 2M5.5 3.5 3 6m18 0-2.5-2.5"/></svg>',
    cal: '<svg viewBox="0 0 24 24"><rect x="4" y="5.5" width="16" height="15" rx="2.5"/><path d="M4 10.5h16M8.5 3.5v4M15.5 3.5v4"/></svg>',
    edit: '<svg viewBox="0 0 24 24"><path d="M4 20h4l11-11a2.8 2.8 0 0 0-4-4L4 16v4z"/><path d="m13.5 6.5 4 4"/></svg>',
    goal: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/></svg>',
    userPlus: '<svg viewBox="0 0 24 24"><circle cx="9.5" cy="8" r="3.5"/><path d="M3 20a6.5 6.5 0 0 1 13 0M19 8v6M16 11h6"/></svg>',
    at: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3.8"/><path d="M15.8 12v1.6a2.6 2.6 0 0 0 5.2 0V12a9 9 0 1 0-3.6 7.2"/></svg>',
    forward: '<svg viewBox="0 0 24 24"><path d="m14 5 7 7-7 7M21 12H9a6 6 0 0 0-6 6v1"/></svg>',
    trophy: '<svg viewBox="0 0 24 24"><path d="M7 4h10v4a5 5 0 0 1-10 0V4Z"/><path d="M7 5H4.5v1.5A3.5 3.5 0 0 0 8 10M17 5h2.5v1.5A3.5 3.5 0 0 1 16 10M12 13v3M8.5 20h7l-1-4h-5l-1 4Z"/></svg>',
  };

  /* ------------------------------------------------------------- statusbar */

  function sbInit() {
    var ver = document.getElementById('sbVer');
    if (ver) ver.textContent = 'v' + (B.version || '');
    var su = document.getElementById('sbUnread');
    if (su) su.addEventListener('click', function () { go('#/alerts'); });
    updateConnUI(navigator.onLine !== false);
    window.addEventListener('offline', function () { updateConnUI(false); });
    window.addEventListener('online', function () {
      updateConnUI(true);
      pollNotifs();
    });
  }

  function updateConnUI(online) {
    var d = document.getElementById('sbConnDot');
    var t = document.getElementById('sbConnTxt');
    if (!d || !t) return;
    d.className = 'sb-dot ' + (online ? 'is-on' : 'is-off');
    t.textContent = online
      ? (state.server ? String(state.server).replace(/^https?:\/\//, '').replace(/\/+$/, '') : 'Connected')
      : 'Offline';
  }

  function sbSync() {
    var el = document.getElementById('sbSync');
    if (el) el.textContent = 'Synced ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
  }

  function sbAtt(att) {
    var el = document.getElementById('sbAtt');
    if (!el) return;
    var open = !!(att && att.can_time_out);
    var done = att && !att.can_time_in && !att.can_time_out;
    el.hidden = false;
    var dot = document.getElementById('sbAttDot');
    var txt = document.getElementById('sbAttTxt');
    if (dot) dot.className = 'sb-att-dot ' + (open ? 'is-in' : done ? 'is-out' : '');
    if (txt) txt.textContent = open ? 'Timed in' : done ? 'Timed out' : 'Not timed in';
    pushTray(att);
  }

  function sbUnread(n) {
    var el = document.getElementById('sbUnread');
    if (!el) return;
    el.hidden = !n;
    el.textContent = (n > 99 ? '99+' : n) + ' unread';
  }

  function pushTray(att) {
    if (!B.trayStatus) return;
    B.trayStatus({
      phase: att && att.can_time_out ? 'in' : att && att.can_time_in ? 'off' : att ? 'out' : 'unknown',
      canIn: !!(att && att.can_time_in),
      canOut: !!(att && att.can_time_out),
    });
  }

  /* ---------------------------------------------------- menu + key commands */

  function clickEl(id) { var el = document.getElementById(id); if (el) el.click(); }
  function focusEl(id) { var el = document.getElementById(id); if (el) { el.focus(); if (el.select) el.select(); } }

  var ROUTE_TITLES = {
    home: 'Today', tasks: 'Tasks', support: 'Support', calendar: 'Calendar',
    notes: 'Notes', reminders: 'Reminders', attendance: 'Attendance',
    goals: 'Goals', ranking: 'Ranking', alerts: 'Notifications', settings: 'Settings',
  };

  var NEW_ACTIONS = {
    tasks: function () { clickEl('taskNew'); },
    notes: function () { clickEl('noteNew'); },
    reminders: function () { focusEl('rTitle'); },
    calendar: function () { clickEl('calNew'); },
    support: function () { focusEl('supSearch'); },
  };

  var REFRESH_ACTIONS = {
    home: function () { loadHome(true); },
    tasks: loadTasks,
    support: loadSupList,
    notes: loadNotes,
    reminders: loadReminders,
    calendar: loadCalendar,
    attendance: vAttendance,
    goals: vGoals,
    ranking: vRanking,
    alerts: function () { loadNotifs(); refreshBadge(); },
    settings: verifySession,
  };

  function menuAction(a) {
    if (!state.user) return;
    switch (a) {
      case 'new':
        if (NEW_ACTIONS[state.route]) NEW_ACTIONS[state.route]();
        else { go('#/tasks'); setTimeout(function () { clickEl('taskNew'); }, 80); }
        break;
      case 'refresh': (REFRESH_ACTIONS[state.route] || function () {})(); break;
      case 'search': { var s = view.querySelector('.search input'); if (s) { s.focus(); if (s.select) s.select(); } break; }
      case 'toggle-sidebar': document.body.classList.toggle('side-collapsed'); break;
      case 'mark-read':
        api('POST', '/staff/notifications/mark-seen').then(function () {
          refreshBadge();
          if (state.route === 'alerts') loadNotifs();
        });
        break;
      case 'time-in': punch('time-in'); break;
      case 'time-out': punch('time-out'); break;
      case 'palette': openPalette(); break;
    }
  }

  /* ----------------------------------------------------- keyboard list nav */

  function listRows(selector) { return [].slice.call(view.querySelectorAll(selector)); }

  function moveIntoView(el) { if (el && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' }); }

  function selectSup(id) {
    sup.sel = String(id);
    document.querySelectorAll('#supRows .trow').forEach(function (r) {
      r.classList.toggle('is-sel', r.getAttribute('data-sup') === String(id));
    });
    renderSupDetail(id);
  }

  function openNoteById(id) {
    nts.sel = String(id);
    document.querySelectorAll('#noteRows .nrow2').forEach(function (r) {
      r.classList.toggle('is-sel', r.getAttribute('data-note') === String(id));
    });
    renderNoteEditor(nts.list.find(function (n) { return String(n.id) === String(id); }));
  }

  function listAdapter() {
    if (state.route === 'tasks') {
      return {
        rows: listRows('#taskRows .trow[data-task]'),
        idOf: function (r) { return r.getAttribute('data-task'); },
        sel: function () { return state.tasks.sel; },
        open: selectTask,
      };
    }
    if (state.route === 'support') {
      return {
        rows: listRows('#supRows .trow[data-sup]'),
        idOf: function (r) { return r.getAttribute('data-sup'); },
        sel: function () { return sup.sel; },
        open: selectSup,
      };
    }
    if (state.route === 'notes') {
      return {
        rows: listRows('#noteRows .nrow2[data-note]'),
        idOf: function (r) { return r.getAttribute('data-note'); },
        sel: function () { return nts.sel; },
        open: openNoteById,
      };
    }
    return null;
  }

  function listMove(dir) {
    var a = listAdapter();
    if (!a || !a.rows.length) return;
    var idx = a.rows.findIndex(function (r) { return String(a.idOf(r)) === String(a.sel()); });
    idx = idx === -1 ? (dir > 0 ? 0 : a.rows.length - 1)
                     : Math.max(0, Math.min(a.rows.length - 1, idx + dir));
    a.open(a.idOf(a.rows[idx]));
    moveIntoView(a.rows[idx]);
  }

  function toggleSelectedRow() {
    if (state.route !== 'tasks') return;
    var a = listAdapter();
    if (!a) return;
    var row = a.rows.find(function (r) { return String(a.idOf(r)) === String(state.tasks.sel); }) || a.rows[0];
    if (!row) return;
    var chk = row.querySelector('[data-check]');
    if (chk) chk.click(); // closeTask / reopenTask through the existing handler
  }

  function openSelectedRow() {
    var a = listAdapter();
    if (!a || !a.rows.length) return;
    if (!a.sel()) { a.open(a.idOf(a.rows[0])); moveIntoView(a.rows[0]); }
  }

  function deleteSelectedRow() {
    if (state.route === 'tasks' && state.tasks.sel) {
      var t = state.tasks.list.find(function (x) { return String(x.id) === String(state.tasks.sel); }) || state.tasks.detail;
      confirmDeleteTask(t);
    } else if (state.route === 'notes' && nts.sel) {
      var n = nts.list.find(function (x) { return String(x.id) === String(nts.sel); });
      if (n) confirmDeleteNote(n);
    }
  }

  function backOut() {
    if (state.route === 'tasks' && state.tasks.sel) {
      state.tasks.sel = null; renderDetailEmpty(); renderTaskList();
    } else if (state.route === 'support' && sup.sel) {
      sup.sel = null; renderSupEmpty(); renderSupList();
    } else if (state.route === 'notes' && nts.sel) {
      nts.sel = null; renderNoteEmpty(); renderNoteList();
    }
  }

  document.addEventListener('keydown', function (e) {
    var inField = e.target && e.target.closest && e.target.closest('input, textarea, select, [contenteditable="true"]');
    var modalOpen = !document.getElementById('modal').hidden;

    if ((e.metaKey || e.ctrlKey) && !e.shiftKey && !e.altKey && !modalOpen) {
      var k = e.key.toLowerCase();
      if (k === 'n') { e.preventDefault(); menuAction('new'); return; }
      if (k === 'f') { e.preventDefault(); menuAction('search'); return; }
      if (k === 'r') { e.preventDefault(); menuAction('refresh'); return; }
      if (k === 'k') { e.preventDefault(); menuAction('palette'); return; }
    }
    if (palState.open) {
      if (e.key === 'Escape') { e.preventDefault(); closePalette(); }
      return; // the palette input owns the rest while it's up
    }
    if (inField || modalOpen || e.defaultPrevented || e.metaKey || e.ctrlKey || e.altKey) return;

    // Space/Enter keep their native click semantics on focused controls.
    var interactive = e.target && e.target.closest &&
      e.target.closest('button, a, select, [role="button"], [role="checkbox"], label');

    switch (e.key) {
      case 'j': case 'J': case 'ArrowDown': e.preventDefault(); listMove(1); break;
      case 'k': case 'K': case 'ArrowUp': e.preventDefault(); listMove(-1); break;
      case ' ': {
        // On a focused task row, Space toggles its checkbox; elsewhere it
        // toggles the selected row — but never steals the click from a
        // focused control.
        var row = e.target && e.target.closest && e.target.closest('.trow[data-task]');
        if (row && state.route === 'tasks') {
          e.preventDefault();
          var chk = row.querySelector('[data-check]');
          if (chk) chk.click();
        } else if (!interactive) {
          e.preventDefault(); toggleSelectedRow();
        }
        break;
      }
      case 'Enter': if (!interactive) openSelectedRow(); break;
      case 'Delete': case 'Backspace': deleteSelectedRow(); break;
      case 'Escape': backOut(); break;
    }
  });

  if (B.onMenuAction) B.onMenuAction(function (a) { menuAction(a); });

  /* ----------------------------------------------------- edit context menu */

  if (B.contextMenu) {
    document.addEventListener('contextmenu', function (e) {
      var el = e.target && e.target.closest && e.target.closest('input, textarea');
      if (!el) return;
      e.preventDefault();
      B.contextMenu([
        { role: 'undo' }, { role: 'redo' }, { sep: true },
        { role: 'cut' }, { role: 'copy' }, { role: 'paste' }, { sep: true },
        { role: 'selectAll' },
      ]);
    });
  }

  /* -------------------------------------------------------- command palette */

  var palState = { open: false, items: [], sel: 0, q: '' };
  var palData = { warmed: false, tasks: null, sup: null, notes: null };

  var PAL_COMMANDS = [
    { t: 'New task', icon: 'box', run: function () { go('#/tasks'); setTimeout(function () { clickEl('taskNew'); }, 80); } },
    { t: 'New note', icon: 'edit', run: function () { go('#/notes'); setTimeout(function () { clickEl('noteNew'); }, 80); } },
    { t: 'New reminder', icon: 'alarm', run: function () { go('#/reminders'); setTimeout(function () { focusEl('rTitle'); }, 80); } },
    { t: 'Search tickets', icon: 'lifebuoy', run: function () { go('#/support'); setTimeout(function () { focusEl('supSearch'); }, 80); } },
    { t: 'Time in', icon: 'check', run: function () { punch('time-in'); } },
    { t: 'Time out', icon: 'check', run: function () { punch('time-out'); } },
    { t: 'Open web portal', icon: 'forward', run: function () { B.portal.open(); } },
    { t: 'Mark all notifications read', icon: 'bell', run: function () { menuAction('mark-read'); } },
    { t: 'Refresh this view', icon: 'refresh', hint: '⌘R', run: function () { menuAction('refresh'); } },
    { t: 'Toggle sidebar', icon: 'back', run: function () { menuAction('toggle-sidebar'); } },
    { t: 'Appearance: System', icon: 'at', run: function () { B.setTheme('system'); } },
    { t: 'Appearance: Light', icon: 'at', run: function () { B.setTheme('light'); } },
    { t: 'Appearance: Dark', icon: 'at', run: function () { B.setTheme('dark'); } },
    { t: 'Sign out…', icon: 'logout', run: function () { go('#/settings'); } },
  ];
  Object.keys(ROUTE_TITLES).forEach(function (r) {
    PAL_COMMANDS.push({ t: 'Go to ' + ROUTE_TITLES[r], icon: 'pointer', run: function () { go('#/' + r); } });
  });

  // Every query token must appear in the text; earlier matches rank higher.
  function palScore(q, text) {
    text = String(text || '').toLowerCase();
    var score = 0;
    var toks = String(q || '').trim().toLowerCase().split(/\s+/);
    for (var i = 0; i < toks.length; i++) {
      var idx = text.indexOf(toks[i]);
      if (idx === -1) return -1;
      score += idx === 0 ? 0 : idx + 4;
    }
    return score;
  }

  function palItems() {
    var q = palState.q.trim();
    var out = [];
    PAL_COMMANDS.forEach(function (c) {
      var s = q ? palScore(q, c.t) : 0;
      if (s >= 0) out.push({ score: s, icon: c.icon, title: c.t, meta: 'Command', hint: c.hint || '', run: c.run });
    });
    if (q) {
      var add = function (list, kind, icon, mkTitle, mkMeta) {
        (list || []).forEach(function (x) {
          var s = palScore(q, mkTitle(x) + ' ' + mkMeta(x));
          if (s >= 0) out.push({ score: s + 6, icon: icon, title: mkTitle(x), meta: mkMeta(x), run: function () { openTarget({ type: kind, id: x.id }); } });
        });
      };
      add(palData.tasks || state.tasks.list, 'task', 'box',
        function (t) { return t.title || ''; },
        function (t) { return 'Task · ' + (t.project_name || 'No project'); });
      add(palData.sup || sup.list, 'ticket', 'lifebuoy',
        function (i) { return (i.ticket_number ? i.ticket_number + ' ' : '') + (i.title || ''); },
        function (i) { return 'Ticket' + (i.customer_name ? ' · ' + i.customer_name : ''); });
      add(palData.notes || nts.list, 'note', 'edit',
        function (n) { return n.title || 'Untitled'; },
        function () { return 'Note'; });
      out.sort(function (a, b) { return a.score - b.score; });
    }
    return out.slice(0, 14);
  }

  function renderPalette() {
    if (!palState.open) return;
    var host = document.getElementById('palList');
    if (!host) return;
    palState.items = palItems();
    if (palState.sel >= palState.items.length) palState.sel = Math.max(0, palState.items.length - 1);
    host.innerHTML = palState.items.length ? palState.items.map(function (it, i) {
      return '<button class="pal-item' + (i === palState.sel ? ' is-sel' : '') + '" data-pi="' + i + '">' +
        '<span class="pal-ico">' + (I[it.icon] || I.pointer) + '</span>' +
        '<span class="pal-t">' + esc(it.title) + '</span>' +
        '<span class="pal-m">' + esc(it.meta || '') + '</span>' +
        (it.hint ? '<kbd class="pal-hint">' + esc(it.hint) + '</kbd>' : '') +
        '</button>';
    }).join('') : '<div class="pal-empty">No matching commands or items.</div>';
    host.querySelectorAll('[data-pi]').forEach(function (row) {
      row.addEventListener('mouseenter', function () { palState.sel = +row.getAttribute('data-pi'); markPalSel(); });
      row.addEventListener('click', function () { runPalette(+row.getAttribute('data-pi')); });
    });
  }

  function markPalSel() {
    var host = document.getElementById('palList');
    if (!host) return;
    host.querySelectorAll('[data-pi]').forEach(function (row) {
      var sel = +row.getAttribute('data-pi') === palState.sel;
      row.classList.toggle('is-sel', sel);
      if (sel && row.scrollIntoView) row.scrollIntoView({ block: 'nearest' });
    });
  }

  function openPalette() {
    if (!state.user || palState.open) return;
    var modal = document.getElementById('modal');
    if (modal && !modal.hidden) return; // modal dialogs own the screen
    palState = { open: true, items: [], sel: 0, q: '' };
    document.getElementById('palette').hidden = false;
    var inp = document.getElementById('palInput');
    inp.value = '';
    renderPalette();
    setTimeout(function () { inp.focus(); }, 0);
    warmPalette();
  }

  function closePalette() {
    if (!palState.open) return;
    palState.open = false;
    document.getElementById('palette').hidden = true;
  }

  function runPalette(i) {
    var it = palState.items[i];
    closePalette();
    if (it && it.run) it.run();
  }

  // The lists populate lazily — palette search gets useful after first use
  // without needing the user to visit each view first.
  function warmPalette() {
    if (palData.warmed) return;
    palData.warmed = true;
    api('GET', '/staff/tasks?status=all&scope=all').then(function (r) { if (r && r.tasks) { palData.tasks = r.tasks; renderPalette(); } });
    api('GET', '/staff/support/issues?scope=all').then(function (r) { if (r && r.issues) { palData.sup = r.issues; renderPalette(); } });
    api('GET', '/staff/notes').then(function (r) { if (r && r.notes) { palData.notes = r.notes; renderPalette(); } });
  }

  var palInput = document.getElementById('palInput');
  palInput.addEventListener('input', function () { palState.q = this.value; palState.sel = 0; renderPalette(); });
  palInput.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown') { e.preventDefault(); palState.sel = Math.min(palState.items.length - 1, palState.sel + 1); markPalSel(); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); palState.sel = Math.max(0, palState.sel - 1); markPalSel(); }
    else if (e.key === 'Enter') { e.preventDefault(); runPalette(palState.sel); }
    else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); closePalette(); }
  });
  document.getElementById('palette').addEventListener('click', function (e) { if (e.target === this) closePalette(); });

  /* ------------------------------------------------------ open-a-thing deep links */

  // Notification clicks, berps:// links and palette picks all land here.
  function openTarget(t) {
    if (!t || !state.user) return;
    if (t.type === 'task' && t.id != null) {
      state.tasks.status = 'all'; state.tasks.scope = 'all';
      if (state.route !== 'tasks') go('#/tasks');
      selectTask(String(t.id));
    } else if ((t.type === 'ticket' || t.type === 'support') && t.id != null) {
      sup.scope = 'all';
      if (state.route !== 'support') go('#/support');
      selectSup(String(t.id));
    } else if (t.type === 'note' && t.id != null) {
      var id = String(t.id);
      if (state.route !== 'notes') go('#/notes');
      var found = nts.list.some(function (n) { return String(n.id) === id; });
      if (found) openNoteById(id);
      else loadNotes().then(function () { openNoteById(id); });
    } else if (t.type === 'route' && t.id) {
      go('#/' + t.id);
    } else {
      go('#/alerts');
    }
  }

  if (B.onOpenTarget) B.onOpenTarget(function (t) { openTarget(t); });

  /* ------------------------------------------------------------------ app */

  function initials() {
    var n = (state.user && (state.user.full_name || state.user.username)) || '?';
    return n.trim().split(/\s+/).slice(0, 2).map(function (w) { return w[0].toUpperCase(); }).join('');
  }

  function showApp() {
    document.body.classList.remove('is-logged-out');
    var u = state.user || {};
    document.getElementById('sideAvatarText').textContent = initials();
    document.getElementById('sideName').textContent = u.full_name || u.username || 'Staff';
    document.getElementById('sideRole').textContent = u.position || 'Staff';
    var av = u.avatar_url;
    var avEl = document.getElementById('sideAvatar');
    avEl.style.backgroundImage = av ? 'url(' + av + ')' : '';
    appEl.hidden = false;
    bootEl.classList.add('is-done');
    if (!location.hash || location.hash === '#/') history.replaceState(null, '', '#/home');
    route(location.hash);
  }

  function showLogin(message) {
    document.body.classList.add('is-logged-out');
    document.title = 'Sign in — BERPS Desk';
    state.user = null;
    var sbA = document.getElementById('sbAtt');
    if (sbA) sbA.hidden = true;
    sbUnread(0);
    pushTray(null);
    // Drop cached per-user data so a re-login never flashes the previous user.
    state.dash = null;
    state.notifs = null;
    state.tasks.list = [];
    state.tasks.sel = null;
    sup.list = [];
    sup.sel = null;
    nts.list = [];
    nts.sel = null;
    notifSeen.primed = false;
    notifSeen.ids.clear();
    palData.warmed = false;
    palData.tasks = palData.sup = palData.notes = null;
    appEl.hidden = false;
    bootEl.classList.add('is-done');
    view.innerHTML =
      '<div class="login-wrap">' +
        '<div class="login-card">' +
          '<img class="login-mark" src="logo.png" alt="BERPS">' +
          '<h1 class="login-title">Welcome to BERPS Desk</h1>' +
          '<p class="login-sub">Sign in with your staff account.</p>' +
          '<div class="login-error" id="loginError"></div>' +
          '<form id="loginForm" autocomplete="off">' +
            '<div class="field"><label for="liUser">Username</label><input id="liUser" required autocomplete="username"></div>' +
            '<div class="field"><label for="liPass">Password</label><input id="liPass" type="password" required autocomplete="current-password"></div>' +
            '<button class="btn btn-primary login-btn" id="liBtn" type="submit">Sign in</button>' +
          '</form>' +
          (state.prefs && state.prefs.touchIdSupported && state.prefs.touchId && B.unlock
            ? '<button class="btn btn-ghost login-btn" type="button" id="liTouch">Use Touch ID</button>'
            : '') +
          '<div class="login-server"><span class="dot"></span><span>' + esc(state.server || '') + '</span></div>' +
        '</div>' +
      '</div>';

    if (message) showLoginError(message);

    document.getElementById('loginForm').addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = document.getElementById('liBtn');
      btn.disabled = true; btn.textContent = 'Signing in…';
      B.auth.login(document.getElementById('liUser').value.trim(), document.getElementById('liPass').value)
        .then(function (res) {
          btn.disabled = false; btn.textContent = 'Sign in';
          if (res && res.ok) {
            state.user = res.user;
            showApp();
            pollNotifs();
          } else {
            showLoginError((res && res.message) || 'Login failed.');
          }
        });
    });

    var liTouch = document.getElementById('liTouch');
    if (liTouch) liTouch.addEventListener('click', function () {
      var b = this;
      b.disabled = true; b.textContent = 'Waiting for Touch ID…';
      B.unlock().then(function (u) {
        b.disabled = false; b.textContent = 'Use Touch ID';
        if (u && u.ok) enterApp();
        else showLoginError((u && u.message) || 'Touch ID was not accepted.');
      });
    });
  }

  function showLoginError(msg) {
    var el = document.getElementById('loginError');
    if (el) { el.textContent = msg; el.classList.add('is-visible'); }
  }

  /* ---------------------------------------------------------------- router */

  var routes = { home: vHome, tasks: vTasks, support: vSupport, calendar: vCalendar, notes: vNotes, reminders: vReminders, attendance: vAttendance, goals: vGoals, ranking: vRanking, alerts: vAlerts, settings: vSettings };

  function routeName(hash) {
    return String(hash || '').replace(/^#\/?/, '').split('?')[0];
  }

  function hashParam(key) {
    return new URLSearchParams(location.hash.split('?')[1] || '').get(key);
  }

  function go(hash) {
    if (location.hash === hash) route(routeName(hash));
    else location.hash = hash;
  }

  function route(name) {
    name = routeName(name);
    if (!routes[name]) name = 'home';
    if (!state.user) return;
    state.route = name;
    document.title = (ROUTE_TITLES[name] || 'BERPS Desk') + ' — BERPS Desk';
    document.querySelectorAll('.side-item').forEach(function (b) {
      b.classList.toggle('is-active', b.getAttribute('data-nav') === name);
    });
    if (state.clock) { clearInterval(state.clock); state.clock = null; }
    routes[name]();
    view.scrollTop = 0;
  }

  window.addEventListener('hashchange', function () { route(location.hash); });
  document.querySelectorAll('[data-nav]').forEach(function (b) {
    var nav = function () { go('#/' + b.getAttribute('data-nav')); };
    b.addEventListener('click', nav);
    if (b.tagName !== 'BUTTON') {
      b.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); nav(); } });
    }
  });
  if (B.onMenuGoto) B.onMenuGoto(function (hash) { go(hash); });

  /* ----------------------------------------------------------- shared bits */

  function viewHead(title, sub, actions) {
    return '<div class="view-head"><div class="view-head-main">' +
      '<h1 class="view-title">' + esc(title) + '</h1>' +
      (sub ? '<p class="view-sub">' + esc(sub) + '</p>' : '') +
      '</div>' + (actions || '') + '</div>';
  }

  function dueChip(due) {
    if (!due || due === '0000-00-00') return '<span class="chip chip-gray">No date</span>';
    var lbl = fmtDate(due);
    var today = new Date(); today.setHours(0, 0, 0, 0);
    var d = new Date(due + 'T00:00:00');
    var diff = Math.round((d - today) / 86400000);
    if (diff < 0) return '<span class="chip chip-red">' + Math.abs(diff) + 'd overdue</span>';
    if (diff === 0) return '<span class="chip chip-orange">Today</span>';
    if (diff <= 7) return '<span class="chip chip-blue">' + lbl + '</span>';
    return '<span class="chip chip-gray">' + lbl + '</span>';
  }

  function dueMetaChip(t) {
    var cls = { overdue: 'chip-red', due_today: 'chip-orange', upcoming: 'chip-blue', undated: 'chip-gray' }[t.due_meta_type] || 'chip-gray';
    return '<span class="chip ' + cls + '">' + esc(t.due_meta_label || fmtDate(t.due_date)) + '</span>';
  }

  function prioCls(t) { return t.priority_value === '1' ? 'chip-red' : t.priority_value === '2' ? 'chip-orange' : 'chip-teal'; }

  function taskRow(t, opts) {
    opts = opts || {};
    var closed = t.status === 'closed';
    return '<button class="trow' + (closed ? ' is-closed' : '') + (opts.sel ? ' is-sel' : '') + '" data-task="' + t.id + '">' +
      '<span class="tcheck' + (closed ? ' is-done' : '') + '" data-check="' + t.id + '" role="checkbox" aria-checked="' + closed + '">' + I.check + '</span>' +
      '<span class="trow-main">' +
        '<span class="trow-title">' + esc(t.title) + '</span>' +
        '<span class="trow-meta">' +
          (closed ? '<span class="chip chip-gray">Done</span>' : t.due_meta_label ? dueMetaChip(t) : (t.due_date && t.due_date !== '0000-00-00' ? dueChip(t.due_date) : '')) +
          (t.is_forwarded_pending ? '<span class="chip chip-teal">Fwd</span>' : '') +
          '<span>' + esc(t.project_name || t.subtitle || 'No project') + '</span>' +
          (t.assigned_person_name ? '<span class="dotsep">·</span><span>' + esc(t.assigned_person_name) + '</span>' : '') + '</span>' +
      '</span>' +
      '<span class="prio-dot prio-' + esc(t.priority_value || t.priority || '2') + '" title="' + esc(t.priority_label || ({ 1: 'High', 2: 'Medium', 3: 'Low' })[t.priority_value || t.priority] || '') + ' priority"></span>' +
    '</button>';
  }

  function bindTaskRows(host) {
    host.querySelectorAll('.trow[data-task]').forEach(function (row) {
      row.addEventListener('click', function (e) {
        if (e.target.closest('.tcheck')) return;
        selectTask(row.getAttribute('data-task'));
      });
      row.addEventListener('contextmenu', function (e) {
        if (!B.contextMenu) return;
        e.preventDefault();
        var id = row.getAttribute('data-task');
        var closed = row.classList.contains('is-closed');
        var t = state.tasks.list.find(function (x) { return String(x.id) === String(id); }) || state.tasks.detail;
        var items = [
          { id: 'open', label: 'Open' },
          { id: closed ? 'reopen' : 'close', label: closed ? 'Reopen task' : 'Close task' },
        ];
        if ((state.tasks.staff || []).length && !closed) items.push({ id: 'forward', label: 'Forward…' });
        items.push({ sep: true }, { id: 'delete', label: 'Delete task…' },
          { sep: true }, { id: 'copy', label: 'Copy task ID', copyText: '#' + id });
        B.contextMenu(items).then(function (a) {
          if (a === 'open') selectTask(id);
          else if (a === 'close') closeTask(id);
          else if (a === 'reopen') reopenTask(id);
          else if (a === 'forward' && t) forwardTaskModal(t);
          else if (a === 'delete' && t) confirmDeleteTask(t);
        });
      });
    });
    host.querySelectorAll('[data-check]').forEach(function (b) {
      b.addEventListener('click', function (e) {
        e.stopPropagation();
        var id = b.getAttribute('data-check');
        if (b.classList.contains('is-done')) reopenTask(id); else closeTask(id);
      });
    });
  }

  function selectTask(id) {
    if (state.route === 'tasks') {
      state.tasks.sel = String(id);
      document.querySelectorAll('#taskRows .trow').forEach(function (r) {
        r.classList.toggle('is-sel', r.getAttribute('data-task') === String(id));
      });
      renderDetail(id);
    } else {
      state.tasks.sel = String(id);
      go('#/tasks');
    }
  }

  /* ----------------------------------------------------------------- today */

  function vHome() {
    var u = state.user || {};
    var dateLabel = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
    var h = new Date().getHours();
    var greet = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
    var first = (u.first_name || u.full_name || 'there').split(' ')[0];

    view.innerHTML =
      viewHead(greet + ', ' + first, dateLabel,
        '<button class="btn btn-ghost" id="homeRefresh">' + I.refresh + ' Refresh</button>') +
      '<div class="view-body">' +
        '<div class="panel"><div class="att-strip" id="attStrip">' +
          '<div class="skel" style="width:9px;height:9px;border-radius:50%"></div>' +
          '<div class="skel" style="flex:1;height:14px"></div>' +
          '<div class="skel" style="width:120px;height:26px"></div>' +
        '</div></div>' +
        '<div class="panel"><div class="panel-head">Needs attention<span class="sub" id="needsCount"></span></div>' +
          '<div class="panel-body flush row-list" id="needsList">' +
          '<div class="skel skel-row" style="margin:10px 14px"></div><div class="skel skel-row" style="margin:10px 14px"></div>' +
          '</div></div>' +
        '<div class="grid-2" style="margin-top:14px">' +
          '<div class="panel"><div class="panel-head">Overview</div><div class="panel-body flush stat-list" id="statList"><div class="skel skel-row" style="margin:10px 14px"></div></div></div>' +
          '<div class="panel"><div class="panel-head">Latest notifications</div><div class="panel-body flush" id="latestNotifs"><div class="skel skel-row" style="margin:10px 14px"></div></div></div>' +
        '</div>' +
      '</div>';

    document.getElementById('homeRefresh').addEventListener('click', function () { loadHome(true); });
    if (state.dash) { // paint last fetch instantly, then revalidate
      renderAttStrip(state.dash.attendance || {});
      renderNeeds(state.dash);
      renderStatList(state.dash);
    }
    loadHome();
  }

  function loadHome(manual) {
    var rb = document.getElementById('homeRefresh');
    if (manual) busy(rb, true);
    api('GET', '/staff/dashboard').then(function (d) {
      busy(rb, false);
      if (!d) {
        errorBox(document.getElementById('needsList'), 'Could not load your dashboard.', function () { loadHome(true); });
        return;
      }
      state.dash = d;
      renderAttStrip(d.attendance || {});
      renderNeeds(d);
      renderStatList(d);
    });
    api('GET', '/staff/notifications?limit=5').then(function (res) {
      if (!res) return;
      updateBadge(res.unseen_total || 0);
      var host = document.getElementById('latestNotifs');
      if (!host) return;
      var items = res.notifications || [];
      host.innerHTML = (items.length ? items.map(function (n, idx) {
        return '<button class="nrow' + (n.is_seen ? '' : ' is-unseen') + '" data-ni="' + idx + '">' +
          '<span class="nrow-dot"></span>' +
          '<span class="nrow-b"><span class="nrow-t">' + esc(n.title || 'Notification') + '</span>' +
          '<span class="nrow-m">' + esc(n.actor_name || n.source || '') + '</span></span>' +
          '<span class="nrow-time">' + esc(n.created_label || '') + '</span></button>';
      }).join('') : '<div class="empty"><div class="empty-title">No notifications</div></div>') +
        (items.length ? '<button class="panel-more" id="seeAllNotifs">See all notifications ›</button>' : '');
      bindNotifRows(host, items);
      var more = document.getElementById('seeAllNotifs');
      if (more) more.addEventListener('click', function () { go('#/alerts'); });
    });
  }

  function renderAttStrip(att) {
    var host = document.getElementById('attStrip');
    sbAtt(att);
    if (!host) return;
    var open = !!att.can_time_out;
    var done = !att.can_time_in && !att.can_time_out;
    host.innerHTML =
      '<span class="att-dot ' + (open ? 'open' : done ? 'done' : 'idle') + '"></span>' +
      '<div class="att-body">' +
        '<div class="att-status">' + (open ? 'Timed in — clock is running' : done ? 'Attendance complete for today' : 'Not timed in yet') + '</div>' +
        '<div class="att-note">' + esc(att.notice || att.status_label || '') + '</div>' +
      '</div>' +
      '<div class="att-actions">' +
        '<button class="btn btn-success" id="btnIn" ' + (!att.can_time_in ? 'disabled' : '') + '>Time in</button>' +
        '<button class="btn btn-ghost" id="btnOut" ' + (!att.can_time_out ? 'disabled' : '') + '>Time out</button>' +
      '</div>';
    var bin = document.getElementById('btnIn');
    var bout = document.getElementById('btnOut');
    if (bin) bin.addEventListener('click', function () { punch('time-in', bin); });
    if (bout) bout.addEventListener('click', function () { punch('time-out', bout); });
  }

  function punch(kind, btn) {
    busy(btn, true, kind === 'time-in' ? 'Timing in…' : 'Timing out…');
    api('POST', '/staff/attendance/' + kind).then(function (res) {
      busy(btn, false);
      if (!res) return;
      if (res.ok) {
        toast(res.message || 'Recorded.', 'ok');
        if (state.route === 'home') loadHome();
        else if (state.route === 'attendance') vAttendance();
        else api('GET', '/staff/dashboard').then(function (d) { if (d) sbAtt(d.attendance || {}); });
      } else {
        toast(res.message || 'Could not record attendance.', 'warn');
      }
    });
  }

  function renderNeeds(d) {
    var host = document.getElementById('needsList');
    if (!host) return;
    var rows = (d.ongoing_tasks || []);
    var count = document.getElementById('needsCount');
    if (count) count.textContent = rows.length ? rows.length + ' open' : '';
    host.innerHTML = rows.length ? rows.map(function (t) { return taskRow(t); }).join('')
      : '<div class="empty">' + I.inbox + '<div class="empty-title">All clear</div><div class="empty-sub">No pressing tasks right now.</div></div>';
    bindTaskRows(host);
  }

  function renderStatList(d) {
    var host = document.getElementById('statList');
    if (!host) return;
    var t = d.tasks || {};
    var rows = [
      { n: t.due_today, l: 'Due today', c: 'c-orange', go: '#/tasks?scope=due_today' },
      { n: t.overdue, l: 'Overdue', c: 'c-red', go: '#/tasks?scope=overdue' },
      { n: t.due_soon, l: 'Due in ' + (t.due_window_days || 7) + ' days', c: 'c-blue', go: '#/tasks?scope=all' },
      { n: t.without_due_date, l: 'No due date', c: 'c-teal', go: '#/tasks?scope=all' },
      { n: t.forwarded_count, l: 'Forwarded to you', c: 'c-green', go: '#/tasks?scope=forwarded' },
      { n: d.support && d.support.unassigned_count, l: 'Unassigned tickets', c: 'c-teal', go: '#/support?scope=unassigned' },
    ];
    host.innerHTML = rows.map(function (r) {
      return '<button class="stat-row is-link" data-go="' + r.go + '">' +
        '<span class="n ' + r.c + '">' + (r.n == null ? 0 : r.n) + '</span><span class="l">' + esc(r.l) + '</span>' +
        '<span class="chev">›</span></button>';
    }).join('');
    host.querySelectorAll('[data-go]').forEach(function (el) {
      el.addEventListener('click', function () { go(el.getAttribute('data-go')); });
    });
  }

  /* ---------------------------------------------------------------- tasks */

  function vTasks() {
    var scopeParam = hashParam('scope');
    if (scopeParam) {
      state.tasks.scope = scopeParam;
      state.tasks.status = 'open';
      // drop the one-shot ?scope= so sidebar clicks keep the user's own filter
      history.replaceState(null, '', '#/tasks');
    }

    view.innerHTML =
      '<div class="tasks-wrap">' +
        '<div class="tasks-list">' +
          '<div class="tasks-filter">' +
            '<div class="seg" id="segStatus">' +
              '<button data-s="open">Open</button><button data-s="closed">Closed</button><button data-s="all">All</button>' +
            '</div>' +
            '<div class="search">' + I.search + '<input id="taskSearch" placeholder="Search…" value="' + esc(state.tasks.q) + '"></div>' +
            '<div class="filter-chips" id="scopeChips">' +
              '<button class="fchip" data-sc="all">All</button>' +
              '<button class="fchip" data-sc="due_today">Due today</button>' +
              '<button class="fchip" data-sc="overdue">Overdue</button>' +
              '<button class="fchip" data-sc="forwarded">Forwarded</button>' +
            '</div>' +
            '<button class="btn btn-primary btn-block" id="taskNew" style="margin-top:8px">New task</button>' +
            '<div id="taskNewForm" hidden style="margin-top:8px">' +
              '<input class="f-input" id="ntTitle" placeholder="Task name (Enter to create)">' +
              '<select class="f-select" id="ntProject" style="margin-top:6px;width:100%"></select>' +
              '<div style="display:flex;gap:6px;margin-top:6px">' +
                '<input class="f-input" type="date" id="ntDue" style="flex:1">' +
                '<select class="f-select" id="ntPrio" style="flex:1"><option value="1">High</option><option value="2" selected>Medium</option><option value="3">Low</option></select>' +
              '</div>' +
              '<button class="btn btn-success btn-block" id="ntCreate" style="margin-top:6px">Create task</button>' +
            '</div>' +
          '</div>' +
          '<div class="tasks-rows" id="taskRows">' +
            '<div class="skel skel-row" style="margin:10px"></div><div class="skel skel-row" style="margin:10px"></div><div class="skel skel-row" style="margin:10px"></div>' +
          '</div>' +
          '<div class="tasks-count" id="taskCount"></div>' +
        '</div>' +
        '<div class="task-detail" id="taskDetail"></div>' +
      '</div>';

    markToolbar();
    document.getElementById('segStatus').querySelectorAll('button').forEach(function (b) {
      b.addEventListener('click', function () { state.tasks.status = b.getAttribute('data-s'); markToolbar(); loadTasks(); });
    });
    document.getElementById('scopeChips').querySelectorAll('button').forEach(function (b) {
      b.addEventListener('click', function () { state.tasks.scope = b.getAttribute('data-sc'); markToolbar(); loadTasks(); });
    });
    document.getElementById('taskSearch').addEventListener('input', function (e) {
      state.tasks.q = e.target.value; state.tasks.limit = 150; renderTaskList();
    });

    document.getElementById('taskNew').addEventListener('click', function () {
      var f = document.getElementById('taskNewForm');
      f.hidden = !f.hidden;
      this.textContent = f.hidden ? 'New task' : 'Cancel';
      this.classList.toggle('btn-primary', f.hidden);
      this.classList.toggle('btn-ghost', !f.hidden);
      if (!f.hidden) { fillTaskFormOptions(); document.getElementById('ntTitle').focus(); }
    });
    document.getElementById('ntTitle').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); document.getElementById('ntCreate').click(); }
    });
    document.getElementById('ntCreate').addEventListener('click', function () {
      var title = document.getElementById('ntTitle').value.trim();
      var proj = parseInt(document.getElementById('ntProject').value, 10) || 0;
      if (!title) { toast('Task name is required.', 'err'); return; }
      if (!proj) { toast('Pick a project.', 'err'); return; }
      var btn = document.getElementById('ntCreate');
      busy(btn, true, 'Creating…');
      api('POST', '/staff/tasks/create', {
        task: title, project_id: proj,
        due_date: document.getElementById('ntDue').value || undefined,
        priority: document.getElementById('ntPrio').value,
      }).then(function (res) {
        busy(btn, false);
        if (!res) return;
        if (res.ok) {
          toast('Task created.', 'ok');
          document.getElementById('ntTitle').value = '';
          document.getElementById('ntDue').value = '';
          document.getElementById('taskNewForm').hidden = true;
          var nb = document.getElementById('taskNew');
          if (nb) { nb.textContent = 'New task'; nb.classList.add('btn-primary'); nb.classList.remove('btn-ghost'); }
          if (res.task_id) state.tasks.sel = String(res.task_id);
          loadTasks();
          if (res.task_id) renderDetail(res.task_id);
        } else if (res.message && /time in|attend/i.test(res.message)) {
          toast('Time in first, then add tasks.', 'warn');
          go('#/attendance');
        } else toast(res.message || 'Could not create task.', 'err');
      });
    });

    if (state.tasks.sel) renderDetail(state.tasks.sel); else renderDetailEmpty();
    if (state.tasks.list.length) renderTaskList(); // cached rows first; revalidate silently
    loadTasks();
  }

  function fillTaskFormOptions() {
    var ps = document.getElementById('ntProject');
    if (!ps) return;
    var cur = ps.value;
    ps.innerHTML = '<option value="">' + (state.tasks.projects.length ? 'Pick a project…' : 'Loading projects…') + '</option>' +
      state.tasks.projects.map(function (p) {
        return '<option value="' + p.id + '">' + esc(p.name) + '</option>';
      }).join('');
    if (cur) ps.value = cur;
  }

  function markToolbar() {
    var seg = document.getElementById('segStatus');
    if (seg) seg.querySelectorAll('button').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-s') === state.tasks.status); });
    var sc = document.getElementById('scopeChips');
    if (sc) sc.querySelectorAll('button').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-sc') === state.tasks.scope); });
  }

  var taskReq = 0;
  function loadTasks() {
    var mine = ++taskReq; // ignore out-of-order responses when filters change quickly
    var host = document.getElementById('taskRows');
    if (host && !state.tasks.list.length) host.classList.add('is-loading');
    api('GET', '/staff/tasks?status=' + state.tasks.status + '&scope=' + state.tasks.scope).then(function (res) {
      if (mine !== taskReq) return;
      state.tasks.limit = 150;
      var h = document.getElementById('taskRows');
      if (h) h.classList.remove('is-loading');
      if (!res) { errorBox(h, 'Could not load tasks.', loadTasks); return; }
      state.tasks.list = res.tasks || [];
      state.tasks.projects = res.projects || state.tasks.projects || [];
      state.tasks.staff = res.staff_options || state.tasks.staff || [];
      state.tasks.prios = res.priority_options || state.tasks.prios || [];
      var f = document.getElementById('taskNewForm');
      if (f && !f.hidden) fillTaskFormOptions();
      renderTaskList();
    });
  }

  function filteredTasks() {
    var q = state.tasks.q.trim().toLowerCase();
    return state.tasks.list.filter(function (t) {
      if (!q) return true;
      return (t.title + ' ' + (t.project_name || '') + ' ' + (t.assigned_person_name || '')).toLowerCase().indexOf(q) !== -1;
    });
  }

  function renderTaskList() {
    var host = document.getElementById('taskRows');
    if (!host) return;
    var rows = filteredTasks();
    var count = document.getElementById('taskCount');
    if (count) count.textContent = rows.length + ' task' + (rows.length === 1 ? '' : 's') +
      (state.tasks.q ? ' matching “' + state.tasks.q + '”' : '');

    // Rendering thousands of rows at once (the "All" filter) freezes the UI,
    // so render in pages. Search still covers the full list.
    var limit = state.tasks.limit || 150;
    var shown = rows.slice(0, limit);
    host.innerHTML = rows.length ? shown.map(function (t) {
      return taskRow(t, { sel: String(t.id) === String(state.tasks.sel) });
    }).join('') + (rows.length > limit ? '<button class="panel-more" id="taskMore">Show more (' + (rows.length - limit) + ' left)</button>' : '')
      : '<div class="empty">' + I.inbox + '<div class="empty-title">Nothing here</div></div>';
    bindTaskRows(host);
    var more = document.getElementById('taskMore');
    if (more) more.addEventListener('click', function () {
      var top = host.scrollTop;
      state.tasks.limit = limit + 300; renderTaskList(); host.scrollTop = top;
    });
  }

  function renderDetailEmpty() {
    var host = document.getElementById('taskDetail');
    if (!host) return;
    host.innerHTML = '<div class="td-empty">' + I.pointer + '<div class="t">Select a task</div><div class="small">Its details will appear here.</div></div>';
  }

  function renderDetail(id) {
    var host = document.getElementById('taskDetail');
    if (!host) return;
    host.innerHTML =
      '<div class="td-head"><div class="td-eyebrow">Task #' + esc(id) + '</div>' +
      '<div class="skel" style="height:20px;width:60%;margin-top:8px"></div></div>' +
      '<div class="td-body"><div class="skel skel-block"></div><div class="skel skel-block" style="margin-top:10px"></div></div>';

    api('GET', '/staff/tasks/' + id).then(function (res) {
      if (!host.isConnected || state.tasks.sel !== String(id) && String(state.tasks.sel) !== String(id)) return;
      if (!res) { errorBox(host, 'Could not load this task.', function () { renderDetail(id); }); return; }
      if (!res.task) {
        host.innerHTML = '<div class="td-empty"><div class="t">Task not found</div><div class="small">It may have been deleted or reassigned.</div></div>';
        return;
      }
      var t = res.task;
      state.tasks.detail = t;
      var checklist = res.checklist || [];
      var done = checklist.filter(function (c) { return c.is_completed; }).length;
      var history = res.history || [];
      var closed = t.status === 'closed';

      host.innerHTML =
        '<div class="td-head">' +
          '<div class="td-head-row"><button class="btn btn-ghost btn-sm td-back" id="tdBack">' + I.back + ' Back</button>' +
          '<div class="td-eyebrow">Task #' + esc(t.id) + '</div></div>' +
          '<h2 class="td-title">' + esc(t.title) + '</h2>' +
          '<div class="td-chips">' +
            '<span class="chip ' + (closed ? 'chip-gray' : 'chip-green') + '">' + (closed ? 'Closed' : 'Open') + '</span>' +
            '<span class="chip ' + prioCls(t) + '">' + esc(t.priority_label || '') + ' priority</span>' +
            (closed ? '' : dueMetaChip(t)) +
            (t.is_forwarded_pending ? '<span class="chip chip-teal">Forwarded</span>' : '') +
          '</div>' +
        '</div>' +
        '<div class="td-body">' +
          '<dl class="kv">' +
            '<dt>Project</dt><dd>' + esc(t.project_name || 'No project') + '</dd>' +
            '<dt>Assigned to</dt><dd>' + esc(t.assigned_person_name || '') + '</dd>' +
            '<dt>Reported</dt><dd>' + esc(fmtDate(t.reported_date)) + '</dd>' +
            '<dt>Due</dt><dd>' + esc(fmtDate(t.due_date)) + '</dd>' +
            (t.support_ticket_number ? '<dt>Ticket</dt><dd>' + esc(t.support_ticket_number) + '</dd>' : '') +
            (t.admin_comment ? '<dt>Admin note</dt><dd>' + esc(t.admin_comment) + '</dd>' : '') +
          '</dl>' +
          (checklist.length ?
            '<div class="sect"><div class="sect-title">Checklist · ' + done + '/' + checklist.length + '</div>' +
            '<div class="progress"><i style="width:' + Math.round(done / checklist.length * 100) + '%"></i></div>' +
            checklist.map(function (c, idx) {
              return '<button class="check-item' + (c.is_completed ? ' is-done' : '') + '" data-ci="' + idx + '"' + (closed ? ' disabled' : '') + '>' +
                '<span class="box">' + I.check + '</span><span class="txt">' + esc(c.item_description) + '</span></button>';
            }).join('') + '</div>' : '') +
          '<div class="sect"><div class="sect-title">History</div><div class="timeline">' +
            (history.length ? history.map(function (h) {
              return '<div class="tl-item ' + (h.task_status === '0' ? 'is-close' : 'is-open') + '">' +
                '<div class="tl-note">' + esc(h.note || (h.task_status === '0' ? 'Closed' : 'Updated')) + '</div>' +
                '<div class="tl-meta">' + esc(h.posted_by || '') + (h.posted_by ? ' · ' : '') + esc(fmtDate(h.posted_at)) + (h.points ? ' · +' + h.points + ' pts' : '') + '</div></div>';
            }).join('') : '<div class="muted small">No history yet.</div>') +
          '</div></div>' +
        '</div>' +
        '<div class="td-foot">' +
          (closed
            ? '<button class="btn btn-soft btn-block" id="tdReopen">Reopen task</button>'
            : '<div class="td-foot-actions">' +
                '<button class="btn btn-success" id="tdClose">' + I.check + ' Close task</button>' +
                ((state.tasks.staff || []).length ? '<button class="btn btn-ghost" id="tdFwdGo" title="Forward">' + I.forward + '<span class="lbl">Forward</span></button>' : '') +
                '<button class="btn btn-danger-soft" id="tdDelete" title="Delete">' + I.trash + '<span class="lbl">Delete</span></button>' +
              '</div>') +
        '</div>';

      var sc = document.getElementById('tdClose');
      var sr = document.getElementById('tdReopen');
      var sb = document.getElementById('tdBack');
      var fg = document.getElementById('tdFwdGo');
      var del = document.getElementById('tdDelete');
      if (sc) sc.addEventListener('click', function () { closeTask(t.id); });
      if (sr) sr.addEventListener('click', function () { reopenTask(t.id); });
      if (sb) sb.addEventListener('click', function () { state.tasks.sel = null; renderDetailEmpty(); renderTaskList(); });
      host.querySelectorAll('[data-ci]').forEach(function (b) {
        b.addEventListener('click', function () {
          var idx = +b.getAttribute('data-ci');
          var items = checklist.map(function (c, i) {
            var doneNow = i === idx ? !c.is_completed : !!c.is_completed;
            return { item_description: c.item_description, is_completed: doneNow ? 1 : 0, status: doneNow ? 'Completed' : 'Pending' };
          });
          b.classList.toggle('is-done');
          host.querySelectorAll('[data-ci]').forEach(function (x) { x.disabled = true; });
          api('POST', '/staff/tasks/' + t.id + '/checklist', { checklist_items: items }).then(function (r) {
            if (r && r.ok) renderDetail(t.id);
            else {
              b.classList.toggle('is-done');
              host.querySelectorAll('[data-ci]').forEach(function (x) { x.disabled = false; });
              if (r) toast(r.message || 'Could not update the checklist.', 'err');
            }
          });
        });
      });
      if (fg) fg.addEventListener('click', function () { forwardTaskModal(t); });
      if (del) del.addEventListener('click', function () { confirmDeleteTask(t); });
    });
  }

  function forwardTaskModal(t) {
    formModal({
      title: 'Forward task', sub: 'Task #' + t.id + ' · ' + t.title,
      body: pickerHtml(state.tasks.staff, 'Forward to') + '<div class="f-field"><label class="f-label">Note (optional)</label><input class="f-input" name="note" placeholder="Anything they should know?"></div>',
      okLabel: 'Forward',
      onOpen: bindPicker,
      onSubmit: function (form) {
        var to = +form.querySelector('.picker').dataset.uid || 0;
        if (!to) { toast('Pick who to forward this task to.', 'warn'); return; }
        return api('POST', '/staff/tasks/' + t.id + '/forward', { forward_to: to, forward_note: form.note.value.trim() }).then(function (res) {
          if (res && res.ok) { toast(res.message || 'Task forwarded.', 'ok'); loadTasks(); renderDetail(t.id); return true; }
          if (res && res.message && /time in|attend/i.test(res.message)) { toast('Time in first, then forward tasks.', 'warn'); go('#/attendance'); return true; }
          if (res) toast(res.message || 'Could not forward task.', 'err');
          return false;
        });
      },
    });
  }

  function confirmDeleteTask(t) {
    if (!t) return;
    confirmBox('Delete task?', '“' + (t.title || 'This task') + '” will be permanently deleted.', 'Delete', true).then(function (yes) {
      if (!yes) return;
      api('POST', '/staff/tasks/' + t.id + '/delete', {}).then(function (res) {
        if (!res) return;
        if (res.ok) {
          toast('Task deleted.', 'ok');
          if (String(state.tasks.sel) === String(t.id)) { state.tasks.sel = null; renderDetailEmpty(); }
          loadTasks();
        } else toast(res.message || 'Could not delete task.', 'err');
      });
    });
  }

  // Flips the check state instantly in the model + rows; the API call races
  // behind and a failure path repaints it back — no waiting for the network.
  function paintTaskStatus(id, closed) {
    var apply = function (t) { if (t) t.status = closed ? 'closed' : 'open'; };
    apply(state.tasks.list.find(function (t) { return String(t.id) === String(id); }));
    if (state.tasks.detail && String(state.tasks.detail.id) === String(id)) apply(state.tasks.detail);
    apply((palData.tasks || []).find(function (t) { return String(t.id) === String(id); }));
    view.querySelectorAll('.trow[data-task="' + id + '"]').forEach(function (row) {
      row.classList.toggle('is-closed', closed);
      var c = row.querySelector('[data-check]');
      if (c) { c.classList.toggle('is-done', closed); c.setAttribute('aria-checked', String(closed)); }
    });
  }

  function closeTask(id) {
    var task = state.tasks.list.find(function (t) { return String(t.id) === String(id); }) || state.tasks.detail;
    var title = task ? task.title : 'this task';
    confirmBox('Close task?', '“' + title + '” will be marked as closed and recorded in the task history.', 'Close task').then(function (yes) {
      if (!yes) return;
      paintTaskStatus(id, true);
      api('POST', '/staff/tasks/' + id + '/status', { task_status: '0', note: 'Closed via BERPS Desk' }).then(function (res) {
        if (res && res.ok) {
          toast('Task closed.', 'ok');
          if (state.route === 'tasks') { loadTasks(); if (String(state.tasks.sel) === String(id)) renderDetail(id); }
          else if (state.route === 'home') loadHome();
          return;
        }
        paintTaskStatus(id, false);
        if (!res) { toast('No connection — task not closed.', 'err'); return; }
        if (res.message && /time in/i.test(res.message)) {
          toast('Time in first, then close tasks.', 'warn');
          go('#/attendance');
          return;
        }
        toast(res.message || 'Could not close the task.', 'err');
      });
    });
  }

  function reopenTask(id) {
    paintTaskStatus(id, false);
    api('POST', '/staff/tasks/' + id + '/status', { task_status: '1', note: 'Reopened via BERPS Desk' }).then(function (res) {
      if (res && res.ok) {
        toast('Task reopened.', 'ok');
        if (state.route === 'tasks') { loadTasks(); if (String(state.tasks.sel) === String(id)) renderDetail(id); }
        else if (state.route === 'home') loadHome();
        return;
      }
      paintTaskStatus(id, true);
      if (!res) { toast('No connection — task not reopened.', 'err'); return; }
      if (res.message && /time in/i.test(res.message)) {
        toast('Time in first, then reopen tasks.', 'warn');
        go('#/attendance');
      } else toast(res.message || 'Could not reopen the task.', 'err');
    });
  }

  /* ---------------------------------------------------------------- support */

  var sup = { scope: 'open', list: [], sel: null, detail: null, counts: {} };

  function vSupport() {
    var sp = hashParam('scope');
    if (sp) { sup.scope = sp; history.replaceState(null, '', '#/support'); }
    view.innerHTML =
      '<div class="tasks-wrap">' +
        '<div class="tasks-list">' +
          '<div class="tasks-filter">' +
            '<div class="filter-chips" id="supScope">' +
              [['open', 'Open'], ['unassigned', 'Unassigned'], ['closed', 'Closed'], ['all', 'All']].map(function (s) {
                return '<button class="fchip" data-s="' + s[0] + '">' + s[1] + '<span class="fchip-n" data-n="' + s[0] + '"></span></button>';
              }).join('') +
            '</div>' +
            '<div class="search">' + I.search + '<input id="supSearch" placeholder="Search tickets…" value="' + esc(sup.q || '') + '"></div>' +
          '</div>' +
          '<div class="tasks-rows" id="supRows"><div class="skel skel-row" style="margin:10px"></div><div class="skel skel-row" style="margin:10px"></div></div>' +
          '<div class="tasks-count" id="supCount"></div>' +
        '</div>' +
        '<div class="sd-detail" id="supDetail"></div>' +
      '</div>';

    markSupScope();
    document.getElementById('supScope').querySelectorAll('button').forEach(function (b) {
      b.addEventListener('click', function () {
        sup.scope = b.getAttribute('data-s');
        markSupScope();
        loadSupList();
      });
    });
    document.getElementById('supSearch').addEventListener('input', function (e) { sup.q = e.target.value; renderSupList(); });
    if (sup.sel) renderSupDetail(sup.sel); else renderSupEmpty();
    if (sup.list.length) renderSupList(); // cached rows first
    loadSupList();
  }

  function markSupScope() {
    document.querySelectorAll('#supScope button').forEach(function (b) {
      var k = b.getAttribute('data-s');
      b.classList.toggle('is-active', k === sup.scope);
      var n = sup.counts && sup.counts[k];
      var el = b.querySelector('.fchip-n');
      if (el) el.textContent = n ? n : '';
    });
  }

  var supReq = 0;
  function loadSupList() {
    var mine = ++supReq;
    api('GET', '/staff/support/issues?scope=' + sup.scope).then(function (res) {
      if (mine !== supReq) return;
      if (!res) { errorBox(document.getElementById('supRows'), 'Could not load tickets.', loadSupList); return; }
      sup.list = res.issues || [];
      sup.counts = res.counts || {};
      renderSupList();
    });
  }

  function supPrioChip(i) {
    var cls = { high: 'chip-red', urgent: 'chip-red', medium: 'chip-orange', low: 'chip-teal' }[String(i.priority || '').toLowerCase()] || 'chip-gray';
    return '<span class="chip ' + cls + '">' + esc(i.priority || 'medium') + '</span>';
  }

  function supStatusChip(i) {
    return '<span class="chip ' + (i.is_closed ? 'chip-gray' : i.is_unassigned ? 'chip-teal' : 'chip-green') + '">' +
      (i.is_closed ? 'Closed' : i.is_unassigned ? 'Unassigned' : 'Open') + '</span>';
  }

  function renderSupList() {
    var host = document.getElementById('supRows');
    if (!host) return;
    markSupScope();
    var q = String(sup.q || '').trim().toLowerCase();
    var rows = sup.list.filter(function (i) {
      if (!q) return true;
      return [i.title, i.ticket_number, i.customer_name, i.department_name, i.project_name].join(' ').toLowerCase().indexOf(q) !== -1;
    });
    var limit = sup.limit || 150;
    var c = document.getElementById('supCount');
    if (c) c.textContent = rows.length + ' ticket' + (rows.length === 1 ? '' : 's');
    host.innerHTML = rows.length ? rows.slice(0, limit).map(function (i) {
      return '<button class="trow' + (String(i.id) === String(sup.sel) ? ' is-sel' : '') + '" data-sup="' + i.id + '">' +
        '<span class="trow-main">' +
          '<span class="trow-title">' + esc(i.title || i.ticket_number) + '</span>' +
          '<span class="trow-meta">' + supStatusChip(i) + supPrioChip(i) +
            '<span>' + esc(i.customer_name || 'Customer') + '</span>' +
          '</span>' +
        '</span>' +
        (i.updated_label || i.created_label ? '<span class="trow-time">' + esc(shortWhen(i.updated_label || i.created_label)) + '</span>' : '') +
      '</button>';
    }).join('') + (rows.length > limit ? '<button class="panel-more" id="supMore">Show more (' + (rows.length - limit) + ' left)</button>' : '')
      : '<div class="empty">' + I.lifebuoy + '<div class="empty-title">' + (q ? 'No matching tickets' : 'No tickets') + '</div></div>';
    host.querySelectorAll('[data-sup]').forEach(function (row) {
      row.addEventListener('click', function () { selectSup(row.getAttribute('data-sup')); });
      row.addEventListener('contextmenu', function (e) {
        if (!B.contextMenu) return;
        e.preventDefault();
        var id = row.getAttribute('data-sup');
        var i = sup.list.find(function (x) { return String(x.id) === String(id); });
        if (!i) return;
        var items = [{ id: 'open', label: 'Open' }];
        if (!i.is_closed) items.push({ id: 'close', label: 'Close ticket…' });
        items.push({ sep: true }, { id: 'copy', label: 'Copy ticket number', copyText: i.ticket_number || ('#' + id) });
        B.contextMenu(items).then(function (a) {
          if (a === 'open') selectSup(id);
          else if (a === 'close') closeTicketModal(i);
        });
      });
    });
    var more = document.getElementById('supMore');
    if (more) more.addEventListener('click', function () { sup.limit = limit + 300; renderSupList(); });
  }

  // "Nov 24, 2026 · 11:47 AM" -> "Nov 24"
  function shortWhen(label) {
    var m = String(label || '').match(/^([A-Z][a-z]{2} \d{1,2})/);
    return m ? m[1] : String(label || '');
  }

  function renderSupEmpty() {
    var host = document.getElementById('supDetail');
    if (host) host.innerHTML = '<div class="td-empty">' + I.lifebuoy + '<div class="t">Select a ticket</div><div class="small">The conversation will appear here.</div></div>';
  }

  function renderSupDetail(id) {
    var host = document.getElementById('supDetail');
    if (!host) return;
    host.innerHTML = '<div class="td-head"><div class="skel" style="height:20px;width:55%"></div></div><div class="td-body"><div class="skel skel-block"></div></div>';

    api('GET', '/staff/support/issues/' + id).then(function (res) {
      if (!host.isConnected || String(sup.sel) !== String(id)) return;
      if (!res) { errorBox(host, 'Could not load this ticket.', function () { renderSupDetail(id); }); return; }
      if (!res.issue) { host.innerHTML = '<div class="td-empty"><div class="t">Ticket not found</div></div>'; return; }
      var i = res.issue;
      sup.detail = i;
      var comments = res.comments || [];
      var closed = i.is_closed;
      var assignable = res.assignable_users || [];
      var taggable = res.taggable_users || [];
      var assigned = String(i.assigned_name || '').trim();

      host.innerHTML =
        '<div class="td-head">' +
          '<div class="td-head-row">' +
            '<button class="btn btn-ghost btn-sm td-back" id="supBack">' + I.back + ' Back</button>' +
            '<div class="td-eyebrow">' + esc(i.ticket_number) + '</div>' +
            (closed ? '' :
              '<div class="td-actions">' +
                (assignable.length ? '<button class="btn btn-ghost btn-sm" id="supAssign" title="Assign / forward">' + I.userPlus + '<span class="lbl">Assign</span></button>' : '') +
                (taggable.length ? '<button class="btn btn-ghost btn-sm" id="supTag" title="Tag a teammate">' + I.at + '<span class="lbl">Tag</span></button>' : '') +
                '<button class="btn btn-success btn-sm" id="supClose" title="Close ticket">' + I.check + '<span class="lbl">Close</span></button>' +
              '</div>') +
          '</div>' +
          '<h2 class="td-title">' + esc(i.title || 'Support ticket') + '</h2>' +
          '<div class="td-chips">' + supStatusChip(i) + supPrioChip(i) +
            (i.category ? '<span class="chip chip-line">' + esc(i.category) + '</span>' : '') +
            (i.department_name && String(i.department_name).toLowerCase() !== String(i.category || '').toLowerCase() ? '<span class="chip chip-line">' + esc(i.department_name) + '</span>' : '') +
          '</div>' +
        '</div>' +
        '<div class="td-body sd-body">' +
          '<dl class="kv">' +
            '<dt>Customer</dt><dd>' + esc(i.customer_name || '—') + '</dd>' +
            (i.customer_email ? '<dt>Email</dt><dd>' + esc(i.customer_email) + '</dd>' : '') +
            (i.customer_phone ? '<dt>Phone</dt><dd>' + esc(i.customer_phone) + '</dd>' : '') +
            (i.project_name ? '<dt>Project</dt><dd>' + esc(i.project_name) + '</dd>' : '') +
            '<dt>Assigned</dt><dd>' + (assigned ? esc(assigned) : '<span class="muted">Nobody yet</span>') + '</dd>' +
            '<dt>Created</dt><dd>' + esc(i.created_label || '—') + '</dd>' +
            (i.task_id ? '<dt>Linked task</dt><dd><button class="link-btn" id="supTask">Task #' + esc(i.task_id) + ' ›</button></dd>' : '') +
            (i.reference_link ? '<dt>Reference</dt><dd><a href="' + esc(i.reference_link) + '" target="_blank" rel="noopener" class="ext-link">' + esc(i.reference_link) + '</a></dd>' : '') +
          '</dl>' +
          (i.description ? '<div class="sect"><div class="sect-title">Description</div><div class="desc-box">' + esc(i.description) + '</div></div>' : '') +
          '<div class="sect"><div class="sect-title">Conversation · ' + comments.length + '</div>' +
            '<div class="chat-thread">' +
              (comments.length ? comments.map(function (cm) {
                var kind = cm.is_internal ? 'internal' : cm.is_mine ? 'mine' : cm.is_customer ? 'customer' : 'theirs';
                return '<div class="bubble ' + kind + '"><div class="b-meta">' + esc(cm.author_name || '') + ' · ' + esc(cm.created_label || '') + '</div>' + esc(cm.comment || '') + '</div>';
              }).join('') : '<div class="muted small">No messages yet.</div>') +
            '</div></div>' +
        '</div>' +
        (closed ? '<div class="td-foot"><div class="muted small" style="text-align:center">This ticket is closed.</div></div>' :
          res.can_reply_chat ?
            '<div class="td-foot composer">' +
              '<textarea class="f-area" id="supReply" rows="1" placeholder="Reply to ' + esc(i.customer_name || 'customer') + '…"></textarea>' +
              '<button class="btn btn-primary composer-send" id="supSend" title="Send (⌘/Ctrl+Enter)">' + I.send + '<span class="lbl">Send</span></button>' +
            '</div>' : '');

      var after = function () { renderSupDetail(id); loadSupList(); };
      document.getElementById('supBack').addEventListener('click', function () { sup.sel = null; renderSupEmpty(); renderSupList(); });
      var st = document.getElementById('supTask');
      if (st) st.addEventListener('click', function () { selectTask(i.task_id); });
      var body = host.querySelector('.sd-body');
      if (comments.length && body) host.scrollTop = host.scrollHeight;

      var reply = document.getElementById('supReply');
      var send = document.getElementById('supSend');
      if (reply) {
        autoGrow(reply);
        reply.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) { e.preventDefault(); send.click(); }
        });
      }
      if (send) send.addEventListener('click', function () {
        var txt = reply.value.trim();
        if (!txt) { reply.focus(); return; }
        busy(send, true);
        api('POST', '/staff/support/issues/' + id + '/comment', { comment: txt }).then(function (r) {
          busy(send, false);
          if (r && r.ok) { toast('Reply sent.', 'ok'); renderSupDetail(id); }
          else if (r) toast(r.message || 'Could not send reply.', 'err');
        });
      });

      var ab = document.getElementById('supAssign');
      if (ab) ab.addEventListener('click', function () {
        formModal({
          title: 'Assign ticket', sub: i.ticket_number + ' · ' + (i.title || ''),
          body: pickerHtml(assignable, 'Assign to') + '<div class="f-field"><label class="f-label">Note (optional)</label><input class="f-input" name="note" placeholder="Anything they should know?"></div>',
          okLabel: 'Assign',
          onOpen: bindPicker,
          onSubmit: function (form) {
            var to = +form.querySelector('.picker').dataset.uid || 0;
            if (!to) { toast('Pick who to assign this ticket to.', 'warn'); return; }
            return api('POST', '/staff/support/issues/' + id + '/forward', { forward_to: to, forward_note: form.note.value.trim() }).then(function (r) {
              if (r && r.ok) { toast(r.message || 'Ticket assigned.', 'ok'); after(); return true; }
              if (r) toast(r.message || 'Could not assign ticket.', 'err');
              return false;
            });
          },
        });
      });

      var tb = document.getElementById('supTag');
      if (tb) tb.addEventListener('click', function () {
        formModal({
          title: 'Tag a teammate', sub: i.ticket_number + ' · ' + (i.title || ''),
          body: pickerHtml(taggable, 'Teammate') + '<div class="f-field"><label class="f-label">Note (optional)</label><input class="f-input" name="note" placeholder="Why are you tagging them?"></div>',
          okLabel: 'Tag',
          onOpen: bindPicker,
          onSubmit: function (form) {
            var to = +form.querySelector('.picker').dataset.uid || 0;
            if (!to) { toast('Pick who to tag.', 'warn'); return; }
            return api('POST', '/staff/support/issues/' + id + '/tag', { tag_user_id: to, tag_note: form.note.value.trim() }).then(function (r) {
              if (r && r.ok) { toast(r.message || 'Teammate tagged.', 'ok'); renderSupDetail(id); return true; }
              if (r) toast(r.message || 'Could not tag.', 'err');
              return false;
            });
          },
        });
      });

      var cb = document.getElementById('supClose');
      if (cb) cb.addEventListener('click', function () { closeTicketModal(i); });
    });
  }

  function closeTicketModal(i) {
    formModal({
      title: 'Close ticket', sub: i.ticket_number + ' · ' + (i.title || ''),
      body: '<div class="f-field"><label class="f-label">Resolution message <span class="req">*</span></label>' +
        '<textarea class="f-area" name="msg" placeholder="How was this resolved? The customer will see this."></textarea></div>',
      okLabel: 'Close ticket', okClass: 'btn-success',
      onSubmit: function (form) {
        var msg = form.msg.value.trim();
        if (!msg) { toast('A resolution message is required.', 'warn'); form.msg.focus(); return; }
        return api('POST', '/staff/support/issues/' + i.id + '/close', { close_message: msg }).then(function (r) {
          if (r && r.ok) { toast('Ticket closed.', 'ok'); sup.sel = null; renderSupEmpty(); loadSupList(); return true; }
          if (r) toast(r.message || 'Could not close the ticket.', 'err');
          return false;
        });
      },
    });
  }

  /* ----------------------------------------------------------------- notes */

  var nts = { list: [], sel: null, dirty: false };

  function vNotes() {
    view.innerHTML =
      '<div class="tasks-wrap">' +
        '<div class="notes-list">' +
          '<div class="tasks-filter">' +
            '<button class="btn btn-primary btn-block" id="noteNew">+ New note</button>' +
            '<div class="search">' + I.search + '<input id="noteSearch" placeholder="Search notes…" value="' + esc(nts.q || '') + '"></div>' +
          '</div>' +
          '<div class="notes-rows" id="noteRows"><div class="skel skel-row" style="margin:10px"></div></div>' +
        '</div>' +
        '<div class="note-edit" id="noteEdit"></div>' +
      '</div>';

    document.getElementById('noteNew').addEventListener('click', function () { nts.sel = null; renderNoteEditor(null); document.querySelectorAll('#noteRows .nrow2').forEach(function (r) { r.classList.remove('is-sel'); }); });
    document.getElementById('noteSearch').addEventListener('input', function (e) { nts.q = e.target.value; renderNoteList(); });
    renderNoteEmpty();
    if (nts.list.length) renderNoteList(); // cached rows first
    loadNotes().then(function () {
      var n = nts.sel && nts.list.find(function (x) { return String(x.id) === String(nts.sel); });
      if (n) renderNoteEditor(n);
    });
  }

  function loadNotes() {
    return api('GET', '/staff/notes').then(function (res) {
      if (!res) { errorBox(document.getElementById('noteRows'), 'Could not load notes.', loadNotes); return; }
      nts.list = res.notes || [];
      renderNoteList();
    });
  }

  function starBtn(n) {
    return '<button class="fav-star' + (n.is_favorite ? ' is-fav' : '') + '" data-fav="' + n.id + '" title="Favorite">' + I.star + '</button>';
  }

  function renderNoteList() {
    var host = document.getElementById('noteRows');
    if (!host) return;
    var q = String(nts.q || '').trim().toLowerCase();
    var sorted = nts.list.filter(function (n) {
      if (!q) return true;
      return ((n.title || '') + ' ' + htmlToText(n.description) + ' ' + (n.tags || []).join(' ')).toLowerCase().indexOf(q) !== -1;
    }).sort(function (a, b) { return (b.is_favorite - a.is_favorite) || String(b.date).localeCompare(String(a.date)) || (b.id - a.id); });
    host.innerHTML = sorted.length ? sorted.map(function (n) {
      var snippet = htmlToText(n.description).replace(/\s+/g, ' ').slice(0, 90);
      return '<div class="nrow2' + (String(n.id) === String(nts.sel) ? ' is-sel' : '') + '" data-note="' + n.id + '" role="button" tabindex="0">' +
        starBtn(n) +
        '<span class="nrow2-b"><span class="nrow2-t">' + esc(n.title || snippet || 'Untitled') + '</span>' +
        (snippet && n.title ? '<span class="nrow2-s">' + esc(snippet) + '</span>' : '') +
        '<span class="nrow2-m">' + esc(n.date_label || '') + (n.tags && n.tags.length ? ' · ' + esc(n.tags.join(', ')) : '') + '</span></span>' +
      '</div>';
    }).join('') : '<div class="empty">' + I.inbox + '<div class="empty-title">' + (q ? 'No matching notes' : 'No notes') + '</div></div>';
    host.querySelectorAll('[data-note]').forEach(function (row) {
      var open = function () { openNoteById(row.getAttribute('data-note')); };
      row.addEventListener('click', function (e) { if (!e.target.closest('[data-fav]')) open(); });
      row.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.target.closest('[data-fav]')) open(); });
      row.addEventListener('contextmenu', function (e) {
        if (!B.contextMenu) return;
        e.preventDefault();
        var id = row.getAttribute('data-note');
        var n = nts.list.find(function (x) { return String(x.id) === String(id); });
        if (!n) return;
        B.contextMenu([
          { id: 'open', label: 'Open' },
          { id: 'fav', label: n.is_favorite ? 'Unfavorite' : 'Favorite' },
          { sep: true },
          { id: 'copy', label: 'Copy title', copyText: n.title || 'Untitled' },
          { sep: true },
          { id: 'del', label: 'Delete…' },
        ]).then(function (a) {
          if (a === 'open') openNoteById(id);
          else if (a === 'fav') {
            api('POST', '/staff/notes/' + id + '/favorite').then(function (r) {
              if (r && r.ok) { n.is_favorite = !n.is_favorite; renderNoteList(); }
              else if (r) toast(r.message || 'Could not update favorite.', 'err');
            });
          } else if (a === 'del') confirmDeleteNote(n);
        });
      });
    });
    host.querySelectorAll('[data-fav]').forEach(function (b) {
      b.addEventListener('click', function (e) {
        e.stopPropagation();
        var id = b.getAttribute('data-fav');
        var n = nts.list.find(function (x) { return String(x.id) === id; });
        if (n) { n.is_favorite = !n.is_favorite; renderNoteList(); } // optimistic
        api('POST', '/staff/notes/' + id + '/favorite').then(function (r) {
          if (!(r && r.ok) && n) { n.is_favorite = !n.is_favorite; renderNoteList(); if (r) toast(r.message || 'Could not update favorite.', 'err'); }
        });
      });
    });
  }

  function renderNoteEmpty() {
    var host = document.getElementById('noteEdit');
    if (!host) return;
    host.classList.remove('is-cover');
    host.innerHTML = '<div class="td-empty">' + I.inbox + '<div class="t">Select a note</div><div class="small">Or write a new one.</div></div>';
  }

  function renderNoteEditor(n) {
    var host = document.getElementById('noteEdit');
    if (!host) return;
    n = n || { title: '', description: '', tags: [], date: ymd() };
    var origText = htmlToText(n.description);
    host.classList.add('is-cover');
    host.innerHTML =
      '<div class="td-head">' +
        '<button class="btn btn-ghost td-back" id="noteBack">' + I.back + ' Back</button>' +
        '<div class="td-eyebrow">' + (n.id ? 'Note #' + n.id : 'New note') + '</div>' +
        '<div style="margin-top:8px"><input class="f-input" id="nTitle" placeholder="Title" value="' + esc(n.title || '') + '" style="font-size:15px;font-weight:650"></div>' +
      '</div>' +
      '<div class="note-edit-b">' +
        '<div class="f-field"><label class="f-label">Note</label><textarea class="f-area note-area" id="nDesc" placeholder="Write it down…">' + esc(origText) + '</textarea></div>' +
        '<div class="f-row">' +
          '<div class="f-field"><label class="f-label">Date</label><input class="f-input" type="date" id="nDate" value="' + esc(n.date || '') + '"></div>' +
          '<div class="f-field"><label class="f-label">Tags (comma separated)</label><input class="f-input" id="nTags" placeholder="work, ideas" value="' + esc((n.tags || []).join(', ')) + '"></div>' +
        '</div>' +
      '</div>' +
      '<div class="note-edit-f">' +
        '<button class="btn btn-primary" id="nSave">' + (n.id ? 'Save changes' : 'Save note') + '</button>' +
        '<span class="muted small kbd-hint">⌘/Ctrl+S</span>' +
        (n.id ? '<button class="btn btn-danger-soft" id="nDelete" style="margin-left:auto">' + I.trash + ' Delete</button>' : '') +
      '</div>';

    document.getElementById('noteBack').addEventListener('click', function () { nts.sel = null; host.classList.remove('is-cover'); renderNoteEmpty(); renderNoteList(); });
    var saveBtn = document.getElementById('nSave');
    var save = function () {
      if (saveBtn.disabled) return;
      var text = document.getElementById('nDesc').value;
      // Keep the portal's rich-text HTML untouched unless the text was edited.
      var desc = text.trim() === origText.trim() && n.description ? n.description : (text.trim() ? textToHtml(text.trim()) : '');
      var body = {
        title: document.getElementById('nTitle').value.trim(),
        description: desc,
        tags: document.getElementById('nTags').value.trim(),
        date: document.getElementById('nDate').value,
      };
      if (!body.title && !body.description) { toast('Write a title or note first.', 'warn'); return; }
      var path = n.id ? '/staff/notes/' + n.id + '/update' : '/staff/notes/create';
      busy(saveBtn, true, 'Saving…');
      api('POST', path, body).then(function (r) {
        busy(saveBtn, false);
        if (r && r.ok) {
          toast('Note saved.', 'ok');
          var id = n.id || r.note_id || null;
          nts.sel = id ? String(id) : null;
          loadNotes().then(function () {
            var fresh = id && nts.list.find(function (x) { return String(x.id) === String(id); });
            if (fresh && document.getElementById('noteEdit')) renderNoteEditor(fresh);
          });
        } else if (r) toast(r.message || 'Could not save.', 'err');
      });
    };
    saveBtn.addEventListener('click', save);
    host.addEventListener('keydown', function (e) {
      if ((e.metaKey || e.ctrlKey) && (e.key === 's' || e.key === 'S')) { e.preventDefault(); save(); }
    });
    if (!n.id) document.getElementById('nTitle').focus();
    var del = document.getElementById('nDelete');
    if (del) del.addEventListener('click', function () { confirmDeleteNote(n); });
  }

  function confirmDeleteNote(n) {
    if (!n) return;
    confirmBox('Delete note?', '“' + (n.title || 'This note') + '” will be deleted permanently.', 'Delete', true).then(function (yes) {
      if (!yes) return;
      api('POST', '/staff/notes/' + n.id + '/delete').then(function (r) {
        if (r && r.ok) { toast('Note deleted.', 'ok'); nts.sel = null; renderNoteEmpty(); loadNotes(); }
        else toast((r && r.message) || 'Could not delete.', 'err');
      });
    });
  }

  /* -------------------------------------------------------------- reminders */

  function vReminders() {
    view.innerHTML =
      viewHead('Reminders', 'Things you asked to be reminded about.',
        '<button class="btn btn-ghost" id="remRefresh">' + I.refresh + ' Refresh</button>') +
      '<div class="view-body" style="max-width:720px">' +
        '<div class="panel"><div class="panel-head">New reminder</div><div class="panel-body">' +
          '<div class="f-row f-row-wrap"><div class="f-field" style="flex:2 1 220px"><input class="f-input" id="rTitle" placeholder="Remind me to…"></div>' +
          '<div class="f-field"><input class="f-input" type="datetime-local" id="rWhen"></div>' +
          '<div class="f-field"><select class="f-select" id="rRec"><option value="once">Once</option><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>' +
          '<div class="f-field" style="flex:0 0 auto"><button class="btn btn-primary" id="rAdd">Add</button></div></div>' +
        '</div></div>' +
        '<div class="panel"><div class="panel-head">Upcoming<span class="sub" id="remDue"></span></div>' +
        '<div class="panel-body flush" id="remList"><div class="skel skel-row" style="margin:10px 14px"></div></div></div>' +
      '</div>';

    document.getElementById('remRefresh').addEventListener('click', loadReminders);
    var inHour = new Date(Date.now() + 3600000);
    document.getElementById('rWhen').value = ymd(inHour) + 'T' + pad2(inHour.getHours()) + ':00';
    var addBtn = document.getElementById('rAdd');
    document.getElementById('rTitle').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); addBtn.click(); }
    });
    addBtn.addEventListener('click', function () {
      var title = document.getElementById('rTitle').value.trim();
      var when = document.getElementById('rWhen').value;
      var rec = document.getElementById('rRec').value;
      if (!title || !when) { toast('Title and time are required.', 'warn'); return; }
      busy(addBtn, true);
      api('POST', '/staff/reminders/create', { title: title, remind_at: when.replace('T', ' ') + (when.length === 16 ? ':00' : ''), recurrence: rec }).then(function (r) {
        busy(addBtn, false);
        if (r && r.ok) { toast('Reminder added.', 'ok'); document.getElementById('rTitle').value = ''; loadReminders(); }
        else if (r) toast(r.message || 'Could not add reminder.', 'err');
      });
    });
    loadReminders();
  }

  function loadReminders() {
    api('GET', '/staff/reminders').then(function (res) {
      var host = document.getElementById('remList');
      if (!host) return;
      if (!res) { errorBox(host, 'Could not load reminders.', loadReminders); return; }
      var due = document.getElementById('remDue');
      if (due) due.textContent = res.due_today_count ? res.due_today_count + ' due today' : '';
      var rows = (res.reminders || []).slice().sort(function (a, b) { return String(a.remind_at).localeCompare(String(b.remind_at)); });
      var today = ymd();
      host.innerHTML = rows.length ? rows.map(function (r) {
        var isDue = String(r.remind_at || '').slice(0, 10) <= today;
        return '<div class="rrow' + (isDue ? ' is-due' : '') + '">' +
          '<span class="rrow-b"><span class="rrow-t">' + esc(r.title || 'Reminder') + '</span>' +
          (r.description ? '<span class="rrow-m">' + esc(r.description) + '</span>' : '') + '</span>' +
          '<span class="chip ' + (r.recurrence !== 'once' ? 'chip-blue' : 'chip-gray') + '">' + esc(r.recurrence || 'once') + '</span>' +
          '<span class="chip ' + (isDue ? 'chip-orange' : 'chip-line') + '">' + esc(r.remind_at_label || '') + '</span>' +
          '<button class="icon-btn-sm" data-rdel="' + r.id + '" title="Delete">' + I.trash + '</button>' +
        '</div>';
      }).join('') : '<div class="empty">' + I.alarm + '<div class="empty-title">No reminders</div></div>';
      host.querySelectorAll('[data-rdel]').forEach(function (b) {
        b.addEventListener('click', function () {
          var row = b.closest('.rrow');
          var t = row ? row.querySelector('.rrow-t').textContent : 'This reminder';
          confirmBox('Delete reminder?', '“' + t + '” will be removed.', 'Delete', true).then(function (yes) {
            if (!yes) return;
            b.disabled = true;
            api('POST', '/staff/reminders/' + b.getAttribute('data-rdel') + '/delete').then(function (r) {
              if (r && r.ok) { toast('Reminder deleted.', 'ok'); loadReminders(); }
              else { b.disabled = false; if (r) toast(r.message || 'Could not delete.', 'err'); }
            });
          });
        });
      });
    });
  }

  /* --------------------------------------------------------------- calendar */

  var cal = { year: null, month: null, selDay: null, events: [], reminders: [] };

  function vCalendar() {
    var now = new Date();
    if (cal.year === null) { cal.year = now.getFullYear(); cal.month = now.getMonth(); cal.selDay = ymd(now); }
    view.innerHTML =
      viewHead('Calendar', 'Your events and reminders.') +
      '<div class="view-body"><div class="cal-wrap">' +
        '<div class="panel"><div class="cal-head">' +
          '<button class="cal-nav" id="calPrev">‹</button>' +
          '<div class="cal-title" id="calTitle"></div>' +
          '<button class="btn btn-ghost" id="calToday" style="padding:4px 10px">Today</button>' +
          '<button class="cal-nav" id="calNext">›</button>' +
        '</div><div class="cal-grid" id="calGrid"></div></div>' +
        '<div class="panel"><div class="panel-head"><span id="calDayTitle">Events</span>' +
          '<button class="btn btn-ghost" id="calNew" style="padding:2px 8px;font-size:11px;margin-left:auto">New event</button></div>' +
          '<div class="panel-body" id="calEvForm" hidden style="border-bottom:1px solid var(--border)">' +
            '<input class="f-input" id="ceTitle" placeholder="Event title">' +
            '<div style="display:flex;gap:6px;margin-top:6px">' +
              '<input class="f-input" type="date" id="ceDate" style="flex:1">' +
              '<input class="f-input" type="time" id="ceStart" style="width:92px">' +
              '<input class="f-input" type="time" id="ceEnd" style="width:92px">' +
            '</div>' +
            '<label style="display:flex;gap:6px;margin-top:8px;font-size:12px;align-items:center;color:var(--text-subtle)"><input type="checkbox" id="ceAllDay"> All day</label>' +
            '<div style="display:flex;gap:6px;margin-top:8px">' +
              '<button class="btn btn-primary" id="ceSave" style="flex:1">Save event</button>' +
              '<button class="btn btn-ghost" id="ceCancel">Cancel</button>' +
            '</div>' +
          '</div>' +
          '<div class="panel-body flush" id="calDayList"></div></div>' +
      '</div></div>';

    document.getElementById('calPrev').addEventListener('click', function () { shiftMonth(-1); });
    document.getElementById('calNext').addEventListener('click', function () { shiftMonth(1); });
    document.getElementById('calToday').addEventListener('click', function () { var n = new Date(); cal.year = n.getFullYear(); cal.month = n.getMonth(); cal.selDay = ymd(n); loadCalendar(); });
    document.getElementById('ceTitle').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); saveCalEvent(); } });
    document.getElementById('ceAllDay').addEventListener('change', function () {
      document.getElementById('ceStart').disabled = this.checked;
      document.getElementById('ceEnd').disabled = this.checked;
    });
    cal.editing = null;
    document.getElementById('calNew').addEventListener('click', function () { openCalForm(null); });
    document.getElementById('ceCancel').addEventListener('click', function () { document.getElementById('calEvForm').hidden = true; cal.editing = null; });
    document.getElementById('ceSave').addEventListener('click', saveCalEvent);
    loadCalendar();
  }

  function openCalForm(ev) {
    var f = document.getElementById('calEvForm');
    if (!f) return;
    cal.editing = ev ? ev.id : null;
    document.getElementById('ceTitle').value = ev ? (ev.title || '') : '';
    document.getElementById('ceDate').value = ev ? String(ev.start || '').slice(0, 10) : (cal.selDay || ymd());
    document.getElementById('ceStart').value = ev && !ev.all_day ? String(ev.start || '').slice(11, 16) : '09:00';
    document.getElementById('ceEnd').value = ev && !ev.all_day ? String(ev.end || '').slice(11, 16) : '10:00';
    document.getElementById('ceAllDay').checked = ev ? !!ev.all_day : false;
    document.getElementById('ceStart').disabled = document.getElementById('ceEnd').disabled = document.getElementById('ceAllDay').checked;
    document.getElementById('ceSave').textContent = ev ? 'Save changes' : 'Save event';
    f.hidden = false;
    document.getElementById('ceTitle').focus();
  }

  function saveCalEvent() {
    var title = document.getElementById('ceTitle').value.trim();
    var date = document.getElementById('ceDate').value;
    var allDay = document.getElementById('ceAllDay').checked;
    if (!title || !date) { toast('Title and date are required.', 'warn'); return; }
    if (!allDay && (document.getElementById('ceEnd').value || '10:00') <= (document.getElementById('ceStart').value || '09:00')) {
      toast('End time must be after the start time.', 'warn'); return;
    }
    var body = { title: title, all_day: allDay ? 1 : 0 };
    if (allDay) { body.start = date; body.end = date; }
    else {
      body.start = date + ' ' + (document.getElementById('ceStart').value || '09:00') + ':00';
      body.end = date + ' ' + (document.getElementById('ceEnd').value || '10:00') + ':00';
    }
    var url = cal.editing ? '/staff/calendar/events/' + cal.editing + '/update' : '/staff/calendar/events/create';
    var sb = document.getElementById('ceSave');
    busy(sb, true, 'Saving…');
    api('POST', url, body).then(function (r) {
      busy(sb, false);
      if (r && r.ok) {
        cal.selDay = date;
        toast(cal.editing ? 'Event updated.' : 'Event added.', 'ok');
        document.getElementById('calEvForm').hidden = true;
        cal.editing = null;
        loadCalendar();
      } else if (r) toast(r.message || 'Could not save event.', 'err');
    });
  }

  function shiftMonth(d) {
    cal.month += d;
    if (cal.month < 0) { cal.month = 11; cal.year--; }
    if (cal.month > 11) { cal.month = 0; cal.year++; }
    var now = new Date();
    cal.selDay = (cal.year === now.getFullYear() && cal.month === now.getMonth())
      ? ymd(now) : cal.year + '-' + pad2(cal.month + 1) + '-01';
    loadCalendar();
  }

  function loadCalendar() {
    var first = cal.year + '-' + pad2(cal.month + 1) + '-01';
    var last = ymd(new Date(cal.year, cal.month + 1, 0));
    var mine = (cal.req = (cal.req || 0) + 1);
    renderCalGrid(); // paint the month immediately; events fill in when loaded
    Promise.all([
      api('GET', '/staff/calendar/events?from=' + first + '&to=' + last),
      api('GET', '/staff/reminders?from=' + first + '&to=' + last),
    ]).then(function (rs) {
      if (mine !== cal.req) return;
      cal.events = (rs[0] && rs[0].events) || [];
      cal.reminders = (rs[1] && rs[1].reminders) || [];
      renderCalGrid();
      renderCalDay();
    });
  }

  function calItemsFor(dateStr) {
    var items = [];
    cal.events.forEach(function (e) {
      var s = String(e.start || '').slice(0, 10);
      var en = String(e.end || e.start || '').slice(0, 10);
      if (dateStr >= s && dateStr <= en) items.push({ kind: 'event', color: e.color || '#3788d8', title: e.title, data: e });
    });
    cal.reminders.forEach(function (r) {
      if (String(r.remind_at || '').slice(0, 10) === dateStr) items.push({ kind: 'reminder', color: '#e9a13b', title: r.title, data: r });
    });
    return items;
  }

  function renderCalGrid() {
    var grid = document.getElementById('calGrid');
    var title = document.getElementById('calTitle');
    if (!grid) return;
    var monthName = new Date(cal.year, cal.month, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    if (title) title.textContent = monthName;

    var firstDow = new Date(cal.year, cal.month, 1).getDay();
    var daysInMonth = new Date(cal.year, cal.month + 1, 0).getDate();
    var daysPrev = new Date(cal.year, cal.month, 0).getDate();
    var today = ymd();
    var dows = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    var html = dows.map(function (d) { return '<div class="cal-dow">' + d + '</div>'; }).join('');
    for (var i = 0; i < 42; i++) {
      var dayNum = i - firstDow + 1;
      var other = dayNum < 1 || dayNum > daysInMonth;
      var disp = dayNum < 1 ? daysPrev + dayNum : dayNum > daysInMonth ? dayNum - daysInMonth : dayNum;
      var dstr = cal.year + '-' + String(cal.month + 1).padStart(2, '0') + '-' + String(Math.max(1, Math.min(dayNum, daysInMonth))).padStart(2, '0');
      var items = other ? [] : calItemsFor(dstr);
      html += '<div class="cal-day' + (other ? ' is-other' : '') + (dstr === today ? ' is-today' : '') + (dstr === cal.selDay && !other ? ' is-sel' : '') + '"' + (other ? '' : ' data-day="' + dstr + '"') + '>' +
        '<span class="cal-num">' + disp + '</span>' +
        '<span class="cal-evs">' + items.slice(0, 2).map(function (it) { return '<span class="cal-ev" style="background:' + esc(it.color) + '" title="' + esc(it.title || '') + '">' + esc(it.title || '') + '</span>'; }).join('') + '</span>' +
        (items.length > 2 ? '<span class="cal-dots">' + items.slice(2).map(function (it) { return '<span class="cal-dot" style="background:' + esc(it.color) + '"></span>'; }).join('') + '</span>' : '') +
      '</div>';
    }
    grid.innerHTML = html;
    grid.querySelectorAll('[data-day]').forEach(function (d) {
      d.addEventListener('click', function () { cal.selDay = d.getAttribute('data-day'); renderCalGrid(); renderCalDay(); });
      d.addEventListener('dblclick', function () { cal.selDay = d.getAttribute('data-day'); renderCalDay(); openCalForm(null); });
    });
  }

  function renderCalDay() {
    var host = document.getElementById('calDayList');
    var title = document.getElementById('calDayTitle');
    if (!host) return;
    var items = calItemsFor(cal.selDay);
    if (title) title.textContent = new Date(cal.selDay + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
    host.innerHTML = items.length ? items.map(function (it) {
      var e = it.data;
      var time = it.kind === 'reminder' ? (e.remind_at_label || '') : (e.all_day ? 'All day' : String(e.start || '').slice(11, 16));
      return '<div class="rrow">' +
        '<span class="cal-dot" style="background:' + esc(it.color) + ';flex:0 0 auto;width:9px;height:9px;margin-top:3px"></span>' +
        '<span class="rrow-b"><span class="rrow-t">' + esc(it.title || '(untitled)') + '</span>' +
        '<span class="rrow-m">' + esc(it.kind === 'reminder' ? 'Reminder · ' + time : (time || '') + (e.location ? ' · ' + e.location : '')) + '</span>' +
        (e.description ? '<span class="rrow-m">' + esc(e.description) + '</span>' : '') + '</span>' +
        '<span class="chip ' + (it.kind === 'reminder' ? 'chip-orange' : 'chip-blue') + '">' + it.kind + '</span>' +
        (it.kind === 'event' && e.can_edit ? '<button class="icon-btn-sm" data-cedit="' + e.id + '" title="Edit">' + I.edit + '</button>' : '') +
        (it.kind === 'event' && (e.can_delete || e.can_edit) ? '<button class="icon-btn-sm" data-cdel="' + e.id + '" title="Delete">' + I.trash + '</button>' : '') +
      '</div>';
    }).join('') : '<div class="empty">' + I.cal + '<div class="empty-title">Nothing scheduled</div></div>';
    host.querySelectorAll('[data-cedit]').forEach(function (b) {
      b.addEventListener('click', function () {
        var ev = cal.events.find(function (x) { return String(x.id) === b.getAttribute('data-cedit'); });
        if (ev) openCalForm(ev);
      });
    });
    host.querySelectorAll('[data-cdel]').forEach(function (b) {
      b.addEventListener('click', function () {
        var ev = cal.events.find(function (x) { return String(x.id) === b.getAttribute('data-cdel'); });
        confirmBox('Delete event?', '“' + (ev ? ev.title : 'This event') + '” will be removed from the calendar.', 'Delete', true).then(function (yes) {
          if (!yes) return;
          api('POST', '/staff/calendar/events/' + b.getAttribute('data-cdel') + '/delete', {}).then(function (r) {
            if (r && r.ok) { toast('Event deleted.', 'ok'); loadCalendar(); }
            else if (r) toast(r.message || 'Could not delete event.', 'err');
          });
        });
      });
    });
  }

  /* ------------------------------------------------------------------ goals */

  function goalBar(label, actual, target, pct, money) {
    return '<div class="goal-line">' +
      '<div class="goal-line-head"><span>' + label + '</span><span>' +
        (money ? '₱' + Number(actual).toLocaleString() + ' / ₱' + Number(target).toLocaleString()
               : Number(actual).toLocaleString() + ' / ' + Number(target).toLocaleString()) +
        ' · ' + esc(pct) + '%</span></div>' +
      '<div class="progress"><i style="width:' + Math.min(100, pct) + '%"></i></div></div>';
  }

  function vGoals() {
    view.innerHTML =
      viewHead('Goals', 'Annual company goals and progress.',
        '<button class="btn btn-ghost" id="gRefresh">' + I.refresh + ' Refresh</button>') +
      '<div class="view-body"><div class="grid-2" id="goalBody"><div class="skel skel-block"></div><div class="skel skel-block"></div></div></div>';

    document.getElementById('gRefresh').addEventListener('click', vGoals);
    api('GET', '/staff/annual-goals').then(function (res) {
      var host = document.getElementById('goalBody');
      if (!host) return;
      if (!res) { errorBox(host, 'Could not load goals.', vGoals); return; }
      var c = res.current;
      var html = '<div class="panel"><div class="panel-head">' + (c ? c.year : res.current_year) + ' goal<span class="sub">current year</span></div>' +
        '<div class="panel-body">' +
        (c ?
          goalBar('Clients', c.actual_clients, c.target_clients, c.clients_progress_pct, false) +
          goalBar('Income', c.actual_income, c.target_income, c.income_progress_pct, true) +
          (c.notes ? '<div class="sect"><div class="sect-title">Notes</div><div class="small" style="white-space:pre-wrap;line-height:1.55">' + esc(c.notes) + '</div></div>' : '')
        : '<div class="empty">' + I.goal + '<div class="empty-title">No goal set for ' + res.current_year + '</div></div>') +
        '</div></div>' +
        '<div class="panel"><div class="panel-head">All years<span class="sub">' + (res.goals || []).length + ' goals</span></div>' +
        '<div class="panel-body flush">' +
        ((res.goals || []).length ? res.goals.map(function (g) {
          return '<div class="rrow">' +
            '<span class="rrow-b"><span class="rrow-t">' + esc(g.year) + '</span>' +
            '<span class="rrow-m">Clients ' + esc(g.clients_progress_pct) + '% · Income ' + esc(g.income_progress_pct) + '%</span></span>' +
            '<div class="progress" style="width:90px;flex:0 0 auto;margin-top:0"><i style="width:' + Math.min(100, (Number(g.clients_progress_pct) + Number(g.income_progress_pct)) / 2) + '%"></i></div>' +
          '</div>';
        }).join('') : '<div class="empty">' + I.goal + '<div class="empty-title">No goals yet</div></div>') +
        '</div></div>';
      host.innerHTML = html;

      if (c) {
        api('GET', '/staff/annual-goals/' + res.current_year).then(function (d) {
          if (!d || !d.monthly || !host.isConnected) return;
          var mx = Math.max.apply(null, d.monthly.map(function (m) { return m.clients || 0; }).concat([1]));
          var cur = document.getElementById('goalMonthly');
          if (cur) cur.remove();
          var panel = host.querySelector('.panel .panel-body');
          if (!panel) return;
          panel.insertAdjacentHTML('beforeend',
            '<div class="sect" id="goalMonthly"><div class="sect-title">Monthly clients</div>' +
            '<div class="goal-bars">' + d.monthly.map(function (m) {
              return '<div class="goal-bar-col" title="' + esc(m.label) + ': ' + m.clients + '"><i style="height:' + Math.max(3, Math.round(m.clients / mx * 46)) + 'px"></i><span>' + esc(m.label.slice(0, 1)) + '</span></div>';
            }).join('') + '</div></div>');
        });
      }
    });
  }

  /* ---------------------------------------------------------------- ranking */

  function vRanking() {
    view.innerHTML =
      viewHead('Ranking', 'Task accomplishment leaderboard.',
        '<button class="btn btn-ghost" id="rkRefresh">' + I.refresh + ' Refresh</button>') +
      '<div class="view-body"><div id="rankBody"><div class="skel skel-block"></div><div class="skel skel-block" style="margin-top:12px"></div></div></div>';

    document.getElementById('rkRefresh').addEventListener('click', vRanking);
    api('GET', '/staff/ranking').then(function (res) {
      var host = document.getElementById('rankBody');
      if (!host) return;
      if (!res) { errorBox(host, 'Could not load the ranking.', vRanking); return; }
      var entries = res.entries || [];
      host.innerHTML =
        '<div class="panel"><div class="panel-head">' + esc((res.period || {}).label || 'This period') +
          '<span class="sub">' + (res.total_employees || 0) + ' employees · ' + (res.total_points || 0) + ' pts total</span></div>' +
          '<div class="panel-body flush">' +
          '<div class="rank-strip"><span class="rank-strip-label">Your rank</span>' +
            '<span class="rank-strip-val">' + ((res.current || {}).rank ? '#' + res.current.rank : '—') + '</span>' +
            '<span class="rank-strip-sub">' + ((res.current || {}).points || 0) + ' pts</span></div>' +
          (entries.length ? entries.map(function (e) {
            return '<div class="rrow' + (e.is_current ? ' is-mine' : '') + '">' +
              '<span class="rank-num' + (e.rank <= 3 ? ' rank-' + e.rank : '') + '">' + e.rank + '</span>' +
              '<span class="rrow-b"><span class="rrow-t">' + esc(e.name || 'Unknown') + (e.is_current ? ' · you' : '') + '</span>' +
              '<span class="rrow-m">' + esc(e.role || '') + (e.last_done_label ? ' · last ' + esc(e.last_done_label) : '') + '</span></span>' +
              '<span class="rank-pts">' + e.points + ' pts</span>' +
            '</div>';
          }).join('') : '<div class="empty">' + I.trophy + '<div class="empty-title">No ranking data</div></div>') +
        '</div></div>';
    });
  }

  /* -------------------------------------------------------------- attendance */

  function vAttendance() {
    view.innerHTML =
      viewHead('Attendance', 'Daily time record',
        '<button class="btn btn-ghost" id="attRefresh">' + I.refresh + ' Refresh</button>') +
      '<div class="view-body"><div class="att-page">' +
        '<div class="panel"><div class="clock-card">' +
          '<div class="clock-time" id="clockTime">--:--</div>' +
          '<div class="clock-date" id="clockDate"></div>' +
          '<div class="clock-status" id="clockStatus">Loading…</div>' +
          '<div class="clock-btns">' +
            '<button class="btn btn-success" id="btnIn2" disabled>Time in</button>' +
            '<button class="btn btn-ghost" id="btnOut2" disabled>Time out</button>' +
          '</div>' +
        '</div></div>' +
        '<div class="panel">' +
          '<div class="panel-head"><span class="dtr-nav"><button class="cal-nav" id="dtrPrev" title="Previous month">‹</button>' +
            '<span id="dtrTitle">This month</span><button class="cal-nav" id="dtrNext" title="Next month">›</button></span><span class="sub" id="dtrMonth"></span></div>' +
          '<div class="dtr-summary" id="dtrSummary"><div class="skel" style="flex:1;height:18px"></div></div>' +
          '<div class="dtr-table-wrap"><table class="dtr-table"><thead><tr><th>Date</th><th>AM</th><th>PM</th><th>Total</th><th>Status</th></tr></thead>' +
          '<tbody id="dtrRows"></tbody></table></div>' +
        '</div>' +
      '</div></div>';

    var tick = function () {
      var t = document.getElementById('clockTime');
      var d = document.getElementById('clockDate');
      var now = new Date();
      if (t) t.textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
      if (d) d.textContent = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
    };
    tick(); state.clock = setInterval(tick, 1000);

    document.getElementById('attRefresh').addEventListener('click', vAttendance);
    var nowM = new Date();
    if (!state.dtr) state.dtr = { y: nowM.getFullYear(), m: nowM.getMonth() + 1 };
    var shiftDtr = function (d) {
      var m = state.dtr.m + d, y = state.dtr.y;
      if (m < 1) { m = 12; y--; } if (m > 12) { m = 1; y++; }
      if (y > nowM.getFullYear() || (y === nowM.getFullYear() && m > nowM.getMonth() + 1)) return;
      state.dtr = { y: y, m: m }; loadDtr();
    };
    document.getElementById('dtrPrev').addEventListener('click', function () { shiftDtr(-1); });
    document.getElementById('dtrNext').addEventListener('click', function () { shiftDtr(1); });

    api('GET', '/staff/dashboard').then(function (d) {
      if (!d) return;
      var att = d.attendance || {};
      sbAtt(att);
      var s = document.getElementById('clockStatus');
      if (s) s.textContent = att.notice || att.status_label || '';
      var bin = document.getElementById('btnIn2');
      var bout = document.getElementById('btnOut2');
      if (bin) { bin.disabled = !att.can_time_in; bin.onclick = function () { punch('time-in', bin); }; }
      if (bout) { bout.disabled = !att.can_time_out; bout.onclick = function () { punch('time-out', bout); }; }
      if (s) s.classList.toggle('is-open', !!att.can_time_out);
    });

    loadDtr();
  }

  function loadDtr() {
    var y = state.dtr.y, m = state.dtr.m;
    var nowM = new Date();
    var isCur = y === nowM.getFullYear() && m === nowM.getMonth() + 1;
    var tEl = document.getElementById('dtrTitle');
    if (tEl) tEl.textContent = isCur ? 'This month' : new Date(y, m - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    var nx = document.getElementById('dtrNext');
    if (nx) nx.disabled = isCur;
    var tb = document.getElementById('dtrRows');
    if (tb) tb.innerHTML = '<tr><td colspan="5"><div class="skel" style="height:16px"></div></td></tr>';
    api('GET', '/staff/my-dtr?month=' + m + '&year=' + y).then(function (res) {
      if (y !== state.dtr.y || m !== state.dtr.m) return;
      if (!res) { errorBox(document.getElementById('dtrSummary'), 'Could not load your time record.', loadDtr); var t2 = document.getElementById('dtrRows'); if (t2) t2.innerHTML = ''; return; }
      var sum = document.getElementById('dtrSummary');
      if (sum) {
        sum.innerHTML =
          '<div><div class="dtr-total-num">' + esc(res.month_total_label || '0h 0m') + '</div><div class="dtr-total-label">Hours this month</div></div>' +
          '<div class="dtr-counts">' +
            '<div class="dtr-count"><div class="dtr-count-num" style="color:var(--success)">' + (res.present_days || 0) + '</div><div class="dtr-count-label">Present</div></div>' +
            '<div class="dtr-count"><div class="dtr-count-num" style="color:var(--warning)">' + (res.pending_days || 0) + '</div><div class="dtr-count-label">Pending</div></div>' +
            '<div class="dtr-count"><div class="dtr-count-num" style="color:var(--danger)">' + (res.absent_days || 0) + '</div><div class="dtr-count-label">Absent</div></div>' +
          '</div>';
      }
      var tbody = document.getElementById('dtrRows');
      var today = ymd();
      var rows = (res.rows || []).slice().reverse();
      if (tbody) tbody.innerHTML = rows.map(function (r) {
        var cls = r.status === 'Present' ? 'chip-green' : r.status === 'Pending' ? 'chip-orange' : 'chip-red';
        return '<tr' + (r.date === today ? ' class="is-today"' : '') + '>' +
          '<td>' + esc(fmtDate(r.date)) + '</td>' +
          '<td>' + esc((r.am_breakdown || []).join(' · ') || '—') + '</td>' +
          '<td>' + esc((r.pm_breakdown || []).join(' · ') || '—') + '</td>' +
          '<td>' + esc(r.total_hours || '—') + '</td>' +
          '<td><span class="chip ' + cls + '">' + esc(r.status) + '</span></td></tr>';
      }).join('') || '<tr><td colspan="5" class="muted" style="padding:14px">No records for this month.</td></tr>';
      var mEl = document.getElementById('dtrMonth');
      if (mEl) mEl.textContent = rows.length + ' days';
    });
  }

  /* ----------------------------------------------------------------- alerts */

  function vAlerts() {
    view.innerHTML =
      viewHead('Notifications', 'Task and support updates for you.',
        '<button class="btn btn-ghost" id="markRead">' + I.check + ' Mark all read</button>') +
      '<div class="view-body"><div class="panel"><div class="panel-body flush" id="notifList">' +
      '<div class="skel skel-row" style="margin:10px 14px"></div><div class="skel skel-row" style="margin:10px 14px"></div></div></div></div>';

    document.getElementById('markRead').addEventListener('click', function () {
      var b = this;
      busy(b, true);
      api('POST', '/staff/notifications/mark-seen').then(function () { busy(b, false); loadNotifs(); refreshBadge(); });
    });
    if (state.notifs) paintNotifs(state.notifs); // cached first, then revalidate
    loadNotifs();
  }

  function loadNotifs() {
    api('GET', '/staff/notifications?limit=50').then(function (res) {
      if (!res) { errorBox(document.getElementById('notifList'), 'Could not load notifications.', loadNotifs); return; }
      paintNotifs(res);
    });
  }

  function paintNotifs(res) {
      state.notifs = res;
      updateBadge(res.unseen_total || 0);
      var host = document.getElementById('notifList');
      if (!host) return;
      var items = res.notifications || [];
      host.innerHTML = items.length ? items.map(function (n, idx) {
        var linked = (n.source === 'support' && n.issue_id) || n.task_id;
        return '<div class="notif' + (n.is_seen ? '' : ' is-unseen') + (linked ? ' is-link' : '') + '" data-ni="' + idx + '"' + (linked ? ' role="button" tabindex="0"' : '') + '>' +
          '<div class="notif-ico ' + (n.source === 'support' ? 'support' : 'task') + '">' + (n.source === 'support' ? I.lifebuoy : I.box) + '</div>' +
          '<div class="notif-body">' +
            '<div class="notif-title">' + esc(n.title || 'Notification') + '</div>' +
            (n.message ? '<div class="notif-msg">' + esc(n.message) + '</div>' : '') +
            '<div class="notif-meta"><span>' + esc(n.actor_name || '') + '</span><span class="dotsep">·</span><span>' + esc(n.created_label || '') + '</span>' +
            (n.ticket_number ? '<span class="chip chip-line">' + esc(n.ticket_number) + '</span>' : '') + '</div>' +
          '</div>' + (linked ? '<span class="chev">›</span>' : '') + '</div>';
      }).join('') : '<div class="empty">' + I.bell + '<div class="empty-title">All caught up</div></div>';
      bindNotifRows(host, items);
  }

  function openNotif(n) {
    if (n) openTarget(notifTarget(n));
  }

  function bindNotifRows(host, items) {
    host.querySelectorAll('[data-ni]').forEach(function (row) {
      var n = items[+row.getAttribute('data-ni')];
      if (!n || !((n.source === 'support' && n.issue_id) || n.task_id) && row.tagName !== 'BUTTON') return;
      row.addEventListener('click', function () { openNotif(n); });
      row.addEventListener('keydown', function (e) { if (e.key === 'Enter') openNotif(n); });
    });
  }

  function updateBadge(n) {
    n = +n || 0;
    var b = document.getElementById('navBadge');
    if (b) { b.hidden = !n; b.textContent = n > 99 ? '99+' : n; }
    sbUnread(n);
    if (B.setBadge) B.setBadge(n);
  }

  function refreshBadge() {
    api('GET', '/staff/notifications?limit=1').then(function (res) {
      if (res) updateBadge(res.unseen_total || 0);
    });
  }

  // Background poll: badge stays fresh and genuinely new notifications arrive
  // as native toasts that open the actual task/ticket when clicked. Toasts are
  // skipped while the app is focused — the badge + status bar already show it.
  var notifSeen = { primed: false, ids: new Set() };

  function notifTarget(n) {
    if (n.task_id) return { type: 'task', id: n.task_id };
    if (n.source === 'support' && n.issue_id) return { type: 'ticket', id: n.issue_id };
    return { type: 'alerts' };
  }

  function pollNotifs() {
    if (!state.user) return;
    api('GET', '/staff/notifications?limit=10').then(function (res) {
      if (!res) return;
      state.notifs = res;
      updateBadge(res.unseen_total || 0);
      var items = res.notifications || [];
      if (!notifSeen.primed) {
        items.forEach(function (n) { notifSeen.ids.add(String(n.id)); });
        notifSeen.primed = true;
        return;
      }
      var fresh = items.filter(function (n) { return !n.is_seen && !notifSeen.ids.has(String(n.id)); });
      items.forEach(function (n) { notifSeen.ids.add(String(n.id)); });
      if (notifSeen.ids.size > 300) notifSeen.ids = new Set(Array.from(notifSeen.ids).slice(-100));
      if (document.hasFocus()) return;
      fresh.slice(0, 2).forEach(function (n) {
        if (B.notify) B.notify(n.title || 'BERPS Desk', n.message || '', '#/alerts', notifTarget(n));
      });
    });
  }

  /* --------------------------------------------------------------- settings */

  function vSettings() {
    var u = state.user || {};
    view.innerHTML =
      viewHead('Settings') +
      '<div class="view-body" style="max-width:640px">' +
        '<div class="panel"><div class="panel-head">Account</div><div class="panel-body flush">' +
          '<div class="profile-row">' +
            '<div class="profile-avatar" id="setAvatar">' + esc(initials()) + '</div>' +
            '<div><div class="profile-name">' + esc(u.full_name || u.username || '') + '</div>' +
            '<div class="profile-sub">' + esc(u.position || 'Staff') + ' · @' + esc(u.username || '') + '</div></div>' +
          '</div>' +
          '<div class="set-row"><span class="k">Email</span><span class="v">' + esc(u.email || '—') + '</span></div>' +
          '<div class="set-row"><span class="k">Username</span><span class="v">' + esc(u.username || '—') + '</span></div>' +
        '</div></div>' +
        '<div class="panel"><div class="panel-head">Connection</div><div class="panel-body flush">' +
          '<div class="set-row"><span class="k">Server</span><span class="v">' + esc(state.server) + '</span></div>' +
          '<div class="set-row"><span class="k">Session</span><span class="v" id="sessState">Checking…</span>' +
          '<button class="btn btn-ghost" id="reAuth" style="padding:4px 10px">Verify</button></div>' +
        '</div></div>' +
        '<div class="panel"><div class="panel-head">Desktop</div><div class="panel-body flush">' +
          '<div class="set-row"><span class="k">Launch at login</span><span class="v muted">Open BERPS Desk when you sign in to this computer.</span>' +
          '<input type="checkbox" class="set-check" id="setLoginItem"></div>' +
          '<div class="set-row" id="setTouchIdRow" hidden><span class="k">Touch ID</span><span class="v muted">Ask for Touch ID when BERPS Desk opens with a saved session.</span>' +
          '<input type="checkbox" class="set-check" id="setTouchId"></div>' +
          '<div class="set-row"><span class="k">Summon hotkey</span><span class="v muted">Show or hide BERPS Desk from anywhere.</span>' +
          '<span class="v" id="setHotkey"></span></div>' +
        '</div></div>' +
        '<div class="panel"><div class="panel-head">Web portal</div><div class="panel-body flush">' +
          '<div class="set-row"><span class="k">Full portal</span><span class="v muted">Projects, invoices, payroll and everything else not in this app. Return with ⌘⇧D or the “← BERPS Desk” button.</span>' +
          '<button class="btn btn-ghost" id="openPortal">Open portal</button></div>' +
        '</div></div>' +
        '<div class="panel"><div class="panel-body flush">' +
          '<div class="set-row"><span class="k">Sign out</span><span class="v muted">End this session on this device.</span>' +
          '<button class="btn btn-danger-soft" id="logoutBtn">' + I.logout + ' Sign out</button></div>' +
        '</div></div>' +
        '<div class="app-foot">BERPS Desk ' + esc(B.version || '') + ' · native desktop</div>' +
      '</div>';

    var av = u.avatar_url;
    if (av) document.getElementById('setAvatar').style.backgroundImage = 'url(' + av + ')';
    document.getElementById('reAuth').addEventListener('click', verifySession);
    document.getElementById('openPortal').addEventListener('click', function () { B.portal.open(); });
    document.getElementById('setLoginItem').addEventListener('change', function () {
      if (B.setLoginItem) B.setLoginItem(this.checked);
    });
    document.getElementById('setTouchId').addEventListener('change', function () {
      if (B.setSetting) B.setSetting('touchId', this.checked);
    });
    if (B.settings) B.settings().then(function (p) {
      p = p || {};
      var li = document.getElementById('setLoginItem');
      if (li) li.checked = !!p.openAtLogin;
      var tir = document.getElementById('setTouchIdRow');
      if (tir) tir.hidden = !p.touchIdSupported;
      var ti = document.getElementById('setTouchId');
      if (ti) ti.checked = !!p.touchId;
      var hk = document.getElementById('setHotkey');
      if (hk) hk.textContent = p.summonHotkey || '—';
    });
    verifySession();
    document.getElementById('logoutBtn').addEventListener('click', function () {
      confirmBox('Sign out?', 'You will need your username and password to sign back in.', 'Sign out', true).then(function (yes) {
        if (!yes) return;
        B.auth.logout().then(function () { updateBadge(0); history.replaceState(null, '', '#/home'); showLogin(); });
      });
    });
  }

  function verifySession() {
    var el = document.getElementById('sessState');
    B.auth.me().then(function (res) {
      if (res && res.ok) {
        if (el) { el.textContent = 'Active'; el.style.color = 'var(--success)'; }
      } else if (res && (res.status === 401 || res.status === 403)) {
        showLogin('Your session expired. Please sign in again.');
      } else if (el) {
        el.textContent = 'Could not reach the server'; el.style.color = 'var(--warning)';
      }
    });
  }

  /* ------------------------------------------------------------------ boot */

  document.body.classList.add('is-logged-out');
  sbInit();

  function loadPrefs() {
    if (!B.settings) { state.prefs = {}; return Promise.resolve(); }
    return B.settings()
      .then(function (p) { state.prefs = p || {}; })
      .catch(function () { state.prefs = {}; });
  }

  function enterApp(s) {
    if (s && s.user) state.user = s.user;
    return B.auth.me().then(function (res) {
      if (res && res.ok) { state.user = res.user; showApp(); pollNotifs(); }
      else showLogin();
    });
  }

  loadPrefs().then(function () {
    return B.auth.state();
  }).then(function (s) {
    state.server = (s && s.server) || '';
    updateConnUI(true);
    if (!(s && s.loggedIn)) { showLogin(); return; }
    // Saved session + Touch ID enabled → gate entry behind biometrics.
    // Cancelling lands on the normal sign-in form with a retry button.
    if (state.prefs.touchId && state.prefs.touchIdSupported && B.unlock) {
      return B.unlock().then(function (u) {
        if (u && u.ok) return enterApp(s);
        showLogin('Unlock BERPS Desk with Touch ID, or sign in again.');
      });
    }
    return enterApp(s);
  }).catch(function () { showLogin('Cannot reach the app services.'); });

  setInterval(pollNotifs, 60000);
})();
