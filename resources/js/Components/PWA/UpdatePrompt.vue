<script setup>
import { ref, onMounted } from 'vue'

const showPrompt = ref(false)
let registration = null

onMounted(() => {
    if (!('serviceWorker' in navigator)) return

    navigator.serviceWorker.ready.then((reg) => {
        registration = reg

        reg.addEventListener('updatefound', () => {
            const newWorker = reg.installing
            if (!newWorker) return

            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    showPrompt.value = true
                }
            })
        })
    })
})

function reload() {
    if (!registration?.waiting) {
        window.location.reload()
        return
    }
    registration.waiting.postMessage({ type: 'SKIP_WAITING' })
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        window.location.reload()
    })
}

function dismiss() {
    showPrompt.value = false
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
            v-if="showPrompt"
            class="fixed bottom-24 md:bottom-6 left-4 right-4 md:left-auto md:right-6 md:w-80 z-50"
        >
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-brand-900 border border-brand-500/30 shadow-2xl shadow-brand-500/20">
                <div class="w-10 h-10 rounded-xl bg-brand-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white">Nueva versión disponible</p>
                    <p class="text-xs text-brand-300 mt-0.5">Actualiza para obtener las últimas mejoras</p>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <button
                        @click="reload"
                        class="px-3 py-1.5 bg-brand-500 hover:bg-brand-400 text-white text-xs font-bold rounded-lg transition-colors"
                    >
                        Actualizar
                    </button>
                    <button
                        @click="dismiss"
                        class="p-1.5 text-brand-400 hover:text-white transition-colors rounded-lg hover:bg-white/5"
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
