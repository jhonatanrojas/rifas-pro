<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    featuredRaffles: {
        type: Array,
        default: () => [],
    },
    otherRaffles: {
        type: Array,
        default: () => [],
    },
});

// Utility para formatear dinero
const formatMoney = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};
</script>

<template>
    <Head title="Inicio - Rifas Online Premium" />

    <div class="min-h-screen bg-surface text-surface-50 font-sans selection:bg-brand-500 selection:text-white pb-20">
        
        <!-- Navigation Glassmorphism -->
        <nav class="fixed w-full z-50 top-0 transition-all duration-300 bg-surface/80 backdrop-blur-md border-b border-surface-lighter/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-accent flex items-center justify-center shadow-lg shadow-brand-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                        </div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-surface-300 tracking-tight">Rifas<span class="text-brand-400">Pro</span></span>
                    </div>

                    <div class="flex items-center space-x-6">
                        <div v-if="canLogin" class="hidden sm:block">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="text-sm font-medium text-surface-200 hover:text-white transition-colors"
                            >
                                Mi Panel
                            </Link>

                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="text-sm font-medium text-surface-200 hover:text-white transition-colors mr-6"
                                >
                                    Ingresar
                                </Link>

                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="relative inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-brand-600 border border-transparent rounded-full hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/40 hover:-translate-y-0.5"
                                >
                                    Registrarse
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative pt-32 pb-20 sm:pt-40 sm:pb-24 overflow-hidden">
            <!-- Background Glow Effects -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-brand-600/20 blur-[120px] rounded-full pointer-events-none"></div>
            <div class="absolute top-40 -right-20 w-96 h-96 bg-accent/20 blur-[100px] rounded-full pointer-events-none"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-lighter border border-surface-300/10 mb-8 mt-4 animate-fade-in-up">
                    <span class="flex w-2 h-2 rounded-full bg-accent-neon animate-pulse"></span>
                    <span class="text-xs font-semibold tracking-wide text-surface-200 uppercase">Sorteos Certificados y Transparentes</span>
                </div>
                
                <h1 class="text-5xl sm:text-7xl font-extrabold tracking-tight mb-8">
                    <span class="block text-white drop-shadow-sm">Gana los mejores premios</span>
                    <span class="block mt-2 text-transparent bg-clip-text bg-gradient-to-r from-brand-400 via-accent-neon to-brand-300 pb-2">con un solo ticket.</span>
                </h1>
                
                <p class="mx-auto max-w-2xl text-lg sm:text-xl text-surface-300 mb-10 font-light">
                    Plataforma premium de sorteos donde la suerte y la transparencia se unen. Participa ahora y cambia tu vida por el precio de un café.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#rifas" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white transition-all duration-200 bg-brand-600 border border-transparent rounded-full hover:bg-brand-500 hover:shadow-lg hover:shadow-brand-500/30 hover:-translate-y-1 w-full sm:w-auto">
                        Ver Sorteos Activos
                        <svg class="w-5 h-5 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    </a>
                    <Link href="#" class="inline-flex items-center justify-center px-8 py-4 text-base font-medium text-white transition-all duration-200 bg-surface-lighter border border-surface-300/20 rounded-full hover:bg-surface-light hover:border-surface-300/40 w-full sm:w-auto">
                        ¿Cómo funciona?
                    </Link>
                </div>
            </div>
        </div>

        <!-- Featured Raffles Section -->
        <div id="rifas" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative z-10">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Sorteos Destacados</h2>
                    <p class="mt-2 text-surface-400">Las oportunidades más calientes del momento.</p>
                </div>
                <div class="hidden sm:block">
                    <span class="text-sm font-medium text-brand-400 cursor-pointer hover:text-brand-300">Ver todos &rarr;</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 ml:grid-cols-3 gap-8">
                <!-- Card Component (Simulated with simple loop but complex design) -->
                <div v-for="raffle in featuredRaffles" :key="raffle.id" class="group relative flex flex-col bg-surface-light rounded-3xl overflow-hidden border border-surface-300/10 hover:border-brand-500/30 transition-all duration-500 hover:shadow-2xl hover:shadow-brand-500/10">
                    
                    <!-- Cover Image Container -->
                    <div class="relative h-64 overflow-hidden bg-surface-lighter">
                        <div class="absolute inset-0 bg-gradient-to-t from-surface-light via-transparent to-transparent z-10 transition-opacity duration-300 opacity-80 group-hover:opacity-60"></div>
                        
                        <!-- Badges -->
                        <div class="absolute top-4 left-4 z-20 flex flex-col gap-2">
                            <span v-if="raffle.is_featured" class="inline-flex items-center px-3 py-1 rounded-full bg-accent/90 backdrop-blur-sm text-xs font-bold text-white shadow-lg border border-white/10 uppercase tracking-widest">
                                🔥 Premium
                            </span>
                        </div>
                        
                        <div class="absolute top-4 right-4 z-20">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-surface-light/80 backdrop-blur-md text-sm font-bold text-brand-300 border border-brand-500/30 shadow-lg">
                                {{ formatMoney(raffle.ticket_price, raffle.currency) }} <span class="text-surface-400 text-xs ml-1 font-normal">/ ticket</span>
                            </span>
                        </div>

                        <!-- Image -->
                        <img 
                            :src="'/storage/' + raffle.cover_image" 
                            :alt="raffle.title"
                            class="w-full h-full object-cover object-center transform transition-transform duration-700 group-hover:scale-110" 
                        />
                    </div>

                    <!-- Content -->
                    <div class="flex flex-col flex-grow p-6 relative z-20 bg-surface-light">
                        <h3 class="text-xl font-bold text-white mb-2 line-clamp-1 group-hover:text-brand-300 transition-colors">{{ raffle.title }}</h3>
                        <p class="text-surface-400 text-sm mb-6 line-clamp-2">{{ raffle.description }}</p>
                        
                        <div class="mt-auto">
                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="flex justify-between text-xs font-semibold mb-2">
                                    <span class="text-surface-300">Tickets vendidos</span>
                                    <span class="text-accent-neon">{{ Math.round((raffle.sold_count / raffle.total_tickets) * 100) }}%</span>
                                </div>
                                <div class="w-full h-2.5 bg-surface-lighter rounded-full overflow-hidden border border-surface-300/10">
                                    <div 
                                        class="h-full bg-gradient-to-r from-brand-500 to-accent-neon rounded-full relative"
                                        :style="`width: ${(raffle.sold_count / raffle.total_tickets) * 100}%`"
                                    >
                                        <div class="absolute top-0 right-0 bottom-0 left-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPjxwYXRoIGQ9Ik0tMiAxMGwxMi0xMk02IDE0bDEyLTEyIiBzdHJva2U9InJnYmEoMjU1LDI1NSwyNTUsMC4yKSIgc3Ryb2tlLXdpZHRoPSIyIi8+PC9zdmc+')] opacity-50"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs mt-2 text-surface-400">
                                    <span>{{ raffle.sold_count }} vendidos</span>
                                    <span>{{ raffle.total_tickets - raffle.sold_count }} restantes</span>
                                </div>
                            </div>
                            
                            <button class="w-full py-3.5 px-4 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl transition-all duration-300 shadow-[0_0_20px_rgba(39,106,230,0.15)] group-hover:shadow-[0_0_25px_rgba(39,106,230,0.4)] flex justify-center items-center gap-2 group/btn">
                                Comprar Tickets
                                <svg class="w-4 h-4 transform transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Empty state fallback -->
            <div v-if="featuredRaffles.length === 0" class="text-center py-20 bg-surface-lighter rounded-3xl border border-surface-300/10">
                <svg class="w-16 h-16 text-surface-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <h3 class="text-xl font-bold text-white">No hay rifas destacadas</h3>
                <p class="text-surface-400 mt-2">Vuelve más tarde para ver nuevos sorteos estupendos.</p>
            </div>
        </div>

        <!-- Other Raffles Section -->
        <div v-if="otherRaffles.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-2xl font-bold text-white tracking-tight mb-8">Todos los Sorteos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Smaller Cards for other raffles -->
                <div v-for="raffle in otherRaffles" :key="raffle.id" class="group flex flex-col bg-surface-light rounded-2xl overflow-hidden border border-surface-300/10 hover:border-surface-300/30 transition-all duration-300">
                    <div class="relative h-48 overflow-hidden bg-surface-lighter">
                        <div class="absolute inset-0 bg-gradient-to-t from-surface-light via-transparent to-transparent z-10 opacity-90"></div>
                        <img 
                            :src="'/storage/' + raffle.cover_image" 
                            :alt="raffle.title"
                            class="w-full h-full object-cover object-center transform transition-transform duration-500 group-hover:scale-105" 
                        />
                        <div class="absolute bottom-3 left-3 z-20">
                            <span class="px-2.5 py-1 rounded-md bg-surface-900/80 backdrop-blur-sm text-xs font-bold text-white border border-white/10">
                                {{ formatMoney(raffle.ticket_price, raffle.currency) }}
                            </span>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="text-lg font-bold text-white line-clamp-1 mb-1">{{ raffle.title }}</h3>
                        <p class="text-xs text-surface-400 mb-4">{{ raffle.available_count }} tickets disponibles</p>
                        <button class="mt-auto w-full py-2 bg-surface-lighter hover:bg-surface-300/20 text-white text-sm font-semibold rounded-lg transition-colors border border-surface-300/10">
                            Participar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Keyframes for subtle entrance animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out forwards;
}
</style>
