<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import InstallBanner from '@/Components/PWA/InstallBanner.vue'
import OfflineBanner from '@/Components/PWA/OfflineBanner.vue'
import PushNotificationConsent from '@/Components/PWA/PushNotificationConsent.vue'
import { Link } from '@inertiajs/vue3'

// Scroll-hide behavior: ocultar header al bajar, mostrar al subir
const headerVisible = ref(true)
let lastScrollY = 0

function handleScroll() {
    const currentY = window.scrollY
    headerVisible.value = currentY < lastScrollY || currentY < 60
    lastScrollY = currentY
}

onMounted(() => window.addEventListener('scroll', handleScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))
</script>

<template>
    <div class="min-h-screen bg-surface selection:bg-brand-500 selection:text-white">
        <!-- Offline Banner -->
        <OfflineBanner />
        <PushNotificationConsent />

        <!-- Decorative Background -->
        <div class="fixed inset-0 bg-mesh opacity-20 pointer-events-none"></div>
        <div class="fixed top-[-10%] right-[-10%] w-[400px] h-[400px] bg-brand-500/10 blur-[100px] rounded-full animate-float pointer-events-none"></div>
        <div class="fixed bottom-[-10%] left-[-10%] w-[300px] h-[300px] bg-accent-neon/10 blur-[80px] rounded-full pointer-events-none"></div>

        <!-- Sticky Header -->
        <header
            class="fixed top-0 left-0 right-0 z-50 transition-transform duration-300"
            :class="headerVisible ? 'translate-y-0' : '-translate-y-full'"
        >
            <div class="flex items-center justify-between px-6 py-4 bg-surface/80 backdrop-blur-md border-b border-white/5">
                <Link href="/">
                    <ApplicationLogo class="h-8 w-auto text-brand-400" />
                </Link>

                <div class="flex items-center gap-3">
                    <Link
                        :href="route('login')"
                        class="px-4 py-2 text-sm font-bold text-zinc-300 hover:text-white transition-colors rounded-xl hover:bg-white/5"
                    >
                        Iniciar Sesión
                    </Link>
                    <Link
                        :href="route('register')"
                        class="px-4 py-2 text-sm font-bold bg-brand-600 hover:bg-brand-500 text-white rounded-xl transition-colors shadow-lg shadow-brand-500/20"
                    >
                        Registrarse
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content (pt para compensar el header) -->
        <div class="flex min-h-screen flex-col items-center justify-center p-6 pt-24">
            <div class="relative z-10 w-full max-w-md">
                <div class="flex justify-center mb-8">
                    <Link href="/">
                        <ApplicationLogo class="h-16 w-16 text-brand-400 drop-shadow-[0_0_15px_rgba(59,130,246,0.3)]" />
                    </Link>
                </div>

                <div class="glass-panel p-8 sm:rounded-[2.5rem] shadow-2xl border border-white/10">
                    <div v-if="$slots.header" class="mb-6">
                        <slot name="header" />
                    </div>
                    <slot />
                </div>
            </div>
        </div>

        <!-- PWA Install Banner -->
        <InstallBanner />
    </div>
</template>

<style scoped>
.glass-panel {
    background: rgba(255, 255, 255, 0.03);
}
</style>
