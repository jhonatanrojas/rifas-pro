<script setup>
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseInput from '@/Components/Base/BaseInput.vue'
import BaseBadge from '@/Components/Base/BaseBadge.vue'
import BaseAvatar from '@/Components/Base/BaseAvatar.vue'
import BaseSpinner from '@/Components/Base/BaseSpinner.vue'
import BaseCard from '@/Components/Base/BaseCard.vue'
import BaseEmpty from '@/Components/Base/BaseEmpty.vue'
import BasePullToRefresh from '@/Components/Base/BasePullToRefresh.vue'
import BaseToast from '@/Components/Base/BaseToast.vue'

const inputValue = ref('Rifas SaaS')
const brandToastVisible = ref(true)
const refreshing = ref(false)
const lastUpdated = ref(new Date())

const pwaChecklist = [
    { label: 'Manifest configurado', status: 'done', note: 'vite-plugin-pwa genera el manifest y los icons.' },
    { label: 'Metas PWA', status: 'done', note: 'app.blade.php define theme color y apple web app tags.' },
    { label: 'Banner de instalacion', status: 'done', note: 'InstallBanner usa beforeinstallprompt y localStorage.' },
    { label: 'Banner offline', status: 'done', note: 'OfflineBanner escucha navigator.onLine.' },
    { label: 'Prompt de update', status: 'done', note: 'UpdatePrompt escucha el service worker updatefound.' },
    { label: 'Consentimiento push', status: 'done', note: 'PushNotificationConsent comparte VAPID y suscribe.' },
    { label: 'Demo /dev/components', status: 'done', note: 'Esta pagina agrupa componentes base y PWA.' },
    { label: 'Lighthouse >= 90', status: 'pending', note: 'No puedo certificarlo sin correr el audit en navegador.' },
]

const toastVariants = [
    { variant: 'info', title: 'Informacion', message: 'Mensaje de referencia para feedback neutro.' },
    { variant: 'success', title: 'Exito', message: 'Operacion completada correctamente.' },
    { variant: 'warning', title: 'Atencion', message: 'Algo requiere revision manual.' },
    { variant: 'error', title: 'Error', message: 'Se produjo un problema en el flujo.' },
    { variant: 'brand', title: 'Marca', message: 'Estado destacado con color principal.' },
]

const timeLabel = computed(() => lastUpdated.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }))

async function handleRefresh() {
    refreshing.value = true
    await new Promise((resolve) => setTimeout(resolve, 900))
    lastUpdated.value = new Date()
    refreshing.value = false
}
</script>

