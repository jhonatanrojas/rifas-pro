<script setup>
/**
 * BaseToast — Contenedor global de toasts.
 * Úsalo en el layout raíz. Los toasts se disparan con useToast() composable.
 * Internamente delega a vue-toastification (ya configurado en app.js).
 * Este componente es un alias visual documentado; el render lo maneja Vue Toastification.
 */
</script>

<template>
    <!-- vue-toastification renderiza fuera del DOM por defecto vía Teleport.
         Este componente existe como documentación de la integración. -->
    <slot />
</template>
