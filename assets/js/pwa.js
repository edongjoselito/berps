(function () {
    'use strict';

    var baseUrlMeta = document.querySelector('meta[name="berps-base-url"]');
    var baseUrl = baseUrlMeta ? baseUrlMeta.content : '';
    var deferredInstallPrompt = null;
    var notice;
    var message;
    var action;

    function createNotice() {
        notice = document.createElement('div');
        notice.className = 'berps-pwa-notice';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');
        notice.hidden = true;

        message = document.createElement('span');
        message.className = 'berps-pwa-notice__message';
        notice.appendChild(message);

        action = document.createElement('button');
        action.className = 'berps-pwa-notice__button';
        action.type = 'button';
        action.hidden = true;
        notice.appendChild(action);

        document.body.appendChild(notice);
    }

    function showOfflineNotice() {
        if (!notice) {
            return;
        }
        notice.classList.add('is-offline');
        message.textContent = 'You are offline. Cached pages are read-only; changes cannot be saved.';
        action.hidden = true;
        notice.hidden = false;
    }

    function updateNetworkState() {
        if (!navigator.onLine) {
            showOfflineNotice();
        } else if (notice && notice.classList.contains('is-offline')) {
            notice.hidden = true;
            notice.classList.remove('is-offline');
        }
    }

    function showInstallAction(label, text) {
        message.textContent = text;
        action.textContent = label;
        action.hidden = false;
        notice.hidden = false;
    }

    function setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', function (event) {
            event.preventDefault();
            deferredInstallPrompt = event;
            showInstallAction('Install', 'Install BERPS on this device for faster access.');
        });

        window.addEventListener('appinstalled', function () {
            deferredInstallPrompt = null;
            if (notice && !notice.classList.contains('is-offline')) {
                notice.hidden = true;
            }
        });

        var isAppleMobile = /iphone|ipad|ipod/i.test(navigator.userAgent);
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
        if (isAppleMobile && !isStandalone) {
            showInstallAction('How to install', 'Add BERPS to your Home Screen for faster access.');
        }

        action.addEventListener('click', async function () {
            if (deferredInstallPrompt) {
                try {
                    deferredInstallPrompt.prompt();
                    await deferredInstallPrompt.userChoice;
                    if (notice && !notice.classList.contains('is-offline')) {
                        notice.hidden = true;
                    }
                } catch (error) {
                    console.error('BERPS install prompt failed:', error);
                } finally {
                    deferredInstallPrompt = null;
                }
                return;
            }

            message.textContent = 'To install BERPS, use your browser menu and choose “Add to Home Screen” or “Install app”.';
            action.hidden = true;
        });
    }

    function blockOfflineWrites() {
        document.addEventListener('submit', function (event) {
            if (!navigator.onLine) {
                event.preventDefault();
                showOfflineNotice();
            }
        }, true);
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator) || !baseUrl) {
            return;
        }

        var workerUrl = new URL('service-worker.js', baseUrl).toString();
        navigator.serviceWorker.register(workerUrl, { scope: baseUrl })
            .then(function () {
                return navigator.serviceWorker.ready;
            })
            .then(function (registration) {
                var userMeta = document.querySelector('meta[name="berps-offline-user"]');
                var userKey = userMeta ? userMeta.content.trim() : '';
                var worker = navigator.serviceWorker.controller || registration.active;
                if (userKey && worker) {
                    worker.postMessage({ type: 'BERPS_USER_ACTIVE', userKey: userKey });
                }
            })
            .catch(function (error) {
                console.error('BERPS service worker registration failed:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        createNotice();
        setupInstallPrompt();
        blockOfflineWrites();
        updateNetworkState();
        window.addEventListener('online', updateNetworkState);
        window.addEventListener('offline', updateNetworkState);
        registerServiceWorker();
    });
}());
