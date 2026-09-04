// ============================================================
// SERVICE WORKER — Cyber Dashboard Profile
// Cache Strategy: Network First with Cache Fallback
// ============================================================
'use strict';

const CACHE_NAME = 'cyber-profile-v1';
const STATIC_ASSETS = [
  '/profile/',
  '/profile/index.php',
  '/profile/assets/css/style.css',
  '/profile/assets/js/main.js',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
];

// ── Install ────────────────────────────────────────────────
self.addEventListener('install', e => {
  console.log('[SW] Installing...');
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return Promise.allSettled(STATIC_ASSETS.map(url => cache.add(url).catch(err => console.warn('[SW] Failed to cache:', url, err))));
    }).then(() => self.skipWaiting())
  );
});

// ── Activate ───────────────────────────────────────────────
self.addEventListener('activate', e => {
  console.log('[SW] Activating...');
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// ── Fetch — Network First, Cache Fallback ──────────────────
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);

  // Skip non-GET or API requests
  if (e.request.method !== 'GET' || url.pathname.startsWith('/profile/api/') || url.pathname.startsWith('/profile/admin/')) {
    return;
  }

  e.respondWith(
    fetch(e.request)
      .then(res => {
        if (res.ok) {
          const clone = res.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(e.request, clone));
        }
        return res;
      })
      .catch(() => caches.match(e.request).then(cached => {
        if (cached) return cached;
        // Offline fallback
        if (e.request.headers.get('accept')?.includes('text/html')) {
          return caches.match('/profile/');
        }
      }))
  );
});

// ── Background Sync ────────────────────────────────────────
self.addEventListener('sync', e => {
  if (e.tag === 'sync-contacts') {
    // Handle offline form submission sync
    e.waitUntil(syncOfflineContacts());
  }
});

async function syncOfflineContacts() {
  const cache = await caches.open('offline-forms');
  const requests = await cache.keys();
  for (const req of requests) {
    try {
      const cached = await cache.match(req);
      await fetch(req, { method: 'POST', body: await cached.text() });
      await cache.delete(req);
    } catch {}
  }
}
