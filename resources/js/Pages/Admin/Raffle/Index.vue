<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    raffles: Object,
});
</script>

<template>
    <Head title="Mis Rifas - Admin" />

    <AdminLayout>
        <template #header>Sorteos y Rifas</template>
        <template #description>Gestiona tus eventos, precios y visualiza el progreso de ventas.</template>
        
        <template #actions>
            <BaseButton :href="route('admin.raffles.create')" variant="neon" size="md">
                + Nuevo Sorteo
            </BaseButton>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 mt-12">
            <div v-for="raffle in raffles.data" :key="raffle.id" class="group relative glass-panel rounded-[2.5rem] overflow-hidden border-2 border-white/5 hover:border-brand-500/30 transition-all duration-500 flex flex-col">
                <!-- Cover Image -->
                <div class="h-48 overflow-hidden relative">
                    <img :src="raffle.cover_image || '/images/placeholder-raffle.png'" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 to-transparent"></div>
                    <div class="absolute top-4 right-4">
                        <BaseBadge :variant="raffle.status === 'active' ? 'success' : (raffle.status === 'draft' ? 'surface' : 'warning')">
                            {{ raffle.status }}
                        </BaseBadge>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-8 flex-1 flex flex-col">
                    <h3 class="text-xl font-black text-white mb-2 leading-tight uppercase tracking-tight">{{ raffle.title }}</h3>
                    <p class="text-zinc-500 text-sm line-clamp-2 mb-6 font-medium">{{ raffle.description }}</p>

                    <div class="space-y-4 mt-auto">
                        <!-- Progress Bar -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                                <span class="text-brand-400">{{ raffle.sold_count }} / {{ raffle.total_tickets }} Vendidos</span>
                                <span class="text-zinc-500">{{ Math.round((raffle.sold_count / raffle.total_tickets) * 100) }}%</span>
                            </div>
                            <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full bg-brand-500 rounded-full" :style="`width: ${ (raffle.sold_count / raffle.total_tickets) * 100 }%`"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-white/5 pt-4">
                            <div>
                                <p class="text-[10px] font-black uppercase text-zinc-500 mb-0.5">Precio Unitario</p>
                                <p class="font-black text-white leading-none">{{ raffle.ticket_price }} {{ raffle.currency }}</p>
                            </div>
                            <div class="flex gap-2">
                                <BaseButton :href="route('admin.raffles.edit', raffle.id)" variant="outline" size="sm">Editar</BaseButton>
                                <BaseButton :href="route('admin.raffles.show', raffle.id)" variant="brand" size="sm">Stats</BaseButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="raffles.data.length === 0" class="flex flex-col items-center justify-center p-20 text-center glass-panel rounded-[3rem]">
             <svg class="w-16 h-16 text-zinc-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="1"/></svg>
             <h3 class="text-2xl font-black text-white mb-2">AUN NO TIENES RIFAS</h3>
             <p class="text-zinc-500 mb-8">Comienza creando tu primera rifa para empezar a vender.</p>
             <BaseButton :href="route('admin.raffles.create')" variant="neon">Crear Primera Rifa</BaseButton>
        </div>
    </AdminLayout>
</template>
