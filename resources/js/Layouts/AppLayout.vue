<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import UserIcon from '@/Components/Icons/UserIcon.vue';
import HomeIcon from '@/Components/Icons/HomeIcon.vue';
import TicketIcon from '@/Components/Icons/TicketIcon.vue';
import TrophyIcon from '@/Components/Icons/TrophyIcon.vue';
import OfflineBanner from '@/Components/PWA/OfflineBanner.vue';
import InstallBanner from '@/Components/PWA/InstallBanner.vue';
import UpdatePrompt from '@/Components/PWA/UpdatePrompt.vue';

const page = usePage();
const showingNavigationDropdown = ref(false);

const navItems = [
    { label: 'Inicio', icon: HomeIcon, route: 'dashboard', active: 'dashboard' },
    { label: 'Mis Rifas', icon: TicketIcon, route: 'purchases.index', active: 'purchases.*' },
    { label: 'Ganadores', icon: TrophyIcon, route: 'winners.index', active: 'winners.*' },
    { label: 'Mi Cuenta', icon: UserIcon, route: 'profile.edit', active: 'profile.*' },
];

const isRouteActive = (routeName) => {
    return route().current(routeName);
};
</script>

<template>
    <div class="min-h-screen bg-surface selection:bg-brand-500 selection:text-white pb-20 md:pb-0 md:pl-20" style="padding-bottom: max(5rem, env(safe-area-inset-bottom)); padding-left: max(0px, env(safe-area-inset-left));">
        <!-- PWA Components -->
        <OfflineBanner />
        <InstallBanner />
        <UpdatePrompt />
        <!-- Background Accents -->
        <div class="fixed inset-0 bg-mesh opacity-10 pointer-events-none"></div>
        <div class="fixed top-[-10%] right-[-10%] w-[500px] h-[500px] bg-brand-500/5 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="fixed bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-accent-neon/5 blur-[100px] rounded-full pointer-events-none"></div>

        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex fixed left-0 top-0 bottom-0 w-20 flex-col items-center py-8 glass-panel z-50">
            <Link href="/" class="mb-10">
                <ApplicationLogo :onlyIcon="true" class="h-10 w-10 text-brand-400" />
            </Link>
            
            <nav class="flex flex-col gap-8">
                <Link 
                    v-for="item in navItems" 
                    :key="item.route"
                    :href="route(item.route)"
                    class="p-3 rounded-2xl transition-all duration-300 group relative"
                    :class="isRouteActive(item.active) ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/40' : 'text-zinc-500 hover:text-white hover:bg-white/5'"
                >
                    <component :is="item.icon" class="w-6 h-6" />
                    <!-- Tooltip -->
                    <span class="absolute left-full ml-4 px-3 py-1 bg-zinc-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                        {{ item.label }}
                    </span>
                </Link>
            </nav>
        </aside>

        <!-- Top Header (Mobile & Desktop) -->
        <header class="sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-white/5 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
               <Link href="/" class="md:hidden">
                    <ApplicationLogo class="h-8 w-auto" />
               </Link>
               <h1 v-if="$slots.header" class="text-xl font-bold md:pl-4">
                   <slot name="header" />
               </h1>
            </div>

            <div class="flex items-center gap-4">
                <div v-if="page.props.auth.user" class="text-right hidden sm:block">
                    <div class="text-sm font-bold">{{ page.props.auth.user.name }}</div>
                    <div class="text-xs text-zinc-500">{{ page.props.auth.user.email }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-400/20 to-brand-600/20 border border-brand-500/30 flex items-center justify-center">
                   <UserIcon class="w-5 h-5 text-brand-400" />
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="relative z-10 px-6 py-8 mx-auto max-w-7xl">
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
            >
                <slot />
            </transition>
        </main>

        <!-- Mobile Bottom Navigation -->
        <nav class="md:hidden fixed bottom-6 left-6 right-6 h-16 glass-panel rounded-[2rem] flex items-center justify-around px-4 z-50 shadow-2xl shadow-black/50 border border-white/10">
            <Link 
                v-for="item in navItems" 
                :key="item.route"
                :href="route(item.route)"
                class="flex flex-col items-center gap-1 transition-all duration-300"
                :class="isRouteActive(item.active) ? 'text-brand-400' : 'text-zinc-500'"
            >
                <component :is="item.icon" class="w-6 h-6" :class="isRouteActive(item.active) ? 'scale-110' : ''" />
                <span class="text-[10px] font-bold uppercase tracking-widest">{{ item.label }}</span>
                <!-- Active Dot -->
                <div v-if="isRouteActive(item.active)" class="w-1 h-1 bg-brand-400 rounded-full"></div>
            </Link>
        </nav>
    </div>
</template>

<style>
.glass-panel {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(20px);
}
</style>
