const CACHE_NAME = 'dramaflix-v1';
// Hanya cache file kritis yang PASTI ADA. 
// Jangan masukkan file gambar/logo disini jika Anda tidak yakin file-nya ada.
const urlsToCache = [
  '/',
  '/assets/style.css',
  '/assets/dashboard.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
      .catch(err => console.error('Gagal Cache Awal:', err)) // Log error biar tau
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request)
      .catch(() => {
        return caches.match(event.request);
      })
  );
});