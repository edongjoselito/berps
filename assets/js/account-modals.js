(function () {
    'use strict';

    var activeModal = null;
    var activeTrigger = null;
    var closeTimer = null;
    var allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
    var maximumImageSize = 2 * 1024 * 1024;

    function findModal(name) {
        return document.querySelector('[data-account-modal="' + name + '"]');
    }

    function dismissProfileDropdown(trigger) {
        if (!trigger) {
            return;
        }

        var dropdown = trigger.closest('.dropdown');

        if (!dropdown) {
            return;
        }

        dropdown.classList.remove('show');
        var menu = dropdown.querySelector('.dropdown-menu');
        var toggle = dropdown.querySelector('.dropdown-toggle');
        if (menu) {
            menu.classList.remove('show');
        }
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    }

    function openModal(name, trigger) {
        var modal = findModal(name);
        if (!modal) {
            return false;
        }

        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        if (activeModal && activeModal !== modal) {
            closeModal(activeModal, false);
        }

        activeModal = modal;
        activeTrigger = trigger || null;
        dismissProfileDropdown(trigger);
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('berps-account-modal-open');

        window.requestAnimationFrame(function () {
            modal.classList.add('is-open');
            var focusTarget = modal.querySelector('input:not([type="file"]), [data-profile-photo-dropzone], button');
            if (focusTarget) {
                focusTarget.focus();
            }
        });

        return true;
    }

    function closeModal(modal, restoreFocus) {
        if (!modal) {
            return;
        }

        resetModalForm(modal);
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('berps-account-modal-open');

        closeTimer = window.setTimeout(function () {
            modal.hidden = true;
            closeTimer = null;
        }, 190);

        if (restoreFocus !== false && activeTrigger) {
            activeTrigger.focus();
        }

        activeModal = null;
        activeTrigger = null;
    }

    function resetModalForm(modal) {
        var form = modal.querySelector('[data-account-form]');
        if (!form || form.querySelector('[data-account-submit]:disabled')) {
            return;
        }

        form.reset();
        clearFeedback(form);

        if (form.getAttribute('data-account-form') === 'password') {
            updatePasswordStrength(form.querySelector('[data-new-password]'));
            resetPasswordVisibility(form);
        }

        if (form.getAttribute('data-account-form') === 'profile-photo') {
            var preview = form.querySelector('[data-profile-photo-preview]');
            var label = form.querySelector('[data-profile-photo-label]');
            if (preview && preview.getAttribute('data-saved-src')) {
                preview.src = preview.getAttribute('data-saved-src');
            }
            if (label) {
                label.textContent = 'Choose a new photo';
            }
        }
    }

    function clearFeedback(form) {
        var feedback = form.querySelector('[data-account-feedback]');
        if (feedback) {
            feedback.hidden = true;
            feedback.className = 'berps-account-feedback';
            feedback.textContent = '';
        }

        Array.prototype.forEach.call(form.querySelectorAll('.is-invalid'), function (element) {
            element.classList.remove('is-invalid');
        });
    }

    function showFeedback(form, type, message, errors) {
        var feedback = form.querySelector('[data-account-feedback]');
        if (!feedback) {
            return;
        }

        feedback.textContent = '';
        feedback.className = 'berps-account-feedback is-' + type;
        feedback.hidden = false;

        var icon = document.createElement('i');
        icon.className = type === 'success' ? 'ph ph-check-circle' : 'ph ph-warning-circle';
        icon.setAttribute('aria-hidden', 'true');

        var content = document.createElement('div');
        var summary = document.createElement('div');
        summary.textContent = message;
        content.appendChild(summary);

        var errorMessages = errors ? Object.keys(errors).map(function (key) {
            return errors[key];
        }).filter(Boolean) : [];

        if (errorMessages.length) {
            var list = document.createElement('ul');
            errorMessages.forEach(function (error) {
                var item = document.createElement('li');
                item.textContent = error;
                list.appendChild(item);
            });
            content.appendChild(list);
        }

        feedback.appendChild(icon);
        feedback.appendChild(content);
    }

    function markInvalidFields(form, errors) {
        if (!errors) {
            return;
        }

        Object.keys(errors).forEach(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            if (!field) {
                return;
            }

            var wrapper = field.closest('.berps-account-input') || field.closest('.berps-photo-dropzone');
            if (wrapper) {
                wrapper.classList.add('is-invalid');
            }
        });
    }

    function setSubmitting(form, isSubmitting) {
        var button = form.querySelector('[data-account-submit]');
        if (!button) {
            return;
        }

        var icon = button.querySelector('i');
        var label = button.querySelector('span');

        if (!button.hasAttribute('data-default-label') && label) {
            button.setAttribute('data-default-label', label.textContent);
        }
        if (!button.hasAttribute('data-default-icon') && icon) {
            button.setAttribute('data-default-icon', icon.className);
        }

        button.disabled = isSubmitting;
        button.classList.toggle('is-loading', isSubmitting);

        if (label) {
            label.textContent = isSubmitting ? 'Saving…' : button.getAttribute('data-default-label');
        }
        if (icon) {
            icon.className = isSubmitting ? 'ph ph-circle-notch' : button.getAttribute('data-default-icon');
        }
    }

    function validatePasswordForm(form) {
        var current = form.querySelector('[name="currentpassword"]');
        var password = form.querySelector('[name="newpassword"]');
        var confirmation = form.querySelector('[name="cnewpassword"]');
        var errors = {};

        if (!current.value) {
            errors.currentpassword = 'Enter your current password.';
        }
        if (!password.value) {
            errors.newpassword = 'Enter a new password.';
        } else if (password.value.length < 8) {
            errors.newpassword = 'Your new password must be at least 8 characters long.';
        } else if (!/^[a-zA-Z0-9!@#$%^&*]+$/.test(password.value)) {
            errors.newpassword = 'Use only letters, numbers, and the allowed symbols: ! @ # $ % ^ & *.';
        }
        if (!confirmation.value) {
            errors.cnewpassword = 'Confirm your new password.';
        } else if (confirmation.value !== password.value) {
            errors.cnewpassword = 'The password confirmation does not match.';
        }

        return errors;
    }

    function validatePhotoForm(form) {
        var input = form.querySelector('[name="nonoy"]');
        var file = input && input.files ? input.files[0] : null;
        var errors = {};

        if (!file) {
            errors.nonoy = 'Choose a profile photo before saving.';
        } else if (allowedImageTypes.indexOf(file.type) === -1) {
            errors.nonoy = 'Choose a JPG, PNG, or GIF image.';
        } else if (file.size > maximumImageSize) {
            errors.nonoy = 'The selected image is larger than 2 MB.';
        }

        return errors;
    }

    function submitForm(form) {
        clearFeedback(form);

        var formType = form.getAttribute('data-account-form');
        var errors = formType === 'password' ? validatePasswordForm(form) : validatePhotoForm(form);

        if (Object.keys(errors).length) {
            markInvalidFields(form, errors);
            showFeedback(form, 'error', 'Please check the form and try again.', errors);
            return;
        }

        setSubmitting(form, true);

        window.fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            return response.text().then(function (text) {
                var payload;
                try {
                    payload = JSON.parse(text);
                } catch (error) {
                    payload = { success: false, message: 'The server returned an unexpected response. Please try again.' };
                }

                if (!response.ok || !payload.success) {
                    var requestError = new Error(payload.message || 'We could not save your changes.');
                    requestError.payload = payload;
                    throw requestError;
                }

                return payload;
            });
        }).then(function (payload) {
            showFeedback(form, 'success', payload.message || 'Your changes were saved.');

            if (formType === 'password') {
                form.reset();
                updatePasswordStrength(form.querySelector('[data-new-password]'));
                resetPasswordVisibility(form);
            }

            if (formType === 'profile-photo' && payload.avatar_url) {
                var cacheSafeUrl = payload.avatar_url + (payload.avatar_url.indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now();
                Array.prototype.forEach.call(document.querySelectorAll('.nav-user img, [data-profile-photo-preview]'), function (image) {
                    image.src = cacheSafeUrl;
                });

                var input = form.querySelector('[name="nonoy"]');
                var label = form.querySelector('[data-profile-photo-label]');
                var preview = form.querySelector('[data-profile-photo-preview]');
                if (input) {
                    input.value = '';
                }
                if (label) {
                    label.textContent = 'Choose another photo';
                }
                if (preview) {
                    preview.setAttribute('data-saved-src', cacheSafeUrl);
                }
            }
        }).catch(function (error) {
            var payload = error.payload || {};
            var serverErrors = payload.errors || {};
            markInvalidFields(form, serverErrors);
            showFeedback(form, 'error', payload.message || error.message || 'We could not save your changes.', serverErrors);
        }).then(function () {
            setSubmitting(form, false);
        });
    }

    function resetPasswordVisibility(form) {
        Array.prototype.forEach.call(form.querySelectorAll('input[type="text"]'), function (input) {
            if (input.name === 'currentpassword' || input.name === 'newpassword' || input.name === 'cnewpassword') {
                input.type = 'password';
            }
        });

        Array.prototype.forEach.call(form.querySelectorAll('[data-password-toggle]'), function (button) {
            button.setAttribute('aria-pressed', 'false');
            button.setAttribute('aria-label', button.getAttribute('aria-label').replace('Hide', 'Show'));
            var icon = button.querySelector('i');
            if (icon) {
                icon.className = 'ph ph-eye';
            }
        });
    }

    function updatePasswordStrength(input) {
        if (!input) {
            return;
        }

        var value = input.value;
        var strength = input.closest('.berps-account-field').querySelector('.berps-password-strength');
        var label = strength.querySelector('[data-password-strength-label]');
        var score = 0;

        if (value.length >= 8) { score += 1; }
        if (value.length >= 12) { score += 1; }
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) { score += 1; }
        if (/\d/.test(value) && /[!@#$%^&*]/.test(value)) { score += 1; }

        if (!value) {
            score = 0;
            label.textContent = 'Use 8 or more characters';
        } else if (score <= 1) {
            label.textContent = 'Password strength: Weak';
        } else if (score === 2) {
            label.textContent = 'Password strength: Fair';
        } else if (score === 3) {
            label.textContent = 'Password strength: Good';
        } else {
            label.textContent = 'Password strength: Strong';
        }

        strength.setAttribute('data-score', String(score));
    }

    function initializePhotoPicker() {
        var form = document.querySelector('[data-account-form="profile-photo"]');
        if (!form) {
            return;
        }

        var input = form.querySelector('[name="nonoy"]');
        var preview = form.querySelector('[data-profile-photo-preview]');
        var label = form.querySelector('[data-profile-photo-label]');
        var dropzone = form.querySelector('[data-profile-photo-dropzone]');

        input.addEventListener('change', function () {
            clearFeedback(form);
            var file = input.files && input.files[0];
            if (!file) {
                return;
            }

            var errors = validatePhotoForm(form);
            if (Object.keys(errors).length) {
                markInvalidFields(form, errors);
                showFeedback(form, 'error', 'That photo cannot be used.', errors);
                input.value = '';
                return;
            }

            label.textContent = file.name;
            var reader = new FileReader();
            reader.addEventListener('load', function () {
                preview.src = reader.result;
            });
            reader.readAsDataURL(file);
        });

        dropzone.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                input.click();
            }
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function () {
                dropzone.classList.add('is-dragging');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function () {
                dropzone.classList.remove('is-dragging');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('click', function (event) {
            var openTrigger = event.target.closest('[data-account-modal-open]');
            if (openTrigger) {
                var opened = openModal(openTrigger.getAttribute('data-account-modal-open'), openTrigger);
                if (opened) {
                    event.preventDefault();
                }
                return;
            }

            var closeTrigger = event.target.closest('[data-account-modal-close]');
            if (closeTrigger) {
                event.preventDefault();
                closeModal(closeTrigger.closest('[data-account-modal]'));
                return;
            }

            var toggle = event.target.closest('[data-password-toggle]');
            if (toggle) {
                var field = toggle.closest('.berps-account-input').querySelector('input');
                var isVisible = field.type === 'text';
                field.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
                toggle.setAttribute('aria-label', (isVisible ? 'Show' : 'Hide') + toggle.getAttribute('aria-label').replace(/^(Show|Hide)/, ''));
                var icon = toggle.querySelector('i');
                if (icon) {
                    icon.className = isVisible ? 'ph ph-eye' : 'ph ph-eye-slash';
                }
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && activeModal && !activeModal.querySelector('[data-account-submit]:disabled')) {
                closeModal(activeModal);
            }

            if (event.key === 'Tab' && activeModal) {
                var focusable = activeModal.querySelectorAll('button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])');
                if (!focusable.length) {
                    return;
                }
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-account-form]'), function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitForm(form);
            });

            form.addEventListener('input', function (event) {
                var wrapper = event.target.closest('.berps-account-input');
                if (wrapper) {
                    wrapper.classList.remove('is-invalid');
                }
                if (event.target.matches('[data-new-password]')) {
                    updatePasswordStrength(event.target);
                }
            });
        });

        initializePhotoPicker();

        var requestedModal = new URLSearchParams(window.location.search).get('account_modal');
        if (requestedModal) {
            openModal(requestedModal, null);
        }
    });
})();
