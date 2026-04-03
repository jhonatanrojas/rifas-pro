<script setup>
import { ref, computed } from 'vue'
import { useCountdown } from '@/composables/useCountdown'
import BaseSpinner from '@/Components/Base/BaseSpinner.vue'

const props = defineProps({
    raffle: { type: Object, required: true },   // { id, title, draw_date, status }
    winner: { type: Object, default: null },    // null | { user: {name}, ticket: {number}, prize_description }
    auditHash: { type: String, default: null },
    canExecute: { type: Boolean, default: false },
    isExecuting: { type: Boolean, default: false },
})

const emit = defineEmits(['execute'])

const { days, hours, minutes, seconds, isExpired } = useCountdown(props.raffle?.draw_date)

const auditOpen = ref(false)
const hashCopied = ref(false)

const drawDatePassed = computed(() => {
    if (!props.raffle?.draw_date) return false
    return new Date(props.raffle.draw_date) <= new Date()
})

// Phase logic
const phase = computed(() => {
    if (props.winner) return 'winner'
    if (drawDatePassed.value && props.canExecute) return 'pre-draw'
    if (!drawDatePassed.value) return 'countdown'
    return 'waiting'
})

const countdownItems = computed(() => [
    { label: 'Días',    value: String(days.value).padStart(2, '0') },
    { label: 'Horas',   value: String(hours.value).padStart(2, '0') },
    { label: 'Min',     value: String(minutes.value).padStart(2, '0') },
    { label: 'Seg',     value: String(seconds.value).padStart(2, '0') },
])

function copyHash() {
    if (!props.auditHash) return
    navigator.clipboard.writeText(props.auditHash).then(() => {
        hashCopied.value = true
        setTimeout(() => { hashCopied.value = false }, 2000)
    })
}
</script>

