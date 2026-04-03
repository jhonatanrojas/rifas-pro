<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import RaffleModal from '../../Components/RaffleModal.vue';

const props = defineProps({
    raffle: Object,
    tickets: Array,
    combos: Array,
});

const isModalOpen = ref(false);

// Formatters
const formatMoney = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};
</script>

<template>
    <Head :title="`${raffle.title} - Detalles de la Rifa`" />

    <div class="min-h-screen bg-surface text-surface-50 font-sans pb-20">
        
        <!-- Navbar Minimal -->
        <nav class="sticky top-0 z-50 bg-surface/80 backdrop-blur-md border-b border-surface-lighter/50 h-16 flex items-center px-4 sm:px-6 lg:px-8">
            <Link :href="route('home')" class="flex items-center text-surface-300 hover:text-white transition-colors group">
                <svg class="w-5 h-5 mr-2 transform transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver al inicio
            </Link>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Columna Izquierda: Imagen, Tabs y Tickets -->
                <div class="flex-1 space-y-8">
                    
                    <!-- Cover Image & Rifa Titulo -->
                    <div class="bg-surface-light rounded-3xl overflow-hidden border border-surface-300/10 shadow-xl relative">
                        <div class="absolute top-4 left-4 z-20 flex gap-2">
                             <span class="inline-flex items-center px-3 py-1 rounded-full bg-surface-light/80 backdrop-blur-md text-sm font-bold border border-surface-300/20 text-white shadow-lg shadow-black/50">
                                Sorteo {{ raffle.draw_type === 'internal_random' ? 'Interno' : 'Lotería' }}
                            </span>
                        </div>
                        
                        <div class="h-64 sm:h-80 md:h-96 relative overflow-hidden bg-surface-lighter">
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-light via-surface-light/20 to-transparent z-10"></div>
                            <img :src="'/storage/' + raffle.cover_image" :alt="raffle.title" class="w-full h-full object-cover object-center" />
                        </div>
                        
                        <div class="p-6 sm:p-8 relative z-20 -mt-16 sm:-mt-20">
                            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-surface-300">{{ raffle.title }}</h1>
                            <p class="text-surface-300 text-lg sm:text-xl font-light mb-8 max-w-3xl leading-relaxed">
                                {{ raffle.description }}
                            </p>
                            
                            <!-- Barra de progreso general -->
                            <div class="max-w-xl">
                                <div class="flex justify-between text-sm font-semibold mb-2">
                                    <span class="text-surface-300">Progreso de venta</span>
                                    <span class="text-accent-neon">{{ raffle.progress_percentage }}%</span>
                                </div>
                                <div class="w-full h-3 bg-surface-lighter rounded-full overflow-hidden border border-surface-300/10 relative">
                                    <div class="h-full bg-gradient-to-r from-brand-500 to-accent-neon rounded-full" :style="`width: ${raffle.progress_percentage}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main CTA -->
                    <div class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-surface-300/10 shadow-xl text-center">
                        <h2 class="text-2xl font-bold text-white tracking-tight mb-2">¡Participa ahora y prueba tu suerte!</h2>
                        <p class="text-surface-400 mb-8 max-w-md mx-auto">Selecciona tus números favoritos o deja que el sistema escoja la suerte por ti con nuestro módulo de compra rápida.</p>
                        
                        <button @click="isModalOpen = true" class="w-full max-w-sm mx-auto py-4 px-4 bg-brand-600 hover:bg-brand-500 text-white font-bold text-lg rounded-xl transition-all duration-300 shadow-[0_0_20px_rgba(39,106,230,0.2)] hover:shadow-[0_0_30px_rgba(39,106,230,0.5)] transform hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-2">
                            <span>Comprar Tickets</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>

                </div>

                <!-- Columna Derecha: Details Sidebar -->
                <div class="lg:w-96 flex-shrink-0 space-y-6">
                    <div class="bg-surface-light rounded-3xl p-6 sm:p-8 border border-surface-300/10 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6">Detalles del Sorteo</h3>
                        
                        <ul class="space-y-4 text-surface-300">
                            <li class="flex items-center gap-3">
                                <span class="p-2 rounded-lg bg-surface-lighter"><svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                                <div>
                                    <p class="text-xs text-surface-400">Fecha del Sorteo</p>
                                    <p class="font-semibold text-white">{{ new Date(raffle.draw_date).toLocaleDateString('es-ES', { dateStyle: 'long' }) }}</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="p-2 rounded-lg bg-surface-lighter"><svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                                <div>
                                    <p class="text-xs text-surface-400">Tickets Totales</p>
                                    <p class="font-semibold text-white">{{ raffle.total_tickets }} boletos</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="p-2 rounded-lg bg-surface-lighter"><svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></span>
                                <div>
                                    <p class="text-xs text-surface-400">Estado de Pasarela</p>
                                    <p class="font-semibold text-accent-neon">Pagos 100% Seguros</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        <RaffleModal 
            :is-open="isModalOpen" 
            :raffle-id="raffle.id" 
            :auth="$page.props.auth"
            @close="isModalOpen = false" 
        />
    </div>
</template>

<style scoped>
/* Estilos simplificados porque RaffleModal se encarga de casi todo */
</style>
