const VERSION='djoeragan-v12';
const STATIC=[
 '/warkop-djoeragan/offline.html',
 '/warkop-djoeragan/assets/css/style.css',
 '/warkop-djoeragan/assets/css/branding.css',
 '/warkop-djoeragan/assets/css/dashboard.css',
 '/warkop-djoeragan/assets/css/extras.css',
 '/warkop-djoeragan/assets/css/cart.css',
 '/warkop-djoeragan/assets/css/orders.css',
 '/warkop-djoeragan/assets/css/notifications.css',
 '/warkop-djoeragan/assets/css/ui-feedback.css',
 '/warkop-djoeragan/assets/js/app.js',
 '/warkop-djoeragan/assets/js/pos.js',
 '/warkop-djoeragan/assets/js/orders.js',
 '/warkop-djoeragan/assets/js/ui-feedback.js',
 '/warkop-djoeragan/assets/icons/icon-192.png',
 '/warkop-djoeragan/assets/icons/icon-512.png',
 '/warkop-djoeragan/assets/icons/favicon.png',
 '/warkop-djoeragan/assets/images/logo.png'
];
self.addEventListener('install',event=>event.waitUntil(caches.open(VERSION).then(c=>c.addAll(STATIC)).then(()=>self.skipWaiting())));
self.addEventListener('activate',event=>event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==VERSION).map(k=>caches.delete(k)))).then(()=>self.clients.claim())));
self.addEventListener('fetch',event=>{
 if(event.request.method!=='GET') return;
 const url=new URL(event.request.url);
 if(url.origin!==location.origin) return;
 if(url.pathname.endsWith('.php')||url.pathname.startsWith('/warkop-djoeragan/api/')||url.pathname==='/warkop-djoeragan/'){
   event.respondWith(fetch(event.request).catch(()=>caches.match('/warkop-djoeragan/offline.html'))); return;
 }
 event.respondWith(caches.match(event.request).then(cached=>cached||fetch(event.request).then(response=>{const copy=response.clone();caches.open(VERSION).then(c=>c.put(event.request,copy));return response})));
});
