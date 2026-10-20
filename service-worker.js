'use strict';

const CACHE_VERSION = 'berps-pwa-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PUBLIC_CACHE = `${CACHE_VERSION}-public`;
const PRIVATE_CACHE_PREFIX = `${CACHE_VERSION}-private-`;
const STATE_CACHE = `${CACHE_VERSION}-state`;
const MAX_PRIVATE_ENTRIES = 40;
const MAX_STATIC_ENTRIES = 120;
const OFFLINE_PAGE = 'offline.html';
const ACTIVE_USER_KEY = '__active_user__';

const scopeUrl = new URL(self.registration.scope);
const offlinePageUrl = new URL(OFFLINE_PAGE, scopeUrl).href;
const activeUserUrl = new URL(ACTIVE_USER_KEY, scopeUrl).href;

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);
        await cache.addAll([
            offlinePageUrl,
            new URL('assets/images/icons/berps-192.png', scopeUrl).href,
            new URL('assets/images/icons/berps-512.png', scopeUrl).href
        ]);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names
            .filter(name => name.startsWith('berps-pwa-') && !name.startsWith(CACHE_VERSION))
            .map(name => caches.delete(name)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    if (url.origin !== self.location.origin || !url.href.startsWith(scopeUrl.href)) {
        return;
    }

    if (isLogoutRequest(url)) {
        event.respondWith(clearPrivateCaches().then(() => fetch(request)));
        return;
    }

    if (request.method !== 'GET') {
        return;
    }

    if (isStaticAsset(request)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    if (isOfflinePageRequest(url) || isCalendarEventsRequest(url)) {
        event.respondWith(networkFirst(request, isOfflinePageRequest(url)));
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(navigateOrOffline(request));
    }
});

self.addEventListener('message', event => {
    if (!event.data || event.data.type !== 'BERPS_USER_ACTIVE' || typeof event.data.userKey !== 'string') {
        return;
    }

    const sourceUrl = event.source && event.source.url ? new URL(event.source.url) : null;
    if (!sourceUrl || sourceUrl.origin !== scopeUrl.origin || !sourceUrl.href.startsWith(scopeUrl.href)) {
        return;
    }

    event.waitUntil((async () => {
        const userKey = event.data.userKey.trim();
        if (!userKey) {
            return;
        }

        const changed = await setActiveUserKey(userKey);
        if (changed && isOfflinePageRequest(sourceUrl)) {
            await cacheCurrentPage(sourceUrl, userKey);
        }
    })());
});

function isLogoutRequest(url) {
    return /\/login\/logout\/?$/i.test(url.pathname);
}

function isStaticAsset(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith(`${scopeUrl.pathname}assets/`)
        && ['style', 'script', 'image', 'font'].includes(request.destination);
}

function isOfflinePageRequest(url) {
    const path = url.pathname.slice(scopeUrl.pathname.length).replace(/^\/+/, '').toLowerCase();
    const query = url.searchParams;

    if (/^(page\/)?projectaddtask\/?$/.test(path)) {
        return query.get('scope') === 'mine';
    }

    return /^(calendar(?:\/index)?|page\/(?:knowledgebase|knowledgebaseview(?:\/.*)?|admin|staff|superadmin|clientdashboard|studentsprofile)|pos\/(?:posadmin|posstaff))\/?$/.test(path);
}

function isCalendarEventsRequest(url) {
    return /\/calendar\/get_events\/?$/i.test(url.pathname);
}

function isPublicKnowledgeBaseArticle(url) {
    const path = url.pathname.slice(scopeUrl.pathname.length).replace(/^\/+/, '').toLowerCase();
    return /^page\/knowledgebaseview\/[^/]+\/?$/.test(path);
}

async function networkFirst(request, cacheHtml) {
    try {
        const response = await fetch(request);
        if (response.redirected && /\/login(?:\/|$)/i.test(new URL(response.url).pathname)) {
            if (cacheHtml) {
                await clearPrivateCaches();
            }
            return response;
        }
        if (!response.ok) {
            if (cacheHtml && (response.status === 401 || response.status === 403)) {
                await clearPrivateCaches();
            } else if (response.status === 404) {
                const userKey = await getActiveUserKey();
                if (userKey) {
                    const cache = await caches.open(privateCacheName(userKey));
                    await cache.delete(request);
                }
                if (isPublicKnowledgeBaseArticle(new URL(request.url))) {
                    const cache = await caches.open(PUBLIC_CACHE);
                    await cache.delete(request);
                }
            }
            return response;
        }

        const responseUserKey = cacheHtml ? await readUserKey(response) : '';
        const userKey = cacheHtml ? responseUserKey : await getActiveUserKey();
        if (!userKey) {
            if (cacheHtml) {
                await clearPrivateCaches();
                if (response.ok && isPublicKnowledgeBaseArticle(new URL(response.url))) {
                    const cache = await caches.open(PUBLIC_CACHE);
                    await cache.put(request, response.clone());
                    await trimCache(cache, MAX_PRIVATE_ENTRIES);
                }
            }
            return response;
        }

        await setActiveUserKey(userKey);
        const cache = await caches.open(privateCacheName(userKey));
        await cache.put(request, response.clone());
        await trimCache(cache, MAX_PRIVATE_ENTRIES);
        return response;
    } catch (error) {
        const userKey = await getActiveUserKey();
        if (userKey) {
            const cache = await caches.open(privateCacheName(userKey));
            const cachedResponse = await cache.match(request);
            if (cachedResponse) {
                return cachedResponse;
            }
        }

        if (isPublicKnowledgeBaseArticle(new URL(request.url))) {
            const publicCache = await caches.open(PUBLIC_CACHE);
            const publicResponse = await publicCache.match(request);
            if (publicResponse) {
                return publicResponse;
            }
        }

        if (request.mode === 'navigate') {
            return offlineFallback();
        }
        throw error;
    }
}