<template>
    <div class="w-full">

        <!-- ===== PHASE: COUNTDOWN ===== -->
        <div v-if="phase === 'countdown'" class="space-y-6">
            <p class="text-center text-surface-400 text-sm font-semibold uppercase tracking-widest">
                Sorteo en
            </p>
            <div class="grid grid-cols-4 gap-3">
                <div
                    v-for="item in countdownItems"
                    :key="item.label"
                    class="glass-card rounded-2xl p-4 flex flex-col items-center gap-1 border border-brand-500/20"
                >
                    <span class="text-3xl sm:text-4xl font-black text-white tabular-nums leading-none">
                        {{ item.value }}
                    </span>
                    <span class="text-xs text-surface-400 font-semibold uppercase tracking-widest">
                        {{ item.label }}
                    </span>
                </div>
            </div>
            <p class="text-center text-surface-400 text-xs">
                {{ new Date(raffle.draw_date).toLocaleDateString('es-ES', { dateStyle: 'full' }) }}
            </p>
        </div>

        <!-- ===== PHASE: PRE-DRAW (admin only) ===== -->
        <div v-else-if="phase === 'pre-draw'" class="flex flex-col items-center gap-6 py-8">
            <div class="text-center space-y-2">
                <p class="text-surface-400 text-sm">La fecha de sorteo ha llegado</p>
                <h3 class="text-2xl font-black text-white">¿Listo para elegir al ganador?</h3>
            </div>

            <button
                v-if="!isExecuting"
                @click="emit('execute')"
                class="btn-neon text-xl px-10 py-5 rounded-2xl font-black flex items-center gap-3 shadow-2xl"
            >
                <span class="text-2xl">🎲</span>
                Ejecutar Sorteo
            </button>

            <div v-else class="flex flex-col items-center gap-4 py-4">
                <BaseSpinner size="lg" color="neon" />
                <p class="text-accent-neon font-bold animate-pulse">Seleccionando ganador...</p>
            </div>
        </div>

        <!-- ===== PHASE: WAITING (date passed, not admin) ===== -->
        <div v-else-if="phase === 'waiting'" class="flex flex-col items-center gap-4 py-8 text-center">
            <div class="w-16 h-16 rounded-full bg-surface-lighter flex items-center justify-center text-3xl animate-pulse">
                ⏳
            </div>
            <div>
                <h3 class="text-white font-bold text-lg">Sorteo en proceso</h3>
                <p class="text-surface-400 text-sm mt-1">El ganador será anunciado muy pronto</p>
            </div>
        </div>

        <!-- ===== PHASE: WINNER REVEAL ===== -->
        <div v-else-if="phase === 'winner'" class="space-y-8">
            <!-- Confetti Layer -->
            <div class="confetti-container" aria-hidden="true">
                <span v-for="i in 20" :key="i" :class="`confetti-piece confetti-${i}`"></span>
            </div>

            <!-- Winner announcement -->
            <div class="flex flex-col items-center gap-6 py-4">
                <div class="text-center space-y-1">
                    <p class="text-surface-400 text-xs uppercase tracking-widest font-semibold">¡Tenemos un ganador!</p>
                    <h3 class="text-3xl font-black text-white">🏆 {{ winner.user?.name }}</h3>
                </div>

                <!-- Winning ticket number -->
                <div class="relative">
                    <div class="winner-ring-outer"></div>
                    <div class="winner-ring-inner"></div>
                    <div class="relative z-10 w-32 h-32 rounded-full bg-gradient-to-br from-yellow-400 to-amber-600 flex flex-col items-center justify-center shadow-2xl shadow-yellow-500/30">
                        <span class="text-xs font-bold text-yellow-900 uppercase tracking-widest">Número</span>
                        <span class="text-3xl font-black text-yellow-900 leading-tight">
                            {{ String(winner.ticket?.number ?? 0).padStart(4, '0') }}
                        </span>
                    </div>
                </div>

                <!-- Prize -->
                <div class="glass-card rounded-2xl px-8 py-4 text-center border border-yellow-500/20 space-y-1">
                    <p class="text-surface-400 text-xs uppercase tracking-widest">Premio</p>
                    <p class="text-brand-400 font-bold text-lg">{{ winner.prize_description }}</p>
                </div>
            </div>
        </div>

        <!-- ===== AUDIT SECTION ===== -->
        <div v-if="auditHash" class="mt-6 border-t border-surface-300/10 pt-6">
            <button
                @click="auditOpen = !auditOpen"
                class="w-full flex items-center justify-between gap-3 text-left group"
            >
                <span class="flex items-center gap-2 text-sm font-semibold text-surface-300 group-hover:text-white transition-colors">
                    <span>🔐</span> Verificar resultado
                </span>
                <svg
                    :class="['w-4 h-4 text-surface-400 transition-transform', auditOpen ? 'rotate-180' : '']"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="auditOpen" class="mt-4 space-y-3">
                    <p class="text-xs text-surface-400 leading-relaxed">
                        Este sorteo fue ejecutado con un algoritmo verificable. El hash SHA-256 a continuación
                        certifica el resultado de forma inmutable.
                    </p>
                    <div class="bg-surface-light rounded-xl p-3 border border-surface-300/20 flex items-start gap-2">
                        <code class="text-xs text-green-400 font-mono break-all flex-1 leading-relaxed">
                            {{ auditHash }}
                        </code>
                        <button
                            @click="copyHash"
                            class="flex-shrink-0 p-1.5 rounded-lg bg-surface-lighter hover:bg-surface-300/20 transition-colors text-surface-400 hover:text-white"
                            :title="hashCopied ? 'Copiado' : 'Copiar hash'"
                        >
                            <svg v-if="!hashCopied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg v-else class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>
        </div>

    </div>
</template>

<style scoped>
/* ── Winner ring animation ── */
.winner-ring-outer,
.winner-ring-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    pointer-events: none;
}

.winner-ring-outer {
    width: 160px;
    height: 160px;
    border: 3px solid rgba(234, 179, 8, 0.5);
    animation: ring-pulse 2s ease-in-out infinite;
}

