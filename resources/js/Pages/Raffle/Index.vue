<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    raffles: Object,
});

const getProgress = (sold, total) => {
    if (!total || total === 0) return 0;
    return Math.round((sold / total) * 100);
};

const formatCurrency = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
};
</script>

<template>
    <Head title="Explorar Sorteos" />

    <div class="min-h-screen bg-surface-dark text-white flex flex-col">
        <!-- Navegación Minimalista -->
        <nav class="absolute top-0 left-0 w-full z-50 py-6 px-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <Link :href="route('home')" class="text-white hover:text-brand-400 flex items-center gap-2 transition-colors font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver al Inicio
                </Link>
            </div>
        </nav>

        <!-- Encabezado de la página -->
        <div class="relative bg-surface-dark pt-32 pb-20 overflow-hidden border-b border-surface-300/10">
            <!-- Patrón de fondo sutil -->
            <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 24px 24px;"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-6 tracking-tight">
                    Explora todos los <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-accent-neon">Sorteos</span>
                </h1>
                <p class="text-xl text-surface-300 max-w-2xl mx-auto">
                    Participa en nuestras rifas exclusivas. ¡El próximo gran premio podría ser tuyo!
                </p>
            </div>
        </div>

        <!-- Grilla de Rifas -->
        <div class="bg-surface py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="raffles.data && raffles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <!-- Tarjeta de Rifa -->
                    <div v-for="raffle in raffles.data" :key="raffle.id" 
                         class="group bg-surface-lighter rounded-2xl overflow-hidden shadow-xl border border-surface-300/10 hover:border-brand-500/50 hover:shadow-brand-500/20 transition-all duration-300 flex flex-col hover:-translate-y-1">
                        
                        <!-- Imagen Principal con status overlay -->
                        <div class="aspect-[4/3] w-full overflow-hidden relative bg-surface-dark">
                            <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                                <span class="bg-surface-lighter/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full border border-surface-300/20 shadow-lg flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    En curso
                                </span>
                            </div>
                            
                            <!-- Precio Flotante -->
                            <div class="absolute bottom-4 right-4 z-10">
                                <div class="bg-brand-600 text-white font-black px-4 py-2 rounded-xl shadow-lg shadow-brand-500/30 transform group-hover:scale-105 transition-transform">
                                    {{ formatCurrency(raffle.ticket_price, raffle.currency) }} <span class="text-xs font-medium text-brand-200">/ ticket</span>
                                </div>
                            </div>
                            
                            <img :src="raffle.image_url" :alt="raffle.title" 
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-dark via-transparent to-transparent opacity-80"></div>
                        </div>

                        <!-- Info del Sorteo -->
                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="text-xl font-bold text-white mb-2 leading-tight group-hover:text-brand-300 transition-colors line-clamp-2">
                                {{ raffle.title }}
                            </h3>
                            
                            <!-- Progreso de Ventas -->
                            <div class="mt-auto pt-4 space-y-3">
                                <div class="flex justify-between items-center text-sm font-medium">
                                    <span class="text-surface-300">Progreso de la meta</span>
                                    <span class="text-accent-neon">{{ getProgress(raffle.sold_count, raffle.total_tickets) }}%</span>
                                </div>
                                <div class="h-2.5 w-full bg-surface rounded-full overflow-hidden relative">
                                    <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-brand-500 to-accent-neon rounded-full" 
                                         :style="`width: ${getProgress(raffle.sold_count, raffle.total_tickets)}%`">
                                    </div>
                                </div>
                                <div class="text-xs text-surface-400 font-medium text-right">
                                    {{ (raffle.total_tickets - raffle.sold_count).toLocaleString() }} tickets restantes
                                </div>

                                <Link :href="route('raffles.show', raffle.slug)" 
                                      class="mt-4 block w-full py-3.5 px-4 bg-surface text-center font-bold text-brand-100 rounded-xl hover:bg-brand-600 hover:text-white hover:shadow-lg hover:shadow-brand-500/30 transition-all border border-surface-300/10">
                                    Ver Detalles
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado Vacio -->
                <div v-else class="text-center py-20 bg-surface-lighter rounded-3xl border border-surface-300/10">
                    <div class="mx-auto w-24 h-24 bg-surface rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">No hay sorteos activos</h3>
                    <p class="text-surface-300">Vuelve pronto para ver nuestras próximas rifas y premios.</p>
                </div>

                <!-- Paginación Simple -->
                <div v-if="raffles.links && raffles.links.length > 3" class="mt-16 flex justify-center pb-20">
                    <nav class="inline-flex rounded-md shadow-sm -space-x-px bg-surface text-white">
                        <Link v-for="(link, key) in raffles.links" :key="key" 
                            :href="link.url || '#'" 
                            v-html="link.label"
                            :class="[
                                'px-4 py-2 border border-surface-300/10 text-sm font-medium transition-colors',
                                link.active ? 'bg-brand-600 text-white z-10' : 'hover:bg-surface-light text-surface-300',
                                !link.url ? 'opacity-50 cursor-not-allowed' : '',
                                key === 0 ? 'rounded-l-md' : '',
                                key === raffles.links.length - 1 ? 'rounded-r-md' : ''
                            ]" />
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>
