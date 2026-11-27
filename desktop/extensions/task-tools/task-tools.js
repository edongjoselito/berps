/* Task Tools — quick actions on BERPS task pages.
 *
 * - Adds a "Close" quick action next to every "Modify Status" control on task
 *   list pages. It posts the same form the status modal submits to
 *   Page/addTaskNote (dataid, taskStat=0, note) and marks the row closed in
 *   place — no full reload.
 * - Alt+T anywhere opens the task list.
 *
 * Runs in an isolated world: full DOM access, no jQuery. Must be idempotent —
 * it may re-run after in-page navigation and after DataTables redraws.
 */

if (window.__berpsTaskTools) {
  // already initialized on this document
} else {
window.__berpsTaskTools = true;

var SRC_FLAG = 'data-berps-close-src'; // marks a status trigger already processed
var CLOSE_NOTE = 'Closed via Task Tools';

function baseUrl() {
  var meta = document.querySelector('meta[name="berps-base-url"]');
  return (meta && meta.content) ? meta.content : location.origin + '/';
}

function toast(message, kind) {
  var host = document.getElementById('berps-task-toast');
  if (!host) {
    host = document.createElement('div');
    host.id = 'berps-task-toast';
    document.body.appendChild(host);
  }
  var el = document.createElement('div');
  el.className = 'berps-task-toast__item berps-task-toast__item--' + (kind || 'info');
  el.textContent = message;
  host.appendChild(el);
  setTimeout(function () { el.classList.add('is-in'); }, 10);
  setTimeout(function () {
    el.classList.remove('is-in');
    setTimeout(function () { el.remove(); }, 250);
  }, 3200);
}

function isStatusTrigger(el) {
  var target = (el.getAttribute('data-target') || '') + (el.getAttribute('href') || '');
  return /status/i.test(target);
}

function markRowClosed(statusEl) {
  var row = statusEl.closest('tr') || statusEl.closest('li') || statusEl.closest('.task-row');
  if (!row) return;
  var badge = row.querySelector('.badge[class*="badge-"]');
  if (badge && /open/i.test(badge.textContent)) {
    badge.className = badge.className.replace(/badge-\w+/g, '').trim();
    badge.classList.add('badge', 'badge-secondary');
    badge.textContent = 'Closed';
  }
  row.classList.add('berps-row-closed');
  statusEl.setAttribute('data-berps-closed', '1'); // don't re-offer the action
  var btn = row.querySelector('.berps-task-close[data-task-id="' +
    statusEl.getAttribute('data-task-id') + '"]');
  if (btn) btn.remove();
}

// Styled confirm — window.confirm renders a bare browser dialog and reads
// as "webpage inside an app".
function confirmClose() {
  return new Promise(function (resolve) {
    var host = document.createElement('div');
    host.id = 'berps-tt-confirm';
    host.innerHTML =
      '<div class="btt-backdrop"></div>' +
      '<div class="btt-card" role="dialog" aria-modal="true" aria-label="Close task">' +
        '<div class="btt-title">Close task?</div>' +
        '<div class="btt-msg">It will be marked as closed and recorded in the task history.</div>' +
        '<div class="btt-actions">' +
          '<button type="button" class="btt-btn btt-cancel">Cancel</button>' +
          '<button type="button" class="btt-btn btt-ok">Close task</button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(host);
    var done = function (yes) {
      document.removeEventListener('keydown', onKey, true);
      host.remove();
      resolve(yes);
    };
    var onKey = function (e) {
      if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); done(false); }
      else if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); done(true); }
    };
    document.addEventListener('keydown', onKey, true);
    host.querySelector('.btt-backdrop').addEventListener('click', function () { done(false); });
    host.querySelector('.btt-cancel').addEventListener('click', function () { done(false); });
    host.querySelector('.btt-ok').addEventListener('click', function () { done(true); });
    host.querySelector('.btt-ok').focus();
  });
}

function closeTask(statusEl) {
  var taskId = statusEl.getAttribute('data-task-id');
  if (!taskId) return;
  confirmClose().then(function (yes) {
    if (!yes) return;
    postClose(statusEl, taskId);
  });
}

function postClose(statusEl, taskId) {
  var body = new URLSearchParams({
    dataid: taskId,
    note: CLOSE_NOTE,
    taskStat: '0',
    add_task_stat: '1'
  });

  fetch(baseUrl() + 'Page/addTaskNote', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body: body.toString()
  }).then(function (res) {
    return res.text().then(function (html) {
      if (/time in first|need to time in/i.test(html)) {
        toast('Time in first before closing tasks.', 'warn');
        return;
      }
      markRowClosed(statusEl);
      toast('Task closed.', 'ok');
    });
  }).catch(function () {
    toast('Could not close task — check your connection.', 'err');
  });
}

function makeCloseControl(statusEl) {
  var inMenu = !!statusEl.closest('.dropdown-menu');
  var btn = document.createElement('a');
  btn.href = 'javascript:void(0)';
  btn.className = inMenu ? 'dropdown-item berps-task-close berps-task-close--menu'
                         : 'berps-task-close';
  btn.setAttribute('data-task-id', statusEl.getAttribute('data-task-id'));
  btn.setAttribute('title', 'Close task');
  btn.setAttribute('aria-label', 'Close task');
  btn.textContent = 'Close task';

  statusEl.insertAdjacentElement('afterend', btn);
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    closeTask(statusEl);
  });
}

function decorate() {
  var triggers = document.querySelectorAll('[data-task-id][data-toggle="modal"]:not([' + SRC_FLAG + '])');
  for (var i = 0; i < triggers.length; i++) {
    var el = triggers[i];
    el.setAttribute(SRC_FLAG, '1');
    if (!isStatusTrigger(el) || el.hasAttribute('data-berps-closed')) continue;
    var taskId = el.getAttribute('data-task-id');
    var existing = el.parentNode &&
      el.parentNode.querySelector('.berps-task-close[data-task-id="' + taskId + '"]');
    if (!existing) makeCloseControl(el);
  }
}

var scheduled = false;
function scheduleDecorate() {
  if (scheduled) return;
  scheduled = true;
  setTimeout(function () { scheduled = false; decorate(); }, 180);
}

decorate();
new MutationObserver(scheduleDecorate)
  .observe(document.body, { childList: true, subtree: true });

// Alt+T → task list
document.addEventListener('keydown', function (e) {
  if (e.altKey && !e.ctrlKey && !e.metaKey && String(e.key).toLowerCase() === 't') {
    e.preventDefault();
    location.href = baseUrl() + 'Page/projectAddTask';
  }
});

} // end __berpsTaskTools guard
