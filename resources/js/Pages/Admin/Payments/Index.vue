<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    payments: Object,
    filters: Object,
});

const filterStatus = (status) => {
    router.get(route('admin.payments.index'), { ...props.filters, status }, { preserveState: true });
};
</script>

<template>
    <Head title="Gestión de Pagos" />

    <AdminLayout>
        <template #header>Gestión de Pagos</template>

        <div class="mb-8 flex flex-wrap gap-2">
            <BaseButton size="sm" :variant="filters.status ? 'outline' : 'brand'" @click="filterStatus(null)">Todos</BaseButton>
            <BaseButton size="sm" :variant="filters.status === 'pending' ? 'brand' : 'outline'" @click="filterStatus('pending')">Pendientes</BaseButton>
            <BaseButton size="sm" :variant="filters.status === 'approved' ? 'brand' : 'outline'" @click="filterStatus('approved')">Aprobados</BaseButton>
            <BaseButton size="sm" :variant="filters.status === 'rejected' ? 'brand' : 'outline'" @click="filterStatus('rejected')">Rechazados</BaseButton>
        </div>

        <div class="glass-panel overflow-hidden rounded-[2rem]">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-zinc-500 text-[10px] font-black uppercase tracking-widest bg-white/2 border-b border-white/5">
                        <th class="px-8 py-5">Usuario</th>
                        <th class="px-8 py-5">Rifa</th>
                        <th class="px-8 py-5">Referencia</th>
                        <th class="px-8 py-5">Monto</th>
                        <th class="px-8 py-5">Estado</th>
                        <th class="px-8 py-5">Acción</th>
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
                        <td class="px-8 py-5">
                            <Link :href="route('admin.payments.show', payment.id)" class="text-brand-400 font-bold hover:underline">Revisar →</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
