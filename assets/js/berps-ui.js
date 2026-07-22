(function () {
  'use strict';

  var dialog;
  var titleElement;
  var messageElement;
  var confirmButton;
  var cancelButton;
  var activeTrigger = null;
  var pendingAction = null;

  function createDialog() {
    if (dialog) {
      return;
    }

    dialog = document.createElement('div');
    dialog.className = 'berps-confirm-dialog';
    dialog.hidden = true;
    dialog.innerHTML =
      '<div class="berps-confirm-dialog__backdrop" data-berps-confirm-cancel></div>' +
      '<div class="berps-confirm-dialog__panel" role="alertdialog" aria-modal="true" aria-labelledby="berps-confirm-title" aria-describedby="berps-confirm-message">' +
        '<div class="berps-confirm-dialog__body">' +
          '<span class="berps-confirm-dialog__icon" aria-hidden="true"><i class="mdi mdi-alert-outline"></i></span>' +
          '<h2 class="berps-confirm-dialog__title" id="berps-confirm-title"></h2>' +
          '<p class="berps-confirm-dialog__message" id="berps-confirm-message"></p>' +
        '</div>' +
        '<div class="berps-confirm-dialog__actions">' +
          '<button type="button" class="btn btn-outline-secondary" data-berps-confirm-cancel>Cancel</button>' +
          '<button type="button" class="btn btn-danger" data-berps-confirm-accept>Delete</button>' +
        '</div>' +
      '</div>';

    document.body.appendChild(dialog);
    titleElement = dialog.querySelector('#berps-confirm-title');
    messageElement = dialog.querySelector('#berps-confirm-message');
    confirmButton = dialog.querySelector('[data-berps-confirm-accept]');
    cancelButton = dialog.querySelector('.berps-confirm-dialog__actions [data-berps-confirm-cancel]');

    dialog.addEventListener('click', function (event) {
      if (event.target.closest('[data-berps-confirm-cancel]')) {
        closeDialog();
      }
    });

    confirmButton.addEventListener('click', function () {
      var action = pendingAction;
      closeDialog(false);
      if (typeof action === 'function') {
        action();
      }
    });
  }

  function closeDialog(restoreFocus) {
    if (!dialog || dialog.hidden) {
      return;
    }

    dialog.hidden = true;
    document.body.classList.remove('berps-dialog-open');
    pendingAction = null;

    if (restoreFocus !== false && activeTrigger && typeof activeTrigger.focus === 'function') {
      activeTrigger.focus();
    }
  }

  function openDialog(trigger, action, focusTarget) {
    createDialog();
    activeTrigger = focusTarget || trigger;
    pendingAction = action;
    var tone = trigger.getAttribute('data-berps-confirm-tone') === 'primary' ? 'primary' : 'danger';
    titleElement.textContent = trigger.getAttribute('data-berps-confirm-title') || 'Confirm action';
    messageElement.textContent = trigger.getAttribute('data-berps-confirm') || 'Are you sure you want to continue?';
    confirmButton.textContent = trigger.getAttribute('data-berps-confirm-label') || 'Delete';
    confirmButton.className = 'btn btn-' + tone;
    dialog.setAttribute('data-tone', tone);
    dialog.hidden = false;
    document.body.classList.add('berps-dialog-open');
    window.setTimeout(function () {
      cancelButton.focus();
    }, 0);
  }

  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('a[data-berps-confirm]');
    if (!trigger) {
      return;
    }

    event.preventDefault();
    openDialog(trigger, function () {
      window.location.assign(trigger.href);
    });
  });

  document.addEventListener('submit', function (event) {
    var form = event.target.closest('form[data-berps-confirm]');
    if (!form || form.getAttribute('data-berps-confirm-approved') === 'true') {
      return;
    }

    event.preventDefault();
    var submitter = event.submitter || null;
    openDialog(form, function () {
      form.setAttribute('data-berps-confirm-approved', 'true');
      if (typeof form.requestSubmit === 'function') {
        if (submitter && submitter.form === form) {
          form.requestSubmit(submitter);
        } else {
          form.requestSubmit();
        }
      } else {
        if (submitter && submitter.name) {
          var submitterValue = document.createElement('input');
          submitterValue.type = 'hidden';
          submitterValue.name = submitter.name;
          submitterValue.value = submitter.value;
          form.appendChild(submitterValue);
        }
        form.submit();
      }
    }, submitter || form);
  });

  document.addEventListener('keydown', function (event) {
    if (!dialog || dialog.hidden) {
      return;
    }

    if (event.key === 'Escape') {
      event.preventDefault();
      closeDialog();
      return;
    }

    if (event.key === 'Tab') {
      var focusable = [cancelButton, confirmButton];
      var currentIndex = focusable.indexOf(document.activeElement);
      var nextIndex = event.shiftKey ? currentIndex - 1 : currentIndex + 1;
      if (nextIndex < 0) {
        nextIndex = focusable.length - 1;
      }
      if (nextIndex >= focusable.length) {
        nextIndex = 0;
      }
      event.preventDefault();
      focusable[nextIndex].focus();
    }
  });
})();

/* Chrome: sidebar toggle (desktop collapse + mobile drawer), topbar title fill. */
(function () {
  'use strict';

  var DESKTOP_MIN_WIDTH = 992;

  function closeSidebar() {
    document.body.classList.remove('berps-sidebar-open');
  }

  document.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-berps-sidebar-toggle]');
    if (toggle) {
      event.preventDefault();
      if (window.innerWidth >= DESKTOP_MIN_WIDTH) {
        document.body.classList.toggle('berps-sidebar-collapsed');
      } else {
        document.body.classList.toggle('berps-sidebar-open');
      }
      return;
    }

    if (event.target.closest('[data-berps-sidebar-close]')) {
      closeSidebar();
      return;
    }

    if (
      document.body.classList.contains('berps-sidebar-open') &&
      event.target.closest('#side-menu a[href]:not([href^="javascript"])')
    ) {
      closeSidebar();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeSidebar();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    // Fill the topbar title from the page heading when no $page_title was set.
    var titleTarget = document.getElementById('berps-topbar-title');
    if (titleTarget && titleTarget.textContent.trim() === '') {
      var heading = document.querySelector('.content-page .berps-page-title, .content-page .page-title');
      if (heading) {
        titleTarget.textContent = heading.textContent.trim();
      }
    }
  });
})();
