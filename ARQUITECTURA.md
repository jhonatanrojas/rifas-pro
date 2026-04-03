# Arquitectura

## Capas

- Controllers: reciben requests, validan con Form Requests y devuelven Inertia o JSON.
- Actions: coordinan casos de uso como reserva, pago y sorteo.
- Services: encapsulan reglas de negocio reutilizables, notificaciones, OCR y calculos.
- Jobs: tareas diferidas para expiracion de reservas, OCR y generacion de comprobantes.
- Notifications: Web Push y WhatsApp para eventos finales del negocio.

## Flujo principal

1. El usuario reserva tickets.
2. Se crea una orden pendiente y se confirma el pago manual o automatico.
3. El admin revisa el comprobante y aprueba o rechaza.
4. Al aprobarse, se marcan tickets como vendidos, se actualiza el sold count y se emite notificacion.
5. Al llegar la fecha, el admin ejecuta el sorteo con auditoria hash.

## Puntos de integracion

- Web Push subscriptions: `push.update` y `push.destroy`.
- WhatsApp provider: `WHATSAPP_PROVIDER=callmebot|twilio`.
- Receipts: ruta firmada `receipts.verify`.

## Consideraciones de produccion

- Mantener `queue:work` activo para jobs y notificaciones.
- Mantener el scheduler de Laravel ejecutandose cada minuto.
- Usar HTTPS para PWA, service workers y Web Push.
- Configurar almacenamiento persistente para comprobantes y archivos publicos.
