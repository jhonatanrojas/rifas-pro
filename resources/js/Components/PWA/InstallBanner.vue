<script setup>
import { ref, onMounted } from 'vue'
import { usePWAInstall } from '@/composables/usePWAInstall'

const { canInstall, install } = usePWAInstall()
const dismissed = ref(false)

onMounted(() => {
    dismissed.value = !!localStorage.getItem('pwa_banner_dismissed')
})

function dismiss() {
    dismissed.value = true
    localStorage.setItem('pwa_banner_dismissed', '1')
}

async function handleInstall() {
    await install()
    dismissed.value = true
}
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="canInstall && !dismissed"
            class="fixed bottom-24 md:bottom-6 left-4 right-4 md:left-auto md:right-6 md:w-80 z-50"
        >
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-surface-light border border-white/10 shadow-2xl shadow-black/50 backdrop-blur-md">
                <!-- App Icon -->
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center flex-shrink-0 shadow-lg shadow-brand-500/30">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white leading-tight">Instala RifasOnline</p>
                    <p class="text-xs text-zinc-400 mt-0.5">Acceso rápido desde tu pantalla de inicio</p>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <button
                        @click="handleInstall"
                        class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-lg transition-colors"
                    >
                        Instalar
                    </button>
                    <button
                        @click="dismiss"
                        class="p-1.5 text-zinc-500 hover:text-white transition-colors rounded-lg hover:bg-white/5"
                        aria-label="Cerrar"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