<template>
    <Head title="Dev Components" />

    <AppLayout>
        <template #header>Dev Components</template>
        <template #description>Biblioteca visual para revisar componentes base, PWA y estados de UI.</template>

        <div class="space-y-10">
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">Estado</p>
                            <h3 class="text-lg font-black text-white">Componentes base</h3>
                        </div>
                    </template>
                    <p class="text-3xl font-black text-white">Listos</p>
                    <p class="text-sm text-surface-400 mt-2">Botones, badges, inputs, avatar, spinner, empty y card ya están montados.</p>
                </BaseCard>

                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">PWA</p>
                            <h3 class="text-lg font-black text-white">Banners activos</h3>
                        </div>
                    </template>
                    <p class="text-3xl font-black text-white">4</p>
                    <p class="text-sm text-surface-400 mt-2">Install, offline, update y push consent viven en los layouts globales.</p>
                </BaseCard>

                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase text-surface-400">Ultima demo</p>
                            <h3 class="text-lg font-black text-white">Pull to refresh</h3>
                        </div>
                    </template>
                    <p class="text-3xl font-black text-white">{{ timeLabel }}</p>
                    <p class="text-sm text-surface-400 mt-2">Usa el gesto o el boton para simular una recarga de lista.</p>
                </BaseCard>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">UI Kit</p>
                            <h3 class="text-lg font-black text-white">Controles base</h3>
                        </div>
                    </template>

                    <div class="space-y-6">
                        <div class="flex flex-wrap gap-3">
                            <BaseButton variant="primary">Primary</BaseButton>
                            <BaseButton variant="secondary">Secondary</BaseButton>
                            <BaseButton variant="neon">Neon</BaseButton>
                            <BaseButton variant="outline">Outline</BaseButton>
                        </div>

                        <BaseInput v-model="inputValue" label="Input base" placeholder="Escribe algo..." required />

                        <div class="flex flex-wrap items-center gap-2">
                            <BaseBadge variant="brand">Brand</BaseBadge>
                            <BaseBadge variant="success">Success</BaseBadge>
                            <BaseBadge variant="warning">Warning</BaseBadge>
                            <BaseBadge variant="error">Error</BaseBadge>
                            <BaseBadge variant="surface">Surface</BaseBadge>
                        </div>

                        <div class="flex items-center gap-4">
                            <BaseAvatar name="Rifas SaaS" size="lg" />
                            <BaseAvatar name="Ana Perez" size="lg" />
                            <BaseAvatar src="/images/placeholder-raffle.png" name="Cover" size="lg" shape="rounded" />
                            <BaseSpinner size="lg" color="brand" />
                        </div>
                    </div>
                </BaseCard>

                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">Estados</p>
                            <h3 class="text-lg font-black text-white">Toasts y vacios</h3>
                        </div>
                    </template>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-3">
                            <BaseToast
                                v-for="item in toastVariants"
                                :key="item.variant"
                                :variant="item.variant"
                                :title="item.title"
                                :message="item.message"
                                :show="true"
                                :dismissible="false"
                            />
                        </div>

                        <div class="pt-2 flex gap-3">
                            <button class="btn-secondary px-4 py-2 text-sm font-bold" @click="brandToastVisible = true">
                                Mostrar toast
                            </button>
                            <button class="btn-secondary px-4 py-2 text-sm font-bold" @click="brandToastVisible = false">
                                Ocultar toast
                            </button>
                        </div>

                        <BaseEmpty
                            title="Sin contenido"
                            message="Este estado vacio se usa en listados, filtrados y secciones sin datos."
                            icon="info"
                        />
                    </div>
                </BaseCard>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">PWA</p>
                            <h3 class="text-lg font-black text-white">Revisión fina de entregables</h3>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <div
                            v-for="item in pwaChecklist"
                            :key="item.label"
                            class="flex items-start gap-3 rounded-2xl border border-white/5 bg-white/3 p-4"
                        >
                            <span
                                :class="[
                                    'mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-xs font-black',
                                    item.status === 'done' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300'
                                ]"
                            >
                                {{ item.status === 'done' ? '✓' : '!' }}
                            </span>
                            <div>
                                <p class="font-bold text-white">{{ item.label }}</p>
                                <p class="text-sm text-surface-400 mt-1">{{ item.note }}</p>
                            </div>
                        </div>
                    </div>
                </BaseCard>

                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">Gestos</p>
                            <h3 class="text-lg font-black text-white">Pull to refresh demo</h3>
                        </div>
                    </template>

                    <BasePullToRefresh :refreshing="refreshing" @refresh="handleRefresh">
                        <div class="space-y-4">
                <div class="rounded-2xl border border-white/5 bg-surface/40 p-4">
                                <p class="text-sm text-surface-400">Toca y arrastra hacia abajo desde la parte superior en mobile para disparar el refresco.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-2xl bg-surface/50 border border-white/5 p-4">
                                    <p class="text-xs uppercase tracking-widest text-surface-500 font-bold">Estado</p>
                                    <p class="text-white font-bold mt-1">{{ refreshing ? 'Refrescando...' : 'Listo' }}</p>
                                </div>
                <div class="rounded-2xl bg-surface/50 border border-white/5 p-4">
                                    <p class="text-xs uppercase tracking-widest text-surface-500 font-bold">Ultima actualizacion</p>
                                    <p class="text-white font-bold mt-1">{{ timeLabel }}</p>
                                </div>
                            </div>
                        </div>
                    </BasePullToRefresh>
                </BaseCard>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">Layouts</p>
                            <h3 class="text-lg font-black text-white">PWA y navegacion</h3>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-white/5 bg-surface/40 p-4">
                            <p class="font-bold text-white">AppLayout</p>
                            <p class="text-sm text-surface-400 mt-1">Bottom nav en mobile + sidebar desktop + banners PWA.</p>
                        </div>
                <div class="rounded-2xl border border-white/5 bg-surface/40 p-4">
                            <p class="font-bold text-white">GuestLayout</p>
                            <p class="text-sm text-surface-400 mt-1">Header sticky, login/register y banner de instalacion.</p>
                        </div>
                <div class="rounded-2xl border border-white/5 bg-surface/40 p-4">
                            <p class="font-bold text-white">AdminLayout</p>
                            <p class="text-sm text-surface-400 mt-1">Sidebar colapsable y estructura para dashboard y sorteos.</p>
                        </div>
                <div class="rounded-2xl border border-white/5 bg-surface/40 p-4">
                            <p class="font-bold text-white">Inertia + Motion</p>
                            <p class="text-sm text-surface-400 mt-1">Progress bar, transiciones y motion plugin ya integrados.</p>
                        </div>
                    </div>
                </BaseCard>

                <BaseCard>
                    <template #header>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-surface-400">Entregable</p>
                            <h3 class="text-lg font-black text-white">Revision rapida</h3>
                        </div>
                    </template>

                    <BaseToast
                        variant="brand"
                        title="Estado de la fase 4"
                        message="La base visual y PWA están bastante completas; lo que sigue pendiente es el audit Lighthouse en navegador y una demo de componentes formal en esta misma página, que ya quedó creada."
                        :show="brandToastVisible"
                        action-label="Ir al inicio"
                        action-href="/"
                    />
                </BaseCard>
            </section>
        </div>
    </AppLayout>
</template>
