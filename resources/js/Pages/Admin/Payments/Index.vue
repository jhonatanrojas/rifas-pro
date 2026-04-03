<script setup>
import { reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    payments: Object,
    filters: Object,
    raffles: Array,
});

const paymentStatuses = [
    { label: 'All', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' },
];

const paymentMethods = ['', 'zelle', 'pago_movil', 'paypal', 'stripe'];

const filterState = reactive({
    status: props.filters.status || '',
    method: props.filters.method || '',
    raffle_id: props.filters.raffle_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

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
</script>

<template>
    <AdminLayout>
        <template #header>Payment Management</template>
        <template #headerBadge>
            <BaseBadge variant="warning" size="sm">{{ payments.total }} total</BaseBadge>
        </template>

        <div class="glass-panel p-6 rounded-[2rem] mb-8 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Status</span>
                    <select v-model="filterState.status" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm">
                        <option v-for="option in paymentStatuses" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Method</span>
                    <select v-model="filterState.method" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm">
                        <option value="">All</option>
                        <option v-for="method in paymentMethods.slice(1)" :key="method" :value="method">{{ method }}</option>
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">Raffle</span>
                    <select v-model="filterState.raffle_id" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm">
                        <option value="">All</option>
                        <option v-for="raffle in raffles" :key="raffle.id" :value="raffle.id">{{ raffle.title }}</option>
                    </select>
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">From</span>
                    <input v-model="filterState.date_from" type="date" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm" />
                </label>

                <label class="space-y-2">
                    <span class="text-[10px] uppercase tracking-widest font-black text-zinc-500">To</span>
                    <input v-model="filterState.date_to" type="date" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm" />
                </label>
            </div>

            <div class="flex flex-wrap gap-3">
                <BaseButton variant="neon" size="sm" @click="applyFilters">Apply filters</BaseButton>
                <BaseButton variant="outline" size="sm" @click="resetFilters">Reset</BaseButton>
                <BaseButton variant="secondary" size="sm" @click="exportCsv">Export CSV</BaseButton>
            </div>
        </div>

        <div class="glass-panel overflow-hidden rounded-[2rem]">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-zinc-500 text-[10px] font-black uppercase tracking-widest bg-white/2 border-b border-white/5">
                            <th class="px-8 py-5">User</th>
                            <th class="px-8 py-5">Raffle</th>
                            <th class="px-8 py-5">Reference</th>
                            <th class="px-8 py-5">Amount</th>
                            <th class="px-8 py-5">Status</th>
                            <th class="px-8 py-5">Created</th>
                            <th class="px-8 py-5">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-white/1 transition-colors">
                            <td class="px-8 py-5">
                                <div class="font-bold">{{ payment.user.name }}</div>
                                <div class="text-[10px] text-zinc-500">{{ payment.user.email }}</div>
                            </td>
                            <td class="px-8 py-5 font-medium">{{ payment.order.raffle.title }}</td>
                            <td class="px-8 py-5 font-mono text-zinc-400 text-xs">{{ payment.reference_number || 'N/A' }}</td>
                            <td class="px-8 py-5 font-black">{{ payment.amount }} {{ payment.currency }}</td>
                            <td class="px-8 py-5">
                                <BaseBadge :variant="payment.status === 'approved' ? 'success' : (payment.status === 'pending' ? 'warning' : 'error')">
                                    {{ payment.status }}
                                </BaseBadge>
                            </td>
                            <td class="px-8 py-5 text-sm text-zinc-400">{{ new Date(payment.created_at).toLocaleString() }}</td>
                            <td class="px-8 py-5">
                                <Link :href="route('admin.payments.show', payment.id)" class="text-brand-400 font-bold hover:underline">Review →</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-white/5 flex items-center justify-between gap-4">
                <div class="text-sm text-zinc-500">
                    Showing {{ payments.from || 0 }} to {{ payments.to || 0 }} of {{ payments.total || 0 }} results
                </div>
                <div class="flex gap-2">
                    <Link v-for="link in payments.links" :key="link.label" :href="link.url || ''" class="px-3 py-2 rounded-xl text-sm font-semibold border" :class="link.active ? 'bg-brand-500 text-white border-brand-500' : 'bg-white/5 border-white/10 text-zinc-300'">
                        <span v-html="link.label" />
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
