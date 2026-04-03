<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    raffle: Object,
    stats: Object,
});
</script>

<template>
    <Head :title="`Estadísticas: ${raffle.data.title}`" />

    <AdminLayout>
        <template #header>{{ raffle.data.title }}</template>
        <template #description>Estadísticas detalladas y control del sorteo.</template>

        <div class="grid grid-cols-1 lg:grid-cols-6 gap-8 mb-12 mt-8">
            <div class="glass-panel p-8 rounded-[2.5rem]">
                <p class="text-[10px] font-black uppercase text-zinc-500 tracking-widest mb-1">Recaudación total</p>
                <div class="text-3xl font-black text-white leading-none">
                    {{ stats.total_sales }} <span class="text-brand-500 text-sm ml-1">{{ raffle.data.currency }}</span>
                </div>
            </div>

            <div class="glass-panel p-8 rounded-[2.5rem]">
                <p class="text-[10px] font-black uppercase text-zinc-500 tracking-widest mb-1">Categoría</p>
                <div class="text-2xl font-black text-white leading-none">{{ raffle.data.category || 'Sin categoría' }}</div>
            </div>

            <div class="glass-panel p-8 rounded-[2.5rem]">
                <p class="text-[10px] font-black uppercase text-zinc-500 tracking-widest mb-1">Boletos vendidos</p>
                <div class="text-3xl font-black text-white leading-none">
                    {{ raffle.data.sold_count }} <span class="text-zinc-500 text-sm font-bold ml-1">/ {{ raffle.data.total_tickets }}</span>
                </div>
            </div>

            <div class="glass-panel p-8 rounded-[2.5rem]">
                <p class="text-[10px] font-black uppercase text-zinc-500 tracking-widest mb-1">Rango</p>
                <div class="text-2xl font-black text-white leading-none">{{ raffle.data.number_range_label }}</div>
            </div>

            <div class="glass-panel p-8 rounded-[2.5rem] lg:col-span-2 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase text-zinc-500 tracking-widest mb-1">Progreso de venta</p>
                    <div class="flex items-end gap-4">
                        <div class="text-4xl font-black text-brand-500 leading-none">{{ Math.round(stats.progress) }}%</div>
                        <div class="flex-1 h-3 bg-white/5 rounded-full overflow-hidden mb-1.5 border border-white/5">
                            <div class="h-full bg-brand-500 rounded-full shadow-[0_0_15px_rgba(20,241,149,0.3)] transition-all duration-1000" :style="`width: ${stats.progress}%`"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-panel rounded-[2.5rem] overflow-hidden border-2 border-white/5">
                    <div class="p-8 border-b border-white/5 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-widest text-white">Últimas ventas</h3>
                        <Link :href="route('admin.payments.index', { raffle_id: raffle.data.id })" class="text-[10px] font-black uppercase text-brand-400 hover:underline">Ver todas →</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="payment in stats.recent_payments" :key="payment.id" class="hover:bg-white/1 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-sm">{{ payment.user.name }}</div>
                                        <div class="text-[10px] text-zinc-500">{{ payment.user.email }}</div>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-black">{{ payment.amount }} {{ payment.currency }}</td>
                                    <td class="px-8 py-5">
                                        <BaseBadge :variant="payment.status === 'approved' ? 'success' : 'warning'" size="sm">
                                            {{ payment.status === 'approved' ? 'Aprobado' : 'Pendiente' }}
                                        </BaseBadge>
                                    </td>
                                    <td class="px-8 py-5 text-zinc-500 text-[10px] uppercase font-bold">{{ new Date(payment.created_at).toLocaleDateString() }}</td>
                                </tr>
                                <tr v-if="stats.recent_payments.length === 0">
                                    <td colspan="4" class="px-8 py-10 text-center text-zinc-500 text-sm italic">No hay ventas registradas aún.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="raffle.data.prizes?.length" class="glass-panel rounded-[2.5rem] p-8 border-2 border-white/5">
                    <h3 class="text-sm font-black uppercase tracking-widest text-white mb-5">Premios</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="prize in raffle.data.prizes" :key="prize.id" class="rounded-2xl overflow-hidden border border-white/10 bg-white/5">
                            <img v-if="prize.image_path" :src="prize.image_path" class="w-full h-40 object-cover" />
                            <div class="p-4">
                                <p class="font-bold text-white">{{ prize.title }}</p>
                                <p v-if="prize.description" class="text-sm text-zinc-400 mt-1">{{ prize.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="glass-panel p-8 rounded-[3rem] border-2 border-brand-500/10">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-brand-400 mb-6 border-b border-brand-500/10 pb-4">Control del sorteo</h3>

                    <div class="space-y-4">
                        <BaseButton v-if="raffle.data.status === 'draft'" variant="neon" class="w-full">Publicar rifa</BaseButton>

                        <div class="p-6 bg-brand-500/5 rounded-[1.5rem] border border-brand-500/10 text-center">
                            <p class="text-[10px] font-black uppercase text-brand-400 mb-2">Fecha programada</p>
                            <p class="text-xl font-black text-white">{{ raffle.data.draw_date || 'No definida' }}</p>
                        </div>

                        <BaseButton
                            :href="route('admin.draw.show', raffle.data.id)"
                            v-if="stats.progress >= 90"
                            variant="brand"
                            class="w-full bg-yellow-500 hover:bg-yellow-600 border-none text-black"
                        >
                            Ejecutar sorteo 🎲
                        </BaseButton>

                        <BaseButton :href="route('admin.raffles.edit', raffle.data.slug)" variant="outline" class="w-full mt-10">Editar configuración</BaseButton>
                    </div>
                </div>

                <div class="glass-panel p-8 rounded-[3rem] border-2 border-red-500/5">
                    <p class="text-red-400 text-[10px] font-black uppercase tracking-widest mb-4">Zona de peligro</p>
                    <BaseButton variant="outline" class="w-full text-red-500 border-red-500/20 hover:bg-red-500/10">Eliminar sorteo</BaseButton>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