async function navigateOrOffline(request) {
    try {
        const response = await fetch(request);
        const contentType = response.headers.get('content-type') || '';
        if (response.ok && contentType.toLowerCase().includes('text/html')) {
            const userKey = await readUserKey(response);
            if (userKey) {
                await setActiveUserKey(userKey);
                if (isOfflinePageRequest(new URL(response.url))) {
                    const cache = await caches.open(privateCacheName(userKey));
                    await cache.put(request, response.clone());
                    await trimCache(cache, MAX_PRIVATE_ENTRIES);
                }
            } else {
                await clearPrivateCaches();
            }
        }
        return response;
    } catch (error) {
        const userKey = await getActiveUserKey();
        if (userKey) {
            const cache = await caches.open(privateCacheName(userKey));
            const cachedResponse = await cache.match(request);
            if (cachedResponse) {
                return cachedResponse;
            }
            if (isAppStartRequest(new URL(request.url))) {
                const dashboardResponse = await findCachedDashboard(cache);
                if (dashboardResponse) {
                    return dashboardResponse;
                }
            }
        }
        return offlineFallback();
    }
}

async function offlineFallback() {
    const cache = await caches.open(STATIC_CACHE);
    const response = await cache.match(offlinePageUrl);
    return response || new Response('You are offline. Reconnect and try again.', {
        status: 503,
        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
    });
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cachedResponse = await cache.match(request);
    const networkRequest = fetch(request)
        .then(async response => {
            if (response.ok) {
                await cache.put(request, response.clone());
                await trimCache(cache, MAX_STATIC_ENTRIES);
            }
            return response;
        })
        .catch(error => {
            if (!cachedResponse) {
                throw error;
            }
            return cachedResponse;
        });

    return cachedResponse || networkRequest;
}

async function readUserKey(response) {
    const contentType = response.headers.get('content-type') || '';
    if (!contentType.toLowerCase().includes('text/html')) {
        return '';
    }

    const html = await response.clone().text();
    const headMatch = html.match(/<head\b[^>]*>([\s\S]*?)<\/head>/i);
    const match = headMatch
        ? headMatch[1].match(/<meta\s+name=["']berps-offline-user["']\s+content=["']([^"']*)["']/i)
        : null;
    return match ? match[1].trim() : '';
}

function isAppStartRequest(url) {
    return url.pathname.replace(/\/+$/, '') === scopeUrl.pathname.replace(/\/+$/, '');
}

function isDashboardPage(url) {
    const path = url.pathname.slice(scopeUrl.pathname.length).replace(/^\/+/, '').toLowerCase();
    return /^(page\/(?:admin|staff|superadmin|clientdashboard|studentsprofile)|pos\/(?:posadmin|posstaff))\/?$/.test(path);
}

async function cacheCurrentPage(url, userKey) {
    const request = new Request(url.href, { credentials: 'same-origin', cache: 'no-store' });
    try {
        const response = await fetch(request);
        if (response.ok && await readUserKey(response) === userKey) {
            const cache = await caches.open(privateCacheName(userKey));
            await cache.put(request, response.clone());
            await trimCache(cache, MAX_PRIVATE_ENTRIES);
        }
    } catch (error) {
        console.warn('BERPS could not refresh the current page for offline access:', error);
    }
}

async function findCachedDashboard(cache) {
    const requests = await cache.keys();
    for (const request of requests) {
        if (isDashboardPage(new URL(request.url))) {
            const response = await cache.match(request);
            if (response) {
                return response;
            }
        }
    }
    return null;
}

function privateCacheName(userKey) {
    return `${PRIVATE_CACHE_PREFIX}${encodeURIComponent(userKey)}`;
}

async function getActiveUserKey() {
    const cache = await caches.open(STATE_CACHE);
    const response = await cache.match(activeUserUrl);
    return response ? response.text() : '';
}

async function setActiveUserKey(userKey) {
    const previousUserKey = await getActiveUserKey();
    if (previousUserKey && previousUserKey !== userKey) {
        await clearPrivateCaches();
    }

    const cache = await caches.open(STATE_CACHE);
    await cache.put(activeUserUrl, new Response(userKey, {
        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
    }));
    return previousUserKey !== userKey;
}

async function clearPrivateCaches() {
    const names = await caches.keys();
    await Promise.all(names
        .filter(name => name.startsWith(PRIVATE_CACHE_PREFIX))
        .map(name => caches.delete(name)));

    const stateCache = await caches.open(STATE_CACHE);
    await stateCache.delete(activeUserUrl);
}

async function trimCache(cache, maxEntries) {
    const requests = await cache.keys();
    if (requests.length <= maxEntries) {
        return;
    }

    await Promise.all(requests
        .slice(0, requests.length - maxEntries)
        .map(request => cache.delete(request)));
}
