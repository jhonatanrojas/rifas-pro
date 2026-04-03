# PWA Setup

## Requisitos

- HTTPS obligatorio en produccion.
- Navegador con soporte de Service Workers y Push API.
- VAPID keys configuradas.

## Variables importantes

- `VAPID_PUBLIC_KEY`
- `VAPID_PRIVATE_KEY`
- `APP_URL`

## Pasos

1. Genera las claves:
   ```bash
   php artisan webpush:vapid
   ```
2. Publica o verifica la configuracion de Web Push.
3. Compila la aplicacion:
   ```bash
   npm run build
   ```
4. Sirve el sitio bajo HTTPS.
5. Verifica que el usuario autenticado vea el prompt de consentimiento.
6. Acepta la notificacion y confirma que se crea la suscripcion.

## Pantallas PWA

- `InstallBanner`: instala la app.
- `OfflineBanner`: muestra el estado sin conexion.
- `UpdatePrompt`: avisa cuando hay nueva version.
- `PushNotificationConsent`: pide permiso para notificaciones push.

## Verificacion

- Abrir la app como usuario autenticado.
- Confirmar que la suscripcion push se guarda en base de datos.
- Enviar una notificacion de prueba desde un flujo real:
  - pago aprobado
  - ganador del sorteo

