<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BaseSpinner from '@/Components/Base/BaseSpinner.vue'
import BaseEmpty from '@/Components/Base/BaseEmpty.vue'

const props = defineProps({
    affiliate: {
        type: Object,
        default: null,
        // null | { code, commission_rate }
    },
    stats: {
        type: Object,
        default: () => ({ clicks: 0, conversions: 0, pending_amount: 0, paid_amount: 0 }),
    },
    conversions: {
        type: Array,
        default: () => [],
        // [{ order_id, commission_amount, status, created_at }]
    },
})

// Generate affiliate code
const generateForm = useForm({})

function generateCode() {
    generateForm.post(route('affiliates.generate_code'))
}

// Referral URL
const referralUrl = computed(() => {
    if (!props.affiliate?.code) return ''
    return `${window.location.origin}?ref=${props.affiliate.code}`
})

// Copy state
const codeCopied = ref(false)
const urlCopied = ref(false)

function copyCode() {
    if (!props.affiliate?.code) return
    navigator.clipboard.writeText(props.affiliate.code).then(() => {
        codeCopied.value = true
        setTimeout(() => { codeCopied.value = false }, 2000)
    })
}

function copyUrl() {
    if (!referralUrl.value) return
    navigator.clipboard.writeText(referralUrl.value).then(() => {
        urlCopied.value = true
        setTimeout(() => { urlCopied.value = false }, 2000)
    })
}

function formatMoney(amount) {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(amount ?? 0)
}

function formatDate(dateStr) {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('es-ES', { dateStyle: 'medium' })
}

