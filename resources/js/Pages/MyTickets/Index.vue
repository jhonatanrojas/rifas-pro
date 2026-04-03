<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BaseEmpty from '@/Components/Base/BaseEmpty.vue'
import BaseBadge from '@/Components/Base/BaseBadge.vue'

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
})

// Flatten orders list from paginated object
const ordersList = computed(() => props.orders?.data ?? [])

const totalCount = computed(() => props.orders?.meta?.total ?? ordersList.value.length)

function formatMoney(amount, currency = 'USD') {
    try {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: currency || 'USD',
        }).format(amount)
    } catch {
        return `${currency} ${Number(amount).toFixed(2)}`
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('es-ES', { dateStyle: 'medium' })
}

const paymentBadge = (status) => {
    const map = {
        approved: { label: 'Aprobado',  classes: 'bg-green-500/20 text-green-300 border-green-500/30' },
        pending:  { label: 'Pendiente', classes: 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30' },
        rejected: { label: 'Rechazado', classes: 'bg-red-500/20 text-red-300 border-red-500/30' },
        refunded: { label: 'Reembolsado', classes: 'bg-purple-500/20 text-purple-300 border-purple-500/30' },
    }
    return map[status] ?? { label: status, classes: 'bg-surface-lighter text-surface-300 border-surface-300/20' }
}

function hasWinningTicket(order) {
    return order.tickets?.some(t => t.status === 'winner') ?? false
}
</script>

<template>
    <Head title="Mis Tickets" />

    <AppLayout>
        <template #header>Mis Tickets</template>

        <div class="space-y-6">

            <!-- Header row -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-white">Mis Tickets</h2>
                    <p class="text-surface-400 text-sm mt-0.5">
                        {{ totalCount }} orden{{ totalCount !== 1 ? 'es' : '' }} en total
                    </p>
                </div>
                <Link
                    :href="route('home')"
                    class="btn-secondary text-sm px-4 py-2 hidden sm:inline-flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Comprar más
                </Link>
            </div>

            <!-- Empty state -->
            <BaseEmpty
                v-if="ordersList.length === 0"
                title="Aún no tienes tickets"
                message="Participa en una rifa y tus tickets aparecerán aquí."
                icon="empty"
            >
                <template #actions>
                    <Link :href="route('home')" class="btn-primary px-6 py-3 text-sm font-bold">
                        🎟️ Ver rifas disponibles
                    </Link>
                </template>
            </BaseEmpty>

            <!-- Orders list -->
            <div v-else class="space-y-4">
                <div
                    v-for="order in ordersList"
                    :key="order.id"
                    class="glass-card rounded-2xl overflow-hidden border transition-all duration-300"
                    :class="hasWinningTicket(order)
                        ? 'border-yellow-500/40 shadow-lg shadow-yellow-500/10'
                        : 'border-surface-300/10 hover:border-brand-500/30'"
                >
                    <!-- Winner banner -->
                    <div
                        v-if="hasWinningTicket(order)"
                        class="px-5 py-2 bg-gradient-to-r from-yellow-500/20 to-amber-500/10 border-b border-yellow-500/20 flex items-center gap-2"
                    >
                        <span class="text-lg">🏆</span>
                        <span class="text-yellow-300 font-black text-sm">¡Ganaste!</span>
                        <span class="text-yellow-400/70 text-xs">Un ticket de esta orden resultó ganador</span>
                    </div>

                    <div class="p-5 flex flex-col sm:flex-row gap-4">
                        <!-- Raffle cover -->
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-surface-lighter">
                                <img
                                    v-if="order.raffle?.cover_image"
                                    :src="`/storage/${order.raffle.cover_image}`"
                                    :alt="order.raffle?.title"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center text-2xl"
                                >
                                    🎟️
                                </div>
                            </div>
                        </div>

                        <!-- Order info -->
                        <div class="flex-1 min-w-0 space-y-3">
                            <!-- Title + payment status -->
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="font-bold text-white text-base leading-tight truncate max-w-xs">
                                        {{ order.raffle?.title ?? 'Rifa sin título' }}
                                    </p>
                                    <p class="text-xs text-surface-400 mt-0.5">
                                        Orden #{{ order.id }} · {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                                <!-- Payment badge -->
                                <span
                                    v-if="order.payment?.status"
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border flex-shrink-0',
                                        paymentBadge(order.payment.status).classes
                                    ]"
                                >
                                    {{ paymentBadge(order.payment.status).label }}
                                </span>
                            </div>

                            <!-- Ticket chips -->
                            <div v-if="order.tickets && order.tickets.length" class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="ticket in order.tickets"
                                    :key="ticket.number"
                                    :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold border',
                                        ticket.status === 'winner'
                                            ? 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30'
                                            : 'bg-surface-lighter text-surface-300 border-surface-300/20'
                                    ]"
                                >
                                    {{ ticket.display_number ?? String(ticket.number).padStart(order.raffle?.number_digits || 4, '0') }}
                                    <span v-if="ticket.status === 'winner'" class="ml-1">🏆</span>
                                </span>
                            </div>

                            <!-- Total -->
                            <div class="flex items-center justify-between pt-1 border-t border-surface-300/10">
                                <span class="text-xs text-surface-400 font-semibold uppercase tracking-wider">Total</span>
                                <span class="text-brand-400 font-black text-base">
                                    {{ formatMoney(order.total, order.currency) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination links -->
            <div
                v-if="orders?.links && orders.links.length > 3"
                class="flex flex-wrap justify-center gap-2 pt-4"
            >
                <template v-for="link in orders.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-bold border transition-all',
                            link.active
                                ? 'bg-brand-600 border-brand-500 text-white'
                                : 'bg-surface-light border-surface-300/20 text-surface-300 hover:border-brand-500/50'
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-3 py-1.5 rounded-lg text-xs font-bold border bg-surface-lighter border-surface-300/10 text-surface-400/40 cursor-not-allowed"
                        v-html="link.label"
                    />
                </template>
            </div>

        </div>
    </AppLayout>
</template>
