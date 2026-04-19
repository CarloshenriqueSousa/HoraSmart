/**
 * Service Worker: sw.js — Cache de assets estáticos do HoraSmart (PWA).
 *
 * Funcionalidades:
 *  - Cache de assets estáticos (CSS, JS, fontes, imagens)
 *  - Estratégia Network First para páginas HTML
 *  - Fallback offline para quando não há conexão
 *
 * Registrado no layouts/app.blade.php e layouts/guest.blade.php.
 *
 * Tecnologias: Service Worker API, Cache API, Fetch API
 */

const CACHE_NAME = 'horasmart-v1';
const OFFLINE_URL = '/';

// Assets para cache imediato
const PRECACHE_ASSETS = [
    '/favicon.svg',
    '/manifest.json',
];

// Install — pré-cache de assets essenciais
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS))
    );
    self.skipWaiting();
});

// Activate — limpar caches antigos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch — Network first, fallback cache
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cache respostas bem-sucedidas de assets
                if (response.ok && event.request.url.match(/\.(css|js|woff2?|svg|png|jpg)$/)) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
