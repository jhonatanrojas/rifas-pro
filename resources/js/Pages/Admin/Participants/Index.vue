<script setup>
import { computed, reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';

const props = defineProps({
    orders: Object,
    filters: Object,
    raffles: Array,
});

const filterState = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    raffle_id: props.filters.raffle_id || '',
});

const orderStatuses = [
    { label: 'Todos', value: '' },
    { label: 'Pendiente', value: 'pending' },
    { label: 'Pagado', value: 'paid' },
    { label: 'Cancelado', value: 'cancelled' },
    { label: 'Reembolsado', value: 'refunded' },
];

const paymentBadge = (status) => {
    const map = {
        approved: { label: 'Aprobado', classes: 'bg-green-500/20 text-green-300 border-green-500/30' },
        pending: { label: 'Pendiente', classes: 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30' },
        rejected: { label: 'Rechazado', classes: 'bg-red-500/20 text-red-300 border-red-500/30' },
    };

    return map[status] ?? { label: status ?? 'N/A', classes: 'bg-white/5 text-zinc-300 border-white/10' };
};

const applyFilters = () => {
    window.location = route('admin.participants.index', Object.fromEntries(
        Object.entries(filterState).filter(([, value]) => value !== '' && value !== null && value !== undefined)
    ));
};

const resetFilters = () => {
    window.location = route('admin.participants.index');
};

const paginationLinks = computed(() => {
    const links = props.orders.links ?? [];

    if (Array.isArray(links)) {
        return links.filter(Boolean);
    }

    return Object.values(links).filter(Boolean);
});
</script>

<template>
    <AdminLayout>
        <template #header>Participantes</template>
        <template #description>Gestiona compradores, tickets y estados de venta desde una sola vista.</template>

        <div class="glass-panel p-5 sm:p-6 rounded-[2rem] mb-8 space-y-4 mt-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Buscar</span>
                    <input v-model="filterState.search" type="text" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white" placeholder="Nombre, email o referencia" />
                </label>
                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Estado</span>
                    <select v-model="filterState.status" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                        <option v-for="option in orderStatuses" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </label>
                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Rifa</span>
                    <select v-model="filterState.raffle_id" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                        <option value="">Todas</option>
                        <option v-for="raffle in raffles" :key="raffle.id" :value="raffle.id">{{ raffle.title }}</option>
                    </select>
                </label>
            </div>

            <div class="flex flex-wrap gap-3">
                <BaseButton variant="neon" size="sm" @click="applyFilters">Aplicar filtros</BaseButton>
                <BaseButton variant="outline" size="sm" @click="resetFilters">Restablecer</BaseButton>
            </div>
        </div>

        <div class="glass-panel overflow-hidden rounded-[2rem]">
            <div class="md:hidden p-4 space-y-3">
                <div v-for="order in orders.data" :key="order.id" class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-bold">{{ order.user?.name }}</div>
                            <div class="text-[10px] text-zinc-500">{{ order.user?.email }}</div>
                        </div>
                        <BaseBadge size="sm" :variant="order.status === 'paid' ? 'success' : (order.status === 'pending' ? 'warning' : 'surface')">
                            {{ order.status }}
                        </BaseBadge>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-1">Rifa</p>
                            <p class="font-medium text-white">{{ order.raffle?.title }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-1">Pago</p>
                            <BaseBadge size="sm" :variant="order.payment?.status === 'approved' ? 'success' : (order.payment?.status === 'pending' ? 'warning' : 'error')">
                                {{ paymentBadge(order.payment?.status).label }}
                            </BaseBadge>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-1">Tickets</p>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="ticket in order.tickets" :key="ticket.id" class="px-2 py-0.5 rounded-lg bg-white/5 text-xs font-bold text-white border border-white/10">
                                    {{ ticket.display_number ?? ticket.number }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-zinc-500">
                        {{ new Date(order.created_at).toLocaleString() }}
                    </div>
                </div>

                <div v-if="!orders.data.length" class="rounded-[1.5rem] border border-dashed border-white/10 p-6 text-center text-zinc-500">
                    No hay participantes con estos filtros.
                </div>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-zinc-500 text-[10px] font-black uppercase tracking-widest bg-white/2 border-b border-white/5">
                            <th class="px-8 py-5">Participante</th>
                            <th class="px-8 py-5">Rifa</th>
                            <th class="px-8 py-5">Tickets</th>
                            <th class="px-8 py-5">Pago</th>
                            <th class="px-8 py-5">Estado</th>
                            <th class="px-8 py-5">Creado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-white/1 transition-colors">
                            <td class="px-8 py-5">
                                <div class="font-bold">{{ order.user?.name }}</div>
                                <div class="text-[10px] text-zinc-500">{{ order.user?.email }}</div>
                            </td>
                            <td class="px-8 py-5 font-medium">{{ order.raffle?.title }}</td>
                            <td class="px-8 py-5">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="ticket in order.tickets" :key="ticket.id" class="px-2 py-0.5 rounded-lg bg-white/5 text-xs font-bold text-white border border-white/10">
                                        {{ ticket.display_number ?? ticket.number }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <BaseBadge size="sm" :variant="order.payment?.status === 'approved' ? 'success' : (order.payment?.status === 'pending' ? 'warning' : 'error')">
                                    {{ paymentBadge(order.payment?.status).label }}
                                </BaseBadge>
                            </td>
                            <td class="px-8 py-5">
                                <BaseBadge size="sm" :variant="order.status === 'paid' ? 'success' : (order.status === 'pending' ? 'warning' : 'surface')">
                                    {{ order.status }}
                                </BaseBadge>
                            </td>
                            <td class="px-8 py-5 text-sm text-zinc-400">{{ new Date(order.created_at).toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 sm:p-6 border-t border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="text-sm text-zinc-500 text-center sm:text-left">
                    Mostrando {{ orders.from || 0 }} a {{ orders.to || 0 }} de {{ orders.total || 0 }} resultados
                </div>
                <div class="flex flex-wrap gap-2 justify-center sm:justify-end">
                    <Link
                        v-for="link in paginationLinks"
                        :key="link.label"
                        :href="link.url || ''"
                        class="min-w-9 h-9 px-3 inline-flex items-center justify-center rounded-lg text-xs font-bold border transition-all"
                        :class="link.active
                            ? 'bg-brand-500 border-brand-500 text-white'
                            : 'bg-white/5 border-white/10 text-zinc-400 hover:text-white hover:border-white/20'"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
