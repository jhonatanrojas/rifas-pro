<script setup>
import { computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    payments: Object,
    filters: Object,
    raffles: Array,
});

const paymentStatuses = [
    { label: 'Todos', value: '' },
    { label: 'Pendiente', value: 'pending' },
    { label: 'Aprobado', value: 'approved' },
    { label: 'Rechazado', value: 'rejected' },
];

const paymentMethods = ['', 'zelle', 'pago_movil', 'paypal', 'stripe'];
const paymentMethodLabels = {
    zelle: 'Zelle',
    pago_movil: 'Pago móvil',
    paypal: 'PayPal',
    stripe: 'Stripe',
};

const filterState = reactive({
    status: props.filters.status || '',
    method: props.filters.method || '',
    raffle_id: props.filters.raffle_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const selectedPayments = reactive([]);

const buildParams = (extra = {}) => {
    const params = {
        status: filterState.status || '',
        method: filterState.method || '',
        raffle_id: filterState.raffle_id || '',
        date_from: filterState.date_from || '',
        date_to: filterState.date_to || '',
        ...extra,
    };

    return Object.fromEntries(
        Object.entries(params).filter(([, value]) => value !== '' && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    window.location = route('admin.payments.index', buildParams());
};

const resetFilters = () => {
    window.location = route('admin.payments.index');
};

const exportCsv = () => {
    window.location = route('admin.payments.index', buildParams({ export: 'csv' }));
};

const allVisibleSelected = computed(() => {
    if (!props.payments.data.length) return false;
    return props.payments.data.every((payment) => selectedPayments.includes(payment.id));
});

const paginationLinks = computed(() => {
    const links = props.payments.links ?? [];

    if (Array.isArray(links)) {
        return links.filter(Boolean);
    }

    return Object.values(links).filter(Boolean);
});

const togglePayment = (paymentId) => {
    const index = selectedPayments.indexOf(paymentId);
    if (index >= 0) {
        selectedPayments.splice(index, 1);
        return;
    }

    selectedPayments.push(paymentId);
};

const toggleAllVisible = () => {
    if (allVisibleSelected.value) {
        props.payments.data.forEach((payment) => {
            const index = selectedPayments.indexOf(payment.id);
            if (index >= 0) {
                selectedPayments.splice(index, 1);
            }
        });
        return;
    }

    props.payments.data.forEach((payment) => {
        if (!selectedPayments.includes(payment.id)) {
            selectedPayments.push(payment.id);
        }
    });
};

const bulkReview = (status) => {
    router.post(route('admin.payments.bulk-review'), {
        payment_ids: [...selectedPayments],
        status,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedPayments.splice(0, selectedPayments.length);
        },
    });
};

const paymentStatusLabel = (status) => {
    const labels = {
        approved: 'Aprobado',
        pending: 'Pendiente',
        rejected: 'Rechazado',
    };

    return labels[status] ?? status ?? 'N/A';
};
</script>

<template>
    <AdminLayout>
        <template #header>Gestión de pagos</template>
        <template #headerBadge>
            <BaseBadge variant="warning" size="sm">{{ payments.total }} en total</BaseBadge>
        </template>

        <div v-if="selectedPayments.length" class="glass-panel p-4 rounded-[2rem] mb-6 border border-brand-500/20 bg-brand-500/5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-sm font-semibold text-zinc-200">
                    {{ selectedPayments.length }} pago(s) seleccionado(s)
                </div>
                <div class="flex flex-wrap gap-3">
                    <BaseButton variant="neon" size="sm" @click="bulkReview('approved')">Aprobar seleccionados</BaseButton>
                    <BaseButton variant="outline" size="sm" class="border-red-500/40 text-red-400 hover:bg-red-500/10" @click="bulkReview('rejected')">Rechazar seleccionados</BaseButton>
                </div>
            </div>
        </div>

        <div class="glass-panel p-5 sm:p-6 rounded-[2rem] mb-8 space-y-4 mt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Estado</span>
                    <select v-model="filterState.status" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                        <option v-for="option in paymentStatuses" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Metodo</span>
                    <select v-model="filterState.method" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                        <option value="">Todos</option>
                        <option v-for="method in paymentMethods.slice(1)" :key="method" :value="method">{{ paymentMethodLabels[method] ?? method }}</option>
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Rifa</span>
                    <select v-model="filterState.raffle_id" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                        <option value="">Todas</option>
                        <option v-for="raffle in raffles" :key="raffle.id" :value="raffle.id">{{ raffle.title }}</option>
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Desde</span>
                    <input v-model="filterState.date_from" type="date" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white" />
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Hasta</span>
                    <input v-model="filterState.date_to" type="date" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm text-white" />
                </label>
            </div>

            <div class="flex flex-wrap gap-3">
                <BaseButton variant="neon" size="sm" @click="applyFilters">Aplicar filtros</BaseButton>
                <BaseButton variant="outline" size="sm" @click="resetFilters">Restablecer</BaseButton>
                <BaseButton variant="secondary" size="sm" @click="exportCsv">Exportar CSV</BaseButton>
            </div>
        </div>

        <div class="glass-panel overflow-hidden rounded-[2rem]">
            <div class="md:hidden p-4 space-y-3">
                <div v-for="payment in payments.data" :key="payment.id" class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                class="rounded border-white/20 bg-white/5"
                                :checked="selectedPayments.includes(payment.id)"
                                @change="togglePayment(payment.id)"
                            />
                            <div>
                                <div class="font-bold">{{ payment.user.name }}</div>
                                <div class="text-[10px] text-zinc-500">{{ payment.user.email }}</div>
                            </div>
                        </label>
                        <Link :href="route('admin.payments.show', payment.id)" class="text-brand-400 font-bold text-sm whitespace-nowrap">Revisar →</Link>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-1">Rifa</p>
                            <p class="font-medium text-white">{{ payment.order.raffle.title }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-1">Referencia</p>
                            <p class="font-mono text-zinc-400 text-xs">{{ payment.reference_number || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-1">Monto</p>
                            <p class="font-black text-white">{{ payment.amount }} {{ payment.currency }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-1">Estado</p>
                        <BaseBadge size="sm" :variant="payment.status === 'approved' ? 'success' : (payment.status === 'pending' ? 'warning' : 'error')">
                                {{ paymentStatusLabel(payment.status) }}
                            </BaseBadge>
                        </div>
                    </div>

                    <div class="text-xs text-zinc-500">
                        {{ new Date(payment.created_at).toLocaleString() }}
                    </div>
                </div>

                <div v-if="!payments.data.length" class="rounded-[1.5rem] border border-dashed border-white/10 p-6 text-center text-zinc-500">
                    No hay pagos con estos filtros.
                </div>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-zinc-500 text-[10px] font-black uppercase tracking-widest bg-white/2 border-b border-white/5">
                            <th class="px-4 py-5">
                                <input type="checkbox" class="rounded border-white/20 bg-white/5" :checked="allVisibleSelected" @change="toggleAllVisible" />
                            </th>
                            <th class="px-8 py-5">Acción</th>
                            <th class="px-8 py-5">Usuario</th>
                            <th class="px-8 py-5">Rifa</th>
                            <th class="px-8 py-5">Referencia</th>
                            <th class="px-8 py-5">Monto</th>
                            <th class="px-8 py-5">Estado</th>
                            <th class="px-8 py-5">Creado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-white/1 transition-colors">
                            <td class="px-4 py-5">
                                <input
                                    type="checkbox"
                                    class="rounded border-white/20 bg-white/5"
                                    :checked="selectedPayments.includes(payment.id)"
                                    @change="togglePayment(payment.id)"
                                />
                            </td>
                            <td class="px-8 py-5">
                                <Link :href="route('admin.payments.show', payment.id)" class="text-brand-400 font-bold hover:underline">Revisar →</Link>
                            </td>
                            <td class="px-8 py-5">
                                <div class="font-bold">{{ payment.user.name }}</div>
                                <div class="text-[10px] text-zinc-500">{{ payment.user.email }}</div>
                            </td>
                            <td class="px-8 py-5 font-medium">{{ payment.order.raffle.title }}</td>
                            <td class="px-8 py-5 font-mono text-zinc-400 text-xs">{{ payment.reference_number || 'N/A' }}</td>
                            <td class="px-8 py-5 font-black">{{ payment.amount }} {{ payment.currency }}</td>
                            <td class="px-8 py-5">
                                <BaseBadge size="sm" :variant="payment.status === 'approved' ? 'success' : (payment.status === 'pending' ? 'warning' : 'error')">
                                    {{ paymentStatusLabel(payment.status) }}
                                </BaseBadge>
                            </td>
                            <td class="px-8 py-5 text-sm text-zinc-400">{{ new Date(payment.created_at).toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 sm:p-6 border-t border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="text-sm text-zinc-500 text-center sm:text-left">
                    Mostrando {{ payments.from || 0 }} a {{ payments.to || 0 }} de {{ payments.total || 0 }} resultados
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
