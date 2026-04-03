<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import UserIcon from '@/Components/Icons/UserIcon.vue'
import HomeIcon from '@/Components/Icons/HomeIcon.vue'
import TicketIcon from '@/Components/Icons/TicketIcon.vue'
import TrophyIcon from '@/Components/Icons/TrophyIcon.vue'
import ChartIcon from '@/Components/Icons/ChartBarIcon.vue'
import SettingsIcon from '@/Components/Icons/SettingsIcon.vue'
import OfflineBanner from '@/Components/PWA/OfflineBanner.vue'

const page = usePage()
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)

const adminItems = [
    { label: 'Dashboard',      icon: ChartIcon,   route: 'admin.dashboard',      active: 'admin.dashboard' },
    { label: 'Sorteos',        icon: TicketIcon,  route: 'admin.raffles.index',  active: 'admin.raffles.*' },
    { label: 'Ventas/Cajas',   icon: ChartIcon,   route: 'admin.orders.index',   active: 'admin.orders.*' },
    { label: 'Usuarios',       icon: UserIcon,    route: 'admin.users.index',    active: 'admin.users.*' },
    { label: 'Configuración',  icon: SettingsIcon,route: 'admin.settings',       active: 'admin.settings' },
]

const isRouteActive = (routeName) => route().current(routeName)
</script>

<template>
    <div class="min-h-screen bg-zinc-950 text-zinc-100 selection:bg-brand-500 selection:text-white">
        <OfflineBanner />

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[55] lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <Transition
            enter-active-class="transition-transform duration-300"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-300"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-show="sidebarOpen || true"
                :class="[
                    'fixed left-0 top-0 bottom-0 bg-zinc-900 border-r border-white/5 flex flex-col z-[60] shadow-2xl transition-all duration-300',
                    // Mobile: slide in/out
                    'translate-x-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                    // Desktop: collapsible width
                    sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
                    'w-64',
                ]"
            >
                <!-- Logo + Collapse Toggle (desktop) -->
                <div class="p-6 pb-4 flex items-center justify-between">
                    <Link :href="route('admin.dashboard')" class="overflow-hidden">
                        <ApplicationLogo :class="['h-8 transition-all duration-300', sidebarCollapsed ? 'w-8' : 'w-auto']" />
                    </Link>
                    <button
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden lg:flex p-1.5 rounded-lg text-zinc-500 hover:text-white hover:bg-white/5 transition-colors ml-2 flex-shrink-0"
                        :title="sidebarCollapsed ? 'Expandir' : 'Colapsar'"
                    >
                        <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>
                </div>

                <div v-if="!sidebarCollapsed" class="px-6 mb-4">
                    <div class="px-3 py-1 inline-block rounded-lg bg-brand-500/10 border border-brand-500/20 text-[10px] uppercase font-black tracking-widest text-brand-400">
                        Panel Admin
                    </div>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <Link
                        v-for="item in adminItems"
                        :key="item.route"
                        :href="route(item.route)"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 font-semibold group relative"
                        :class="isRouteActive(item.active)
                            ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20'
                            : 'text-zinc-500 hover:text-zinc-200 hover:bg-white/5'"
                    >
                        <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                        <span
                            class="transition-all duration-300 overflow-hidden whitespace-nowrap"
                            :class="sidebarCollapsed ? 'lg:w-0 lg:opacity-0' : 'opacity-100'"
                        >
                            {{ item.label }}
                        </span>
                        <!-- Tooltip cuando collapsed -->
                        <span
                            v-if="sidebarCollapsed"
                            class="hidden lg:block absolute left-full ml-3 px-2 py-1 bg-zinc-800 border border-white/10 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50"
                        >
                            {{ item.label }}
                        </span>
                    </Link>
                </nav>

                <!-- User + Logout -->
                <div class="p-4 border-t border-white/5 space-y-3">
                    <div v-if="!sidebarCollapsed" class="flex items-center gap-3 px-2">
                        <div class="w-9 h-9 rounded-xl bg-zinc-800 flex items-center justify-center border border-white/10 uppercase font-black text-brand-500 flex-shrink-0 text-sm">
                            {{ page.props.auth.user.name.charAt(0) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">{{ page.props.auth.user.name }}</p>
                            <p class="text-xs text-zinc-500 truncate capitalize">{{ page.props.auth.user.role }}</p>
                        </div>
                    </div>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/5 hover:bg-red-500/10 text-zinc-500 hover:text-red-400 transition-colors font-semibold border border-white/5 text-sm"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Cerrar Sesión</span>
                    </Link>
                </div>
            </aside>
        </Transition>

        <!-- Main Content -->
        <div
            class="flex flex-col transition-all duration-300"
            :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'"
        >
            <!-- Top Header -->
            <header class="sticky top-0 h-16 flex items-center px-6 bg-zinc-950/80 backdrop-blur-md z-40 border-b border-white/5 justify-between">
                <!-- Mobile hamburger -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 text-zinc-400 hover:text-white transition-colors rounded-xl hover:bg-white/5"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Breadcrumb desktop -->
                <div class="hidden lg:flex items-center gap-3 text-sm font-medium text-zinc-500">
                    <Link :href="route('admin.dashboard')" class="hover:text-white transition-colors">Admin</Link>
                    <span class="opacity-30">/</span>
                    <span class="text-white">
                        <slot name="headerText">Dashboard</slot>
                    </span>
                </div>

                <div class="flex items-center gap-4 ml-auto">
                    <!-- Payments pending badge — slot opcional -->
                    <slot name="headerBadge" />

                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-green-500/10 border border-green-500/20 text-[10px] font-black text-green-500">
                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                        <span>SISTEMA ONLINE</span>
                    </div>

                    <Link href="/" class="hidden lg:flex text-xs font-bold text-zinc-500 hover:text-white transition-colors items-center gap-2 border-l border-white/10 pl-4">
                        <span>Sitio público</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </Link>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-6 lg:p-10">
                <div v-if="$slots.header" class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div class="space-y-1">
                        <h1 class="text-2xl lg:text-3xl font-black text-white leading-none">
                            <slot name="header" />
                        </h1>
                        <p v-if="$slots.description" class="text-zinc-500 font-medium text-sm">
                            <slot name="description" />
                        </p>
                    </div>
                    <div v-if="$slots.actions" class="flex items-center gap-3">
                        <slot name="actions" />
                    </div>
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
