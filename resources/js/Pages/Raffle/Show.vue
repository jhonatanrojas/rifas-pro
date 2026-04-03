<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import RaffleModal from '../../Components/RaffleModal.vue'
import NumberGrid from '@/Components/NumberGrid.vue'
import { useCountdown } from '@/composables/useCountdown'

const props = defineProps({
    raffle: Object,
    tickets: {
        type: Array,
        default: () => [],
    },
    combos: {
        type: Array,
        default: () => [],
    },
    tickets_stats: Object,
})

const isModalOpen = ref(false)
const selectedTickets = ref([])

const raffleData = computed(() => props.raffle?.data ?? props.raffle ?? {})
const raffleCombos = computed(() => props.combos?.length ? props.combos : (raffleData.value?.combos ?? []))

const { days, hours, minutes, seconds } = useCountdown(raffleData.value?.draw_date)

const drawIsFuture = computed(() => {
    if (!raffleData.value?.draw_date) return false
    return new Date(raffleData.value.draw_date) > new Date()
})

const countdownItems = computed(() => [
    { label: 'Dias', value: String(days.value).padStart(2, '0') },
    { label: 'Hrs', value: String(hours.value).padStart(2, '0') },
    { label: 'Min', value: String(minutes.value).padStart(2, '0') },
    { label: 'Seg', value: String(seconds.value).padStart(2, '0') },
])

const ticketStats = computed(() => {
    const arr = props.tickets ?? []

    if (arr.length > 0) {
        return {
            total: arr.length,
            available: arr.filter((t) => t.status === 'available').length,
            reserved: arr.filter((t) => t.status === 'reserved').length,
            sold: arr.filter((t) => t.status === 'sold').length,
        }
    }

    if (props.tickets_stats) {
        const available = Number(props.tickets_stats.available ?? 0)
        const reserved = Number(props.tickets_stats.reserved ?? 0)
        const sold = Number(props.tickets_stats.sold ?? 0)

        return {
            total: available + reserved + sold,
            available,
            reserved,
            sold,
        }
    }

    return { total: 0, available: 0, reserved: 0, sold: 0 }
})

function formatMoney(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(amount)
}
</script>

