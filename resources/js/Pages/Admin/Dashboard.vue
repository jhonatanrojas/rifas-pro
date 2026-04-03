<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    dailyStats: Object,
    salesHistory: Array,
    rafflesProgress: Array,
    recentOrders: Array,
});

const stats = ref(props.dailyStats);
const orders = ref(props.recentOrders);

// Echo setup for real-time metrics
onMounted(() => {
    window.Echo.private('admin.metrics')
        .listen('.metrics.updated', (event) => {
            stats.value = event.metrics.daily;
            orders.value = event.metrics.orders;
        });
});

const chartOptions = {
    chart: { type: 'area', toolbar: { show: false }, background: 'transparent' },
    stroke: { curve: 'smooth', width: 3, colors: ['#3b82f6'] },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] } },
    xaxis: { 
        categories: props.salesHistory.map(h => h.date),
        axisBorder: { show: false },
        labels: { style: { colors: '#71717a' } }
    },
    yaxis: { labels: { style: { colors: '#71717a' } } },
    grid: { borderColor: 'rgba(255,255,255,0.05)' },
    theme: { mode: 'dark' },
    colors: ['#3b82f6'],
};

const chartSeries = [{
    name: 'Ventas (USD)',
    data: props.salesHistory.map(h => h.amount)
}];

const donutOptions = {
    labels: props.rafflesProgress.map(r => r.title),
    legend: { position: 'bottom', labels: { colors: '#fff' } },
    theme: { mode: 'dark' },
    colors: ['#3b82f6', '#f43f5e', '#10b981', '#f59e0b'],
    plotOptions: { pie: { donut: { size: '70%' } } }
};

const donutSeries = props.rafflesProgress.map(r => r.progress);
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header>Dashboard Administrativo</template>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-panel p-6 rounded-[2rem] border-l-4 border-brand-500">
                <p class="text-xs font-black uppercase text-zinc-500 mb-1">Ventas Hoy (USD)</p>
                <div class="flex items-center justify-between">
                    <p class="text-3xl font-black">${{ stats.sales_usd }}</p>
                    <div class="p-2 bg-brand-500/10 rounded-xl text-brand-400">
                       <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3 1.343 3 3-1.343 3-3 3m0-12c1.657 0 3 1.343 3 3s-1.343 3-3 3-3-1.343-3-3 1.343-3 3-3m0 0V4m0 16v-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-6 rounded-[2rem] border-l-4 border-accent-neon">
                <p class="text-xs font-black uppercase text-zinc-500 mb-1">Ventas Hoy (VES)</p>
                <p class="text-3xl font-black">Bs.{{ stats.sales_ves }}</p>
            </div>

            <div class="glass-panel p-6 rounded-[2rem] border-l-4 border-blue-500">
                <p class="text-xs font-black uppercase text-zinc-500 mb-1">Tickets Vendidos</p>
                <p class="text-3xl font-black">{{ stats.tickets_sold_today }}</p>
            </div>

            <div class="glass-panel p-6 rounded-[2rem] border-l-4" :class="stats.pending_reviews > 0 ? 'border-red-500 animate-pulse' : 'border-zinc-700'">
                <p class="text-xs font-black uppercase text-zinc-500 mb-1">Pendientes de Revisión</p>
                <div class="flex items-center gap-4">
                    <p class="text-3xl font-black">{{ stats.pending_reviews }}</p>
                    <BaseBadge v-if="stats.pending_reviews > 0" variant="error" size="sm">Urgente</BaseBadge>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="glass-panel p-8 rounded-[2rem]">
                <h3 class="text-lg font-black uppercase tracking-widest mb-6">Tendencia de Ventas (7d)</h3>
                <VueApexCharts height="300" :options="chartOptions" :series="chartSeries" />
            </div>

            <div class="glass-panel p-8 rounded-[2rem]">
                <h3 class="text-lg font-black uppercase tracking-widest mb-6">Progreso de Rifas Activas</h3>
                <VueApexCharts height="300" type="donut" :options="donutOptions" :series="donutSeries" />
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="glass-panel rounded-[2rem] overflow-hidden">
            <div class="p-8 flex items-center justify-between border-b border-white/5">
                <h3 class="text-lg font-black uppercase tracking-widest">Últimas Órdenes</h3>
                <Link href="/admin/payments" class="text-xs font-bold text-brand-400 hover:underline">Ver todas →</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-zinc-500 text-[10px] font-black uppercase tracking-widest bg-white/2">
                            <th class="px-8 py-4">Usuario</th>
                            <th class="px-8 py-4">Rifa</th>
                            <th class="px-8 py-4">Monto</th>
                            <th class="px-8 py-4">Fecha</th>
                            <th class="px-8 py-4">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-white/2 transition-colors">
                            <td class="px-8 py-5">
                                <div class="font-bold">{{ order.user.name }}</div>
                                <div class="text-[10px] text-zinc-500">{{ order.user.email }}</div>
                            </td>
                            <td class="px-8 py-5 font-medium">{{ order.raffle.title }}</td>
                            <td class="px-8 py-5 font-black">{{ order.total }} {{ order.currency }}</td>
                            <td class="px-8 py-5 text-sm text-zinc-400">{{ new Date(order.created_at).toLocaleString() }}</td>
                            <td class="px-8 py-5">
                                <BaseBadge :variant="order.status === 'paid' ? 'success' : 'warning'">
                                    {{ order.status }}
                                </BaseBadge>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
