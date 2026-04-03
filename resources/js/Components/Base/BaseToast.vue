<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    title: {
        type: String,
        default: 'Notificación',
    },
    message: {
        type: String,
        default: '',
    },
    variant: {
        type: String,
        default: 'info', // info | success | warning | error | brand
    },
    show: {
        type: Boolean,
        default: true,
    },
    dismissible: {
        type: Boolean,
        default: true,
    },
    actionLabel: {
        type: String,
        default: '',
    },
    actionHref: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['close'])

const variantMap = {
    info: {
        wrapper: 'border-blue-500/30 bg-blue-500/10 text-blue-300',
        accent: 'bg-blue-400',
        icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    success: {
        wrapper: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        accent: 'bg-emerald-400',
        icon: 'M5 13l4 4L19 7',
    },
    warning: {
        wrapper: 'border-amber-500/30 bg-amber-500/10 text-amber-300',
        accent: 'bg-amber-400',
        icon: 'M12 9v4m0 4h.01M10.29 3.86l-8.5 14.75A2 2 0 003.5 21h17a2 2 0 001.71-3.39l-8.5-14.75a2 2 0 00-3.42 0z',
    },
    error: {
        wrapper: 'border-red-500/30 bg-red-500/10 text-red-300',
        accent: 'bg-red-400',
        icon: 'M6 18L18 6M6 6l12 12',
    },
    brand: {
        wrapper: 'border-brand-500/30 bg-brand-500/10 text-brand-300',
        accent: 'bg-brand-400',
        icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
    },
}

const tone = computed(() => variantMap[props.variant] ?? variantMap.info)
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="show"
            class="w-full rounded-2xl border bg-surface-light shadow-lg"
            :class="tone.wrapper"
            role="status"
            aria-live="polite"
        >
            <div class="flex items-start gap-4 p-4 sm:p-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" :class="tone.accent">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tone.icon" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-black uppercase tracking-widest text-white leading-none">
                        {{ title }}
                    </p>
                    <p v-if="message" class="mt-1 text-sm text-surface-300 leading-relaxed">
                        {{ message }}
                    </p>

                    <div v-if="actionLabel" class="mt-3">
                        <Link
                            v-if="actionHref"
                            :href="actionHref"
                            class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-bold text-white bg-white/10 hover:bg-white/15 transition-colors"
                        >
                            {{ actionLabel }}
                        </Link>
                        <button
                            v-else
                            type="button"
                            class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-bold text-white bg-white/10 hover:bg-white/15 transition-colors"
                        >
                            {{ actionLabel }}
                        </button>
                    </div>
                </div>

                <button
                    v-if="dismissible"
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg p-2 text-surface-400 hover:text-white hover:bg-white/5 transition-colors"
                    aria-label="Cerrar"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>
