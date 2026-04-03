# 🎟️ RifasOnline SaaS

Plataforma SaaS para la gestión y realización de rifas online con enfoque Mobile-First y PWA.

## 🚀 Tecnologías
- **Backend:** Laravel 11 (PHP 8.3+)
- **Frontend:** Vue 3 + Inertia.js + TailwindCSS
- **Real-time:** Web Push Notifications + WhatsApp Integration
- **Database:** PostgreSQL (Core) + SQLite (Testing) + Redis (Cache/Queues)
- **Animaciones:** GSAP + VueUse Motion

## 📦 Instalación

1. Clonar el repositorio.
2. `composer install`
3. `npm install`
4. Configurar `.env` basándose en `.env.example`.
5. Generar claves:
   - `php artisan key:generate`
   - `php artisan webpush:vapid`
6. `php artisan migrate --seed`
7. `npm run dev`

## ✅ Sorteos y Ganadores
La plataforma incluye un sistema de sorteo aleatorio verificable (SHA-256) con notificaciones automáticas para los ganadores via WebPush y WhatsApp.

## 📱 PWA
La aplicación es una PWA instalable con soporte offline y notificaciones nativas en dispositivos móviles.

## Produccion

1. Copia las variables de entorno desde `.env.example`.
2. Ejecuta `composer install` y `npm install`.
3. Genera claves de Web Push con `php artisan webpush:vapid`.
4. Ejecuta `php artisan migrate --seed`.
5. Compila assets con `npm run build`.
6. Arranca workers y scheduler en produccion.

## Documentacion tecnica

- [Arquitectura](./ARQUITECTURA.md)
- [Config PWA](./PWA_SETUP.md)
- Plan de tareas principal: `F:\rifas_saas\PLAN_TAREAS_RIFAS_SAAS.md`