const conversionBadge = (status) => {
    const map = {
        pending:  { label: 'Pendiente', classes: 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30' },
        approved: { label: 'Aprobada',  classes: 'bg-green-500/20 text-green-300 border-green-500/30' },
        paid:     { label: 'Pagada',    classes: 'bg-brand-500/20 text-brand-300 border-brand-500/30' },
        rejected: { label: 'Rechazada', classes: 'bg-red-500/20 text-red-300 border-red-500/30' },
    }
    return map[status] ?? { label: status, classes: 'bg-surface-lighter text-surface-300 border-surface-300/20' }
}

const metrics = computed(() => [
    {
        label: 'Clics totales',
        value: props.stats?.clicks ?? 0,
        icon: '👆',
        color: 'text-brand-400',
        bg: 'bg-brand-500/10',
    },
    {
        label: 'Conversiones',
        value: props.stats?.conversions ?? 0,
        icon: '✅',
        color: 'text-green-400',
        bg: 'bg-green-500/10',
    },
    {
        label: 'Comisión pendiente',
        value: formatMoney(props.stats?.pending_amount),
        icon: '⏳',
        color: 'text-yellow-400',
        bg: 'bg-yellow-500/10',
    },
    {
        label: 'Comisión pagada',
        value: formatMoney(props.stats?.paid_amount),
        icon: '💰',
        color: 'text-accent-neon',
        bg: 'bg-accent-neon/10',
    },
])
</script>

<template>
    <Head title="Panel de Afiliados" />

    <AppLayout>
        <template #header>Afiliados</template>

        <div class="space-y-8">

            <!-- Header -->
            <div>
                <h2 class="text-2xl font-black text-white">Panel de Afiliados</h2>
                <p class="text-surface-400 text-sm mt-0.5">
                    Gana comisiones compartiendo rifas con tu red
                </p>
            </div>

            <!-- ===== NO AFFILIATE: invite to generate ===== -->
            <div v-if="!affiliate" class="glass-card rounded-2xl p-8 border border-surface-300/10 text-center space-y-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-brand-500/10 flex items-center justify-center text-4xl">
                    🔗
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-white">Únete al programa de afiliados</h3>
                    <p class="text-surface-400 text-sm max-w-md mx-auto leading-relaxed">
                        Genera tu código único y empieza a ganar comisiones por cada venta que traigas.
                        Sin límites, sin complicaciones.
                    </p>
                </div>

                <button
                    @click="generateCode"
                    :disabled="generateForm.processing"
                    class="btn-primary px-8 py-3 font-bold text-base inline-flex items-center gap-2"
                >
                    <BaseSpinner v-if="generateForm.processing" size="sm" color="white" />
                    <span v-else>🚀</span>
                    Generar mi código
                </button>
            </div>

            <!-- ===== HAS AFFILIATE ===== -->
            <template v-else>

                <!-- Affiliate code card -->
                <div class="glass-card rounded-2xl p-6 border border-brand-500/20 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-500/10 flex items-center justify-center text-xl">🔗</div>
                        <div>
                            <p class="text-xs text-surface-400 uppercase tracking-widest font-semibold">Tu código de afiliado</p>
                            <p class="text-xs text-brand-400">Comisión: {{ (affiliate.commission_rate * 100).toFixed(0) }}% por conversión</p>
                        </div>
                    </div>

                    <!-- Code display -->
                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-surface-light rounded-xl border border-surface-300/20 px-4 py-3 flex items-center justify-between">
                            <code class="text-xl font-black text-white tracking-widest">
                                {{ affiliate.code }}
                            </code>
                            <button
                                @click="copyCode"
                                class="ml-3 p-2 rounded-lg bg-surface-lighter hover:bg-surface-300/20 transition-colors text-surface-400 hover:text-white flex items-center gap-1.5 text-xs font-semibold"
                            >
                                <svg v-if="!codeCopied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <svg v-else class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ codeCopied ? 'Copiado' : 'Copiar' }}
                            </button>
                        </div>
                    </div>

                    <!-- Full referral URL -->
                    <div class="space-y-1.5">
                        <p class="text-xs text-surface-400 font-semibold uppercase tracking-widest">URL de referido</p>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-surface-light rounded-xl border border-surface-300/20 px-3 py-2.5 overflow-hidden">
                                <p class="text-xs text-surface-300 font-mono truncate">{{ referralUrl }}</p>
                            </div>
                            <button
                                @click="copyUrl"
                                :class="[
                                    'flex-shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold border transition-all',
                                    urlCopied
                                        ? 'bg-green-500/20 border-green-500/30 text-green-300'
                                        : 'bg-surface-light border-surface-300/20 text-surface-300 hover:border-brand-500/50 hover:text-white'
                                ]"
                            >
                                {{ urlCopied ? '✓ Copiado' : 'Copiar URL' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4 Metric cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="metric in metrics"
                        :key="metric.label"
                        class="glass-card rounded-2xl p-5 border border-surface-300/10 space-y-3"
                    >
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center text-xl', metric.bg]">
                            {{ metric.icon }}
                        </div>
                        <div>
                            <p :class="['text-2xl font-black', metric.color]">{{ metric.value }}</p>
                            <p class="text-xs text-surface-400 font-semibold mt-0.5">{{ metric.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Conversions table -->
                <div class="glass-card rounded-2xl border border-surface-300/10 overflow-hidden">
                    <div class="px-6 py-4 border-b border-surface-300/10">
                        <h3 class="font-bold text-white">Últimas conversiones</h3>
                    </div>

                    <BaseEmpty
                        v-if="conversions.length === 0"
                        title="Sin conversiones aún"
                        message="Comparte tu enlace para empezar a ganar comisiones."
                        icon="empty"
                        class="rounded-none border-0"
                    />

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-surface-300/10">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-surface-400 uppercase tracking-widest">Orden</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-surface-400 uppercase tracking-widest">Comisión</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-surface-400 uppercase tracking-widest">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-surface-400 uppercase tracking-widest">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-300/5">
                                <tr
                                    v-for="conversion in conversions"
                                    :key="conversion.order_id"
                                    class="hover:bg-surface-lighter/30 transition-colors"
                                >
                                    <td class="px-6 py-3 text-surface-300 font-mono text-xs">
                                        #{{ conversion.order_id }}
                                    </td>
                                    <td class="px-6 py-3 font-bold text-brand-400">
                                        {{ formatMoney(conversion.commission_amount) }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border',
                                                conversionBadge(conversion.status).classes
                                            ]"
                                        >
                                            {{ conversionBadge(conversion.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-surface-400 text-xs">
                                        {{ formatDate(conversion.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </template>

        </div>
    </AppLayout>
</template>