<template>
    <Head :title="`${raffleData.title} - Detalles de la Rifa`" />

    <div class="min-h-screen bg-surface text-surface-50 font-sans pb-32 md:pb-20">
        <nav class="sticky top-0 z-50 bg-surface/80 backdrop-blur-md border-b border-surface-lighter/50 h-16 flex items-center px-4 sm:px-6 lg:px-8">
            <Link :href="route('home')" class="flex items-center text-surface-300 hover:text-white transition-colors group">
                <svg class="w-5 h-5 mr-2 transform transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver al inicio
            </Link>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="flex-1 space-y-8">
                    <div class="bg-surface-light rounded-3xl overflow-hidden border border-surface-300/10 shadow-xl relative">
                        <div class="absolute top-4 left-4 z-20 flex gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-surface-light/80 backdrop-blur-md text-sm font-bold border border-surface-300/20 text-white shadow-lg shadow-black/50">
                                Sorteo {{ raffleData.draw_type === 'internal_random' ? 'Interno' : 'Loteria' }}
                            </span>
                        </div>

                        <div class="h-64 sm:h-80 md:h-96 relative overflow-hidden bg-surface-lighter">
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-light via-surface-light/20 to-transparent z-10"></div>
                            <img
                                v-if="raffleData.cover_image"
                                :src="raffleData.cover_image"
                                :alt="raffleData.title"
                                class="w-full h-full object-cover object-center"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-surface-400 text-sm">
                                Sin imagen de portada
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 relative z-20 -mt-16 sm:-mt-20">
                            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-surface-300">{{ raffleData.title }}</h1>
                            <p class="text-surface-300 text-lg sm:text-xl font-light mb-8 max-w-3xl leading-relaxed">
                                {{ raffleData.description }}
                            </p>

                            <div class="max-w-xl">
                                <div class="flex justify-between text-sm font-semibold mb-2">
                                    <span class="text-surface-300">Progreso de venta</span>
                                    <span class="text-accent-neon">{{ raffleData.progress_percentage }}%</span>
                                </div>
                                <div class="w-full h-3 bg-surface-lighter rounded-full overflow-hidden border border-surface-300/10 relative">
                                    <div class="h-full bg-gradient-to-r from-brand-500 to-accent-neon rounded-full" :style="`width: ${raffleData.progress_percentage}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="drawIsFuture" class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-brand-500/20 shadow-xl">
                        <p class="text-center text-surface-400 text-xs uppercase tracking-widest font-semibold mb-4">
                            Sorteo en
                        </p>
                        <div class="grid grid-cols-4 gap-3">
                            <div
                                v-for="item in countdownItems"
                                :key="item.label"
                                class="glass-card rounded-2xl p-4 flex flex-col items-center gap-1 border border-brand-500/20 text-center"
                            >
                                <span class="text-3xl sm:text-4xl font-black text-white tabular-nums leading-none">
                                    {{ item.value }}
                                </span>
                                <span class="text-xs text-surface-400 font-semibold uppercase tracking-widest">
                                    {{ item.label }}
                                </span>
                            </div>
                        </div>
                        <p class="text-center text-surface-400 text-xs mt-4">
                            {{ new Date(raffleData.draw_date).toLocaleDateString('es-ES', { dateStyle: 'full' }) }}
                        </p>
                    </div>

                    <div v-if="ticketStats.total > 0" class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-surface-300/10 shadow-xl space-y-5">
                        <h3 class="text-lg font-bold text-white">Estado de tickets</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="bg-surface-lighter rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-white">{{ ticketStats.total }}</p>
                                <p class="text-xs text-surface-400 mt-1 font-semibold uppercase tracking-wider">Total</p>
                            </div>
                            <div class="bg-surface-lighter rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-green-400">{{ ticketStats.available }}</p>
                                <p class="text-xs text-surface-400 mt-1 font-semibold uppercase tracking-wider">Disponibles</p>
                            </div>
                            <div class="bg-surface-lighter rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-yellow-400">{{ ticketStats.reserved }}</p>
                                <p class="text-xs text-surface-400 mt-1 font-semibold uppercase tracking-wider">Reservados</p>
                            </div>
                            <div class="bg-surface-lighter rounded-2xl p-4 text-center">
                                <p class="text-2xl font-black text-surface-300">{{ ticketStats.sold }}</p>
                                <p class="text-xs text-surface-400 mt-1 font-semibold uppercase tracking-wider">Vendidos</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="tickets.length > 0" class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-surface-300/10 shadow-xl space-y-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-white">Elige tus numeros</h3>
                                <p class="text-surface-400 text-sm">Selecciona en el grid y continua al checkout cuando estes listo.</p>
                            </div>
                            <button @click="selectedTickets = []" class="text-xs font-semibold text-surface-400 hover:text-white">
                                Limpiar
                            </button>
                        </div>

                        <NumberGrid
                            v-model="selectedTickets"
                            :tickets="tickets"
                            :ticket-price="Number(raffleData.ticket_price)"
                            :currency="raffleData.currency"
                            :max-selection="100"
                            @continue="isModalOpen = true"
                        />
                    </div>

                    <div v-if="raffleCombos && raffleCombos.length" class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-surface-300/10 shadow-xl space-y-5">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">🎁</span>
                            <h3 class="text-lg font-bold text-white">Combos con descuento</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <button
                                v-for="combo in raffleCombos"
                                :key="combo.id"
                                @click="isModalOpen = true"
                                class="relative group bg-surface-lighter rounded-2xl p-5 border border-surface-300/10 hover:border-brand-500/50 transition-all duration-300 text-left hover:-translate-y-1"
                            >
                                <div
                                    v-if="combo.savings_percentage"
                                    class="absolute -top-2 -right-2 bg-accent-neon text-surface text-xs font-black px-2 py-0.5 rounded-full shadow-lg shadow-accent-neon/30"
                                >
                                    -{{ combo.savings_percentage }}%
                                </div>

                                <p class="text-3xl font-black text-white mb-1">{{ combo.quantity }}</p>
                                <p class="text-sm text-surface-400 mb-3">ticket{{ combo.quantity > 1 ? 's' : '' }}</p>

                                <div class="flex items-baseline gap-2">
                                    <span class="text-xl font-black text-brand-400">
                                        {{ formatMoney(combo.price, raffleData.currency) }}
                                    </span>
                                    <span
                                        v-if="combo.original_price && combo.original_price > combo.price"
                                        class="text-xs text-surface-400 line-through"
                                    >
                                        {{ formatMoney(combo.original_price, raffleData.currency) }}
                                    </span>
                                </div>

                                <p v-if="combo.label" class="mt-2 text-xs text-accent-neon font-semibold">
                                    {{ combo.label }}
                                </p>
                            </button>
                        </div>
                    </div>

                    <div class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-surface-300/10 shadow-xl text-center">
                        <h2 class="text-2xl font-bold text-white tracking-tight mb-2">Participa ahora y prueba tu suerte</h2>
                        <p class="text-surface-400 mb-8 max-w-md mx-auto">Selecciona tus numeros o deja que el sistema escoja la suerte por ti.</p>

                        <button @click="isModalOpen = true" class="w-full max-w-sm mx-auto py-4 px-4 bg-brand-600 hover:bg-brand-500 text-white font-bold text-lg rounded-xl transition-all duration-300 shadow-[0_0_20px_rgba(39,106,230,0.2)] hover:shadow-[0_0_30px_rgba(39,106,230,0.5)] transform hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-2">
                            <span>Comprar tickets</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="lg:w-96 flex-shrink-0 space-y-6">
                    <div class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-surface-300/10 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6">Detalles del sorteo</h3>

                        <ul class="space-y-4 text-surface-300">
                            <li class="flex items-center gap-3">
                                <span class="p-2 rounded-lg bg-surface-lighter"><svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                                <div>
                                    <p class="text-xs text-surface-400">Fecha del sorteo</p>
                                    <p class="font-semibold text-white">{{ new Date(raffleData.draw_date).toLocaleDateString('es-ES', { dateStyle: 'long' }) }}</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="p-2 rounded-lg bg-surface-lighter"><svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                                <div>
                                    <p class="text-xs text-surface-400">Tickets totales</p>
                                    <p class="font-semibold text-white">{{ raffleData.total_tickets }} boletos</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="p-2 rounded-lg bg-surface-lighter"><svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></span>
                                <div>
                                    <p class="text-xs text-surface-400">Estado de pasarela</p>
                                    <p class="font-semibold text-accent-neon">Pagos seguros</p>
                                </div>
                            </li>
                            <li v-if="raffleData.ticket_price" class="flex items-center gap-3">
                                <span class="p-2 rounded-lg bg-surface-lighter">
                                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                </span>
                                <div>
                                    <p class="text-xs text-surface-400">Precio por ticket</p>
                                    <p class="font-semibold text-white">{{ formatMoney(raffleData.ticket_price, raffleData.currency) }}</p>
                                </div>
                            </li>
                        </ul>

                        <button
                            @click="isModalOpen = true"
                            class="mt-8 w-full btn-primary py-3 text-sm font-bold flex items-center justify-center gap-2"
                        >
                            Comprar tickets
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 px-4 pb-6 pt-3 bg-gradient-to-t from-surface via-surface/95 to-transparent">
            <div class="flex items-center gap-3">
                <div v-if="raffleData.ticket_price" class="flex-shrink-0">
                    <p class="text-xs text-surface-400 leading-none">Desde</p>
                    <p class="text-white font-black text-lg leading-tight">
                        {{ formatMoney(raffleData.ticket_price, raffleData.currency) }}
                    </p>
                </div>
                <button
                    @click="isModalOpen = true"
                    class="flex-1 btn-primary py-3.5 font-bold text-base flex items-center justify-center gap-2 shadow-2xl shadow-brand-500/30"
                >
                    Comprar tickets
                </button>
            </div>
        </div>

        <RaffleModal
            :is-open="isModalOpen"
            :raffle-id="raffleData.id"
            :auth="$page.props.auth"
            :initial-selection="selectedTickets"
            @close="isModalOpen = false"
        />
    </div>
</template>

<style scoped>
/* Clean page shell; purchase flow lives in RaffleModal */
</style>