.winner-ring-inner {
    width: 148px;
    height: 148px;
    border: 2px solid rgba(234, 179, 8, 0.25);
    animation: ring-pulse 2s ease-in-out infinite 0.5s;
}

@keyframes ring-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
    50%       { transform: translate(-50%, -50%) scale(1.1); opacity: 0.3; }
}

/* ── Confetti ── */
.confetti-container {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}

.confetti-piece {
    position: absolute;
    width: 8px;
    height: 8px;
    top: -10px;
    border-radius: 2px;
    animation: confetti-fall linear infinite;
}

/* Generate 20 pieces with varying positions, colors, and timings */
.confetti-1  { left: 5%;  background: #f59e0b; animation-duration: 3s;   animation-delay: 0s;    width: 6px;  height: 10px; }
.confetti-2  { left: 10%; background: #8b5cf6; animation-duration: 4s;   animation-delay: 0.3s;  width: 8px;  height: 8px; }
.confetti-3  { left: 18%; background: #ec4899; animation-duration: 3.5s; animation-delay: 0.6s;  width: 5px;  height: 12px; }
.confetti-4  { left: 25%; background: #10b981; animation-duration: 2.8s; animation-delay: 0.2s;  width: 9px;  height: 6px; }
.confetti-5  { left: 32%; background: #3b82f6; animation-duration: 4.2s; animation-delay: 0.9s;  width: 7px;  height: 9px; }
.confetti-6  { left: 40%; background: #f59e0b; animation-duration: 3.1s; animation-delay: 0.1s;  width: 6px;  height: 8px; }
.confetti-7  { left: 48%; background: #ef4444; animation-duration: 3.8s; animation-delay: 0.7s;  width: 10px; height: 6px; }
.confetti-8  { left: 55%; background: #8b5cf6; animation-duration: 2.9s; animation-delay: 0.4s;  width: 5px;  height: 11px; }
.confetti-9  { left: 62%; background: #ec4899; animation-duration: 4.5s; animation-delay: 1.0s;  width: 8px;  height: 7px; }
.confetti-10 { left: 70%; background: #10b981; animation-duration: 3.3s; animation-delay: 0.5s;  width: 6px;  height: 9px; }
.confetti-11 { left: 78%; background: #f59e0b; animation-duration: 2.7s; animation-delay: 0.8s;  width: 9px;  height: 5px; }
.confetti-12 { left: 85%; background: #3b82f6; animation-duration: 4.1s; animation-delay: 0.2s;  width: 7px;  height: 8px; }
.confetti-13 { left: 92%; background: #ef4444; animation-duration: 3.6s; animation-delay: 0.6s;  width: 5px;  height: 10px; }
.confetti-14 { left: 8%;  background: #a855f7; animation-duration: 3.9s; animation-delay: 1.1s;  width: 8px;  height: 6px; }
.confetti-15 { left: 22%; background: #06b6d4; animation-duration: 2.6s; animation-delay: 0.3s;  width: 6px;  height: 8px; }
.confetti-16 { left: 38%; background: #f59e0b; animation-duration: 4.3s; animation-delay: 0.9s;  width: 10px; height: 7px; }
.confetti-17 { left: 52%; background: #ec4899; animation-duration: 3.2s; animation-delay: 0.1s;  width: 7px;  height: 11px; }
.confetti-18 { left: 66%; background: #10b981; animation-duration: 4.0s; animation-delay: 0.7s;  width: 5px;  height: 8px; }
.confetti-19 { left: 74%; background: #8b5cf6; animation-duration: 2.8s; animation-delay: 0.4s;  width: 9px;  height: 6px; }
.confetti-20 { left: 90%; background: #3b82f6; animation-duration: 3.7s; animation-delay: 1.2s;  width: 6px;  height: 10px; }

@keyframes confetti-fall {
    0%   { transform: translateY(-10px) rotate(0deg);   opacity: 1; }
    80%  { opacity: 1; }
    100% { transform: translateY(400px) rotate(720deg); opacity: 0; }
}
</style>
