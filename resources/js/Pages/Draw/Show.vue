<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DrawAnimation from '@/Components/DrawAnimation.vue'
import WinnerCard from '@/Components/WinnerCard.vue'
import BaseEmpty from '@/Components/Base/BaseEmpty.vue'

const props = defineProps({
    raffle: {
        type: Object,
        default: () => ({}),
    },
    winner: {
        type: Object,
        default: null,
    },
    winners: {
        type: Array,
        default: () => [],
    },
    auditHash: {
        type: String,
        default: null,
    },
    drawAudit: {
        type: Object,
        default: null,
    },
    canExecute: {
        type: Boolean,
        default: false,
    },
})

const raffleData = computed(() => props.raffle?.data ?? props.raffle ?? {})
const latestWinner = computed(() => props.winner ?? props.winners?.[0] ?? null)

const form = useForm({
    raffle_id: raffleData.value?.id,
    prize_description: '',
    confirm_draw: false,
    execution_mode: raffleData.value?.draw_type === 'external_lottery' ? 'manual_external' : 'automatic',
    winning_number: '',
    external_reference: '',
})

function submitDraw() {
    form.raffle_id = raffleData.value.id
    form.post(route('admin.draw.execute', raffleData.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('confirm_draw')
        },
    })
}
</script>

<template>
    <Head :title="`Preparar sorteo: ${raffleData.title}`" />

    <AdminLayout>
        <template #header>Preparar sorteo</template>
        <template #description>Revisa el resultado, el hash de auditoria y ejecuta el sorteo cuando estes listo.</template>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mt-10">
            <div class="xl:col-span-2 space-y-8">
                <div class="glass-panel rounded-[2.5rem] p-8 border-2 border-white/5 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.18) 1px, transparent 0); background-size: 24px 24px;"></div>

                    <div class="relative z-10 flex flex-wrap items-center justify-between gap-4 mb-8">
                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 tracking-widest mb-1">Rifa</p>
                            <h1 class="text-3xl font-black text-white leading-tight">{{ raffleData.title }}</h1>
                            <p class="text-zinc-400 text-sm mt-1">{{ raffleData.draw_date ? new Date(raffleData.draw_date).toLocaleString('es-ES') : 'Sin fecha' }}</p>
                        </div>

                        <Link :href="route('admin.raffles.show', raffleData.id)" class="btn-secondary text-sm">
                            Volver a stats
                        </Link>
                    </div>

                    <DrawAnimation
                        :raffle="raffleData"
                        :winner="latestWinner"
                        :audit-hash="auditHash"
                        :can-execute="canExecute"
                        :is-executing="form.processing"
                        @execute="submitDraw"
                    />
                </div>

                <div class="glass-panel rounded-[2.5rem] p-8 border-2 border-white/5">
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 tracking-widest mb-1">Ganadores</p>
                            <h2 class="text-xl font-black text-white">Historial reciente</h2>
                        </div>
                        <p class="text-sm text-zinc-500">{{ winners.length }} ganador{{ winners.length === 1 ? '' : 'es' }}</p>
                    </div>

                    <BaseEmpty
                        v-if="winners.length === 0"
                        title="Aun no hay ganadores"
                        message="Cuando ejecutes el sorteo, apareceran aqui."
                        icon="empty"
                    />

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <WinnerCard
                            v-for="winnerItem in winners"
                            :key="winnerItem.id"
                            :winner="winnerItem"
                        />
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="glass-panel p-8 rounded-[2.5rem] border-2 border-brand-500/10">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-brand-400 mb-6 border-b border-brand-500/10 pb-4">Control del sorteo</h3>

                    <div class="space-y-4">
                        <div class="p-5 bg-white/5 rounded-[1.5rem] border border-white/5">
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-2">Modo de ejecución</p>
                            <select
                                v-model="form.execution_mode"
                                class="w-full bg-surface-light border border-surface-300/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-500"
                            >
                                <option value="automatic">Automático</option>
                                <option value="manual_external">Manual / Externo</option>
                            </select>
                        </div>

                        <div v-if="form.execution_mode === 'manual_external'" class="p-5 bg-white/5 rounded-[1.5rem] border border-white/5 space-y-4">
                            <div>
                                <p class="text-[10px] font-black uppercase text-zinc-500 mb-2">Número ganador</p>
                                <input
                                    v-model="form.winning_number"
                                    type="number"
                                    min="0"
                                    class="w-full bg-surface-light border border-surface-300/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-500"
                                    placeholder="Ej: 1234"
                                />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-zinc-500 mb-2">Referencia externa</p>
                                <input
                                    v-model="form.external_reference"
                                    type="text"
                                    class="w-full bg-surface-light border border-surface-300/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-500"
                                    placeholder="Ej: Sorteo de lotería oficial del día"
                                />
                            </div>
                        </div>

                        <div class="p-5 bg-brand-500/5 rounded-[1.5rem] border border-brand-500/10">
                            <p class="text-[10px] font-black uppercase text-brand-400 mb-2">Premio</p>
                            <textarea
                                v-model="form.prize_description"
                                rows="4"
                                class="w-full bg-surface-light border border-surface-300/20 rounded-xl px-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-500"
                                placeholder="Describe el premio que se entregara al ganador"
                            />
                        </div>

                        <label class="flex items-start gap-3 p-4 rounded-[1.25rem] border border-white/5 bg-white/3">
                            <input v-model="form.confirm_draw" type="checkbox" class="mt-1 rounded border-zinc-600 text-brand-500 focus:ring-brand-500">
                            <span class="text-sm text-zinc-300">
                                Confirmo que revisé los boletos vendidos y quiero ejecutar este sorteo.
                            </span>
                        </label>

                        <button
                            @click="submitDraw"
                            :disabled="form.processing || !form.confirm_draw || !form.prize_description"
                            class="w-full py-4 rounded-2xl font-black text-black bg-yellow-500 hover:bg-yellow-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <span v-if="form.processing">Ejecutando...</span>
                            <span v-else>Ejecutar sorteo</span>
                        </button>
                    </div>
                </div>

                <div class="glass-panel p-8 rounded-[2.5rem] border-2 border-white/5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-4">Auditoria</p>
                    <p class="text-sm text-zinc-400 leading-relaxed mb-4">
                        El hash de participantes confirma que el sorteo puede verificarse posteriormente.
                    </p>
                    <code class="block break-all text-[11px] text-green-400 bg-surface rounded-xl p-4 border border-white/5">
                        {{ auditHash || 'Aun no existe auditoria para esta rifa.' }}
                    </code>
                    <div v-if="drawAudit" class="mt-4 text-xs text-zinc-400 space-y-1">
                        <p>Modo: <span class="text-white font-semibold">{{ drawAudit.execution_mode === 'manual_external' ? 'Manual / Externo' : 'Automático' }}</span></p>
                        <p v-if="drawAudit.winning_number !== null">Número externo: <span class="text-white font-semibold">{{ drawAudit.winning_number }}</span></p>
                        <p v-if="drawAudit.external_reference">Referencia: <span class="text-white font-semibold">{{ drawAudit.external_reference }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
