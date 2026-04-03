<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import WinnerCard from '@/Components/WinnerCard.vue'
import BaseEmpty from '@/Components/Base/BaseEmpty.vue'
import BaseModal from '@/Components/Base/BaseModal.vue'

const props = defineProps({
    winners: { type: Array, default: () => [] },
    raffles: { type: Array, default: () => [] },
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user ?? null)

const selectedRaffleId = ref(null)
const selectedWinner = ref(null)
const detailOpen = ref(false)
const testimonyOpen = ref(false)

const testimonyForm = useForm({
    testimony: '',
    photo: null,
})

const filteredWinners = computed(() => {
    if (!selectedRaffleId.value) return props.winners
    return props.winners.filter((winner) => winner.raffle?.id === selectedRaffleId.value)
})

function selectRaffle(id) {
    selectedRaffleId.value = id
}

function openWinner(winner) {
    selectedWinner.value = winner
    detailOpen.value = true
    testimonyOpen.value = false
    testimonyForm.reset()
    testimonyForm.testimony = winner.testimony || ''
    testimonyForm.photo = null
}

function closeDetail() {
    detailOpen.value = false
    testimonyOpen.value = false
}

function openTestimony() {
    if (!selectedWinner.value) return
    testimonyOpen.value = true
    testimonyForm.testimony = selectedWinner.value.testimony || ''
    testimonyForm.photo = null
}

function handlePhotoChange(event) {
    testimonyForm.photo = event.target.files?.[0] ?? null
}

function submitTestimony() {
    if (!selectedWinner.value) return

    testimonyForm.post(route('winners.testimony.store', selectedWinner.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedWinner.value) {
                selectedWinner.value.testimony = testimonyForm.testimony
            }
            testimonyOpen.value = false
        },
    })
}
</script>

<template>
    <div class="space-y-6">
        <div v-if="raffles.length > 0">
            <div class="hidden md:flex flex-wrap gap-2">
                <button
                    @click="selectRaffle(null)"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-semibold transition-all border',
                        selectedRaffleId === null
                            ? 'bg-brand-600 border-brand-500 text-white shadow-lg shadow-brand-500/20'
                            : 'bg-surface-light border-surface-300/20 text-surface-300 hover:border-brand-500/50 hover:text-white'
                    ]"
                >
                    Todos los ganadores
                </button>
                <button
                    v-for="raffle in raffles"
                    :key="raffle.id"
                    @click="selectRaffle(raffle.id)"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-semibold transition-all border',
                        selectedRaffleId === raffle.id
                            ? 'bg-brand-600 border-brand-500 text-white shadow-lg shadow-brand-500/20'
                            : 'bg-surface-light border-surface-300/20 text-surface-300 hover:border-brand-500/50 hover:text-white'
                    ]"
                >
                    {{ raffle.title }}
                </button>
            </div>

            <div class="md:hidden">
                <select
                    v-model="selectedRaffleId"
                    class="w-full bg-surface-light border border-surface-300/20 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500 transition-colors"
                >
                    <option :value="null">Todos los ganadores</option>
                    <option v-for="raffle in raffles" :key="raffle.id" :value="raffle.id">
                        {{ raffle.title }}
                    </option>
                </select>
            </div>
        </div>

        <BaseEmpty
            v-if="filteredWinners.length === 0"
            title="Aun no hay ganadores"
            message="Cuando finalice el sorteo, los ganadores apareceran aqui."
            icon="empty"
        />

        <div v-else class="columns-1 md:columns-2 lg:columns-3 gap-4">
            <div
                v-for="(winner, index) in filteredWinners"
                :key="winner.id ?? index"
                class="break-inside-avoid mb-4 cursor-pointer"
                @click="openWinner(winner)"
            >
                <WinnerCard :winner="winner" />
            </div>
        </div>

        <BaseModal :show="detailOpen" maxWidth="2xl" @close="closeDetail">
            <div v-if="selectedWinner" class="p-6 sm:p-8 space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-surface-400">Ganador</p>
                        <h3 class="text-2xl font-black text-white">{{ selectedWinner.user?.name }}</h3>
                        <p class="text-sm text-surface-400 mt-1">
                            Rifa: {{ selectedWinner.raffle?.title }} | Ticket #{{ String(selectedWinner.ticket?.number ?? 0).padStart(4, '0') }}
                        </p>
                    </div>
                    <button @click="closeDetail" class="text-surface-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedWinner.photo_path" class="rounded-2xl overflow-hidden border border-surface-300/10">
                    <img
                        :src="`/storage/${selectedWinner.photo_path}`"
                        :alt="selectedWinner.user?.name"
                        class="w-full max-h-[320px] object-cover"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-surface-light rounded-2xl p-4 border border-surface-300/10">
                        <p class="text-[10px] uppercase tracking-widest text-surface-400 font-bold mb-1">Premio</p>
                        <p class="text-brand-400 font-bold">{{ selectedWinner.prize_description }}</p>
                    </div>
                    <div class="bg-surface-light rounded-2xl p-4 border border-surface-300/10">
                        <p class="text-[10px] uppercase tracking-widest text-surface-400 font-bold mb-1">Publicado</p>
                        <p class="text-white font-semibold">{{ selectedWinner.drawn_at || 'Sin fecha' }}</p>
                    </div>
                </div>

                <div v-if="selectedWinner.testimony" class="bg-surface-light rounded-2xl p-4 border border-surface-300/10">
                    <p class="text-[10px] uppercase tracking-widest text-surface-400 font-bold mb-2">Testimonio</p>
                    <p class="text-surface-200 leading-relaxed italic">{{ selectedWinner.testimony }}</p>
                </div>

                <div v-if="selectedWinner.is_own_winner || authUser?.id === selectedWinner.user?.id" class="space-y-4">
                    <button
                        v-if="!testimonyOpen"
                        @click="openTestimony"
                        class="w-full btn-primary py-3 font-bold"
                    >
                        Añadir mi testimonio
                    </button>

                    <div v-else class="space-y-4 bg-surface-light rounded-2xl p-4 border border-surface-300/10">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-surface-400 mb-2">Tu testimonio</label>
                            <textarea
                                v-model="testimonyForm.testimony"
                                rows="5"
                                class="w-full bg-surface-dark border border-surface-300/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-500"
                                placeholder="Cuéntanos como fue tu experiencia..."
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-surface-400 mb-2">Foto opcional</label>
                            <input
                                type="file"
                                accept="image/*"
                                @change="handlePhotoChange"
                                class="block w-full text-sm text-surface-300 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-white hover:file:bg-brand-400"
                            />
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="submitTestimony"
                                :disabled="testimonyForm.processing"
                                class="btn-primary px-5 py-3 font-bold flex-1"
                            >
                                <span v-if="testimonyForm.processing">Guardando...</span>
                                <span v-else>Enviar</span>
                            </button>
                            <button
                                @click="testimonyOpen = false"
                                class="px-5 py-3 rounded-xl border border-surface-300/20 text-surface-300 hover:text-white hover:border-brand-500/50"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </BaseModal>
    </div>
</template>
