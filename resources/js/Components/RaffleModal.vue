<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import NumberGrid from '@/Components/NumberGrid.vue'
import PaymentUploader from '@/Components/PaymentUploader.vue'

const props = defineProps({
    isOpen: Boolean,
    raffleId: Number,
    auth: Object,
    initialSelection: {
        type: Array,
        default: () => [],
    },
    initialStep: {
        type: Number,
        default: 1,
    },
})

const emit = defineEmits(['close'])
const toast = useToast()

const currentStep = ref(1)
const loading = ref(false)
const processing = ref(false)
const raffle = ref(null)
const tickets = ref([])
const combos = ref([])

const selectionMode = ref('manual')
const manualTickets = ref([])
const randomQuantity = ref('')

const form = ref({
    name: '',
    email: '',
    phone: '',
})
const errors = ref({})

const paymentForm = ref({
    method: 'zelle',
    reference: '',
})
const paymentProof = ref(null)

function resetState() {
    currentStep.value = props.initialSelection.length > 0
        ? Math.max(2, props.initialStep || 1)
        : (props.initialStep || 1)

    selectionMode.value = props.initialSelection.length > 0 ? 'manual' : 'manual'
    manualTickets.value = [...props.initialSelection]
    randomQuantity.value = ''
    paymentProof.value = null
    paymentForm.value = { method: 'zelle', reference: '' }
    errors.value = {}

    if (props.auth?.user) {
        form.value = {
            name: props.auth.user.name || '',
            email: props.auth.user.email || '',
            phone: props.auth.user.phone || '',
        }
    } else {
        form.value = { name: '', email: '', phone: '' }
    }
}

watch(() => props.isOpen, async (isOpen) => {
    if (!isOpen || !props.raffleId) {
        resetState()
        return
    }

    loading.value = true
    try {
        const response = await axios.get(`/api/raffles/${props.raffleId}`)
        raffle.value = response.data?.raffle?.data ?? response.data?.raffle ?? null
        tickets.value = response.data?.tickets?.data ?? response.data?.tickets ?? []
        combos.value = response.data?.combos?.data ?? response.data?.combos ?? raffle.value?.combos ?? []
        resetState()
    } catch (error) {
        console.error(error)
        toast.error('Error al cargar la informacion del sorteo.')
    } finally {
        loading.value = false
    }
})

const availableTickets = computed(() => tickets.value.filter((ticket) => ticket.status === 'available'))

const totals = computed(() => {
    if (!raffle.value) {
        return { subtotal: 0, discount: 0, total: 0, quantity: 0 }
    }

    const quantity = selectionMode.value === 'manual'
        ? manualTickets.value.length
        : (parseInt(randomQuantity.value, 10) || 0)

    const price = Number(raffle.value.ticket_price || 0)
    const subtotal = quantity * price

    let discount = 0
    if (combos.value.length > 0) {
        combos.value.forEach((combo) => {
            if (quantity >= combo.quantity) {
                const comboSubtotal = combo.quantity * price
                const comboDiscount = (comboSubtotal - Number(combo.price)) * Math.floor(quantity / combo.quantity)
                if (comboDiscount > discount) discount = comboDiscount
            }
        })
    }

    return {
        subtotal,
        discount,
        total: subtotal - discount,
        quantity,
    }
})

function toggleTicket(number) {
    const ticket = tickets.value.find((item) => item.number === number)
    if (!ticket || ticket.status !== 'available') return

    if (manualTickets.value.includes(number)) {
        manualTickets.value = manualTickets.value.filter((value) => value !== number)
        return
    }

    if (manualTickets.value.length >= 100) {
        toast.warning('Has alcanzado el limite maximo por transaccion (100).')
        return
    }

    manualTickets.value.push(number)
}

function selectMachine() {
    selectionMode.value = 'random'
    manualTickets.value = []
}

function applyCombo(quantity) {
    selectionMode.value = 'random'
    randomQuantity.value = String(quantity)
    manualTickets.value = []
}

function nextStep() {
    if (currentStep.value === 1 && totals.value.quantity > 0) {
        currentStep.value = 2
        return
    }

    if (currentStep.value === 2) {
        validateForm()
        if (Object.keys(errors.value).length === 0) currentStep.value = 3
        return
    }

    if (currentStep.value === 3) {
        submitPurchase()
    }
}

function prevStep() {
    if (currentStep.value > 1) currentStep.value--
}

function validateForm() {
    errors.value = {}

    if (!form.value.name.trim()) errors.value.name = 'El nombre es obligatorio.'
    if (!form.value.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) errors.value.email = 'Correo electronico invalido.'
    if (!form.value.phone.trim() || form.value.phone.length < 7) errors.value.phone = 'Telefono de contacto invalido.'

    if (Object.keys(errors.value).length > 0) {
        toast.error('Por favor, corrige los errores del formulario.')
    }
}

async function submitPurchase() {
    if (!paymentProof.value) {
        toast.warning('Debes subir el comprobante de pago para continuar.')
        return
    }

    processing.value = true
    try {
        const payload = new FormData()
        payload.append('name', form.value.name)
        payload.append('email', form.value.email)
        payload.append('phone', form.value.phone)
        payload.append('quantity', totals.value.quantity)
        payload.append('amount', totals.value.total)
        payload.append('payment_method', paymentForm.value.method)
        payload.append('reference', paymentForm.value.reference || '-')
        payload.append('proof', paymentProof.value)

        if (selectionMode.value === 'manual') {
            payload.append('manual_tickets', JSON.stringify(manualTickets.value))
        }

        await axios.post(`/api/raffles/${raffle.value.id}/purchase`, payload)

        toast.success('Compra registrada con exito.')
        currentStep.value = 4
    } catch (error) {
        console.error(error)
        toast.error('No se pudo procesar el pago. Intentalo de nuevo.')
    } finally {
        processing.value = false
    }
}

function closeModal() {
    if (processing.value) return
    emit('close')
}
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="fixed inset-0 bg-surface-dark/90 backdrop-blur-md transition-opacity" @click="closeModal"></div>

        <div class="relative w-full max-w-5xl h-[95vh] md:h-[85vh] bg-surface-dark border border-surface-300/20 md:rounded-3xl shadow-2xl flex flex-col md:flex-row overflow-hidden transform transition-all mt-auto md:mt-0 animate-slide-up-modal">
            <div v-if="loading" class="absolute inset-0 bg-surface-dark z-20 flex flex-col items-center justify-center">
                <svg class="animate-spin h-10 w-10 text-brand-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div v-if="raffle && !loading" class="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
                <div class="p-6 border-b border-surface-300/10 flex justify-between items-center sticky top-0 bg-surface-dark/95 backdrop-blur z-10">
                    <div>
                        <span class="text-xs font-bold text-brand-400 tracking-wider uppercase">Paso {{ currentStep }} de 4</span>
                        <h2 class="text-xl font-bold text-white leading-tight">
                            {{ currentStep === 1 ? 'Seleccion de Tickets' : currentStep === 2 ? 'Tus Datos' : currentStep === 3 ? 'Completar Pago' : 'Compra Recibida' }}
                        </h2>
                    </div>
                    <button v-if="currentStep < 4" @click="closeModal" class="p-2 text-surface-400 hover:text-white rounded-full bg-surface-light">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 md:p-8 flex-1 relative">
                    <transition name="slide-fade" mode="out-in">
                        <div v-if="currentStep === 1" class="w-full space-y-6">
                            <div v-if="combos.length > 0" class="space-y-3">
                                <h3 class="text-sm font-bold text-surface-300 mb-3">Combos Flash</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="combo in combos"
                                        :key="combo.id"
                                        @click="applyCombo(combo.quantity)"
                                        class="group px-4 py-2 border border-brand-500/30 rounded-xl bg-surface-lighter hover:bg-brand-500/20 transition-all font-medium text-white shadow-lg"
                                    >
                                        <span class="text-brand-400 text-xl font-bold mr-1">{{ combo.quantity }}</span>TICKETS
                                    </button>
                                </div>
                            </div>

                            <div class="mb-6 flex p-1 bg-surface-lighter rounded-xl">
                                <button
                                    @click="selectionMode = 'manual'"
                                    :class="[selectionMode === 'manual' ? 'bg-surface-dark text-brand-400 shadow' : 'text-surface-400 hover:text-white', 'flex-1 py-3 text-sm font-bold rounded-lg transition-all']"
                                >
                                    Elegir yo
                                </button>
                                <button
                                    @click="selectMachine"
                                    :class="[selectionMode === 'random' ? 'bg-surface-dark text-accent-neon shadow' : 'text-surface-400 hover:text-white', 'flex-1 py-3 text-sm font-bold rounded-lg transition-all']"
                                >
                                    Busqueda rapida
                                </button>
                            </div>

                            <div v-if="selectionMode === 'random'" class="bg-surface-lighter p-8 rounded-2xl border border-surface-300/10 text-center animate-fade-in">
                                <div class="w-20 h-20 mx-auto bg-surface-light rounded-full flex items-center justify-center text-4xl mb-4">🎰</div>
                                <h3 class="text-xl font-bold text-white mb-2">Maquina Magica</h3>
                                <p class="text-surface-400 mb-6 text-sm">Escoge cuantos numeros dejar a la suerte.</p>

                                <div class="flex flex-wrap gap-2 justify-center mb-5">
                                    <button v-for="qty in [1, 5, 10, 20]" :key="qty" @click="randomQuantity = String(qty)" class="px-4 py-2 rounded-xl bg-surface-dark border border-surface-300/10 text-white font-bold hover:border-brand-500 transition-colors">
                                        {{ qty }}
                                    </button>
                                </div>

                                <div class="relative flex items-center max-w-[200px] mx-auto mb-4">
                                    <button @click="randomQuantity = String(Math.max(1, (parseInt(randomQuantity || '1', 10) || 1) - 1))" class="w-12 h-12 bg-surface text-white rounded-l-xl text-2xl font-bold hover:bg-surface-300/40">-</button>
                                    <input type="number" v-model="randomQuantity" min="1" :max="availableTickets.length" class="w-full h-12 bg-surface border-y border-surface-300/20 text-white text-center text-xl font-bold focus:outline-none focus:border-brand-500">
                                    <button @click="randomQuantity = String((parseInt(randomQuantity || '0', 10) || 0) + 1)" class="w-12 h-12 bg-surface text-white rounded-r-xl text-2xl font-bold hover:bg-surface-300/40">+</button>
                                </div>
                            </div>

                            <div v-else class="space-y-3">
                                <p class="text-xs text-surface-400">Selecciona manualmente tus tickets y continua cuando estes listo.</p>
                                <NumberGrid
                                    v-model="manualTickets"
                                    :tickets="tickets"
                                    :ticket-price="Number(raffle.ticket_price)"
                                    :currency="raffle.currency"
                                    :max-selection="100"
                                    :show-summary="false"
                                />
                            </div>
                        </div>

                        <div v-else-if="currentStep === 2" class="w-full max-w-sm mx-auto pt-4">
                            <div class="space-y-4">
                                <div class="group">
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Nombre Completo</label>
                                    <input v-model="form.name" type="text" placeholder="Ej. Juan Perez"
                                        :class="['w-full bg-surface-light border-2 rounded-xl px-4 py-3.5 text-white transition-all focus:bg-surface outline-none', errors.name ? 'border-red-500 focus:border-red-500' : 'border-surface-300/10 focus:border-brand-500']">
                                    <p class="text-red-400 text-xs mt-1.5 font-medium" v-if="errors.name">{{ errors.name }}</p>
                                </div>
                                <div class="group">
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Correo Electronico</label>
                                    <input v-model="form.email" type="email" placeholder="tucorreo@email.com"
                                        :class="['w-full bg-surface-light border-2 rounded-xl px-4 py-3.5 text-white transition-all focus:bg-surface outline-none', errors.email ? 'border-red-500 focus:border-red-500' : 'border-surface-300/10 focus:border-brand-500']">
                                    <p class="text-red-400 text-xs mt-1.5 font-medium" v-if="errors.email">{{ errors.email }}</p>
                                </div>
                                <div class="group">
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Telefono (WhatsApp)</label>
                                    <input v-model="form.phone" type="tel" placeholder="+58 414 1234567"
                                        :class="['w-full bg-surface-light border-2 rounded-xl px-4 py-3.5 text-white transition-all focus:bg-surface outline-none', errors.phone ? 'border-red-500 focus:border-red-500' : 'border-surface-300/10 focus:border-brand-500']">
                                    <p class="text-red-400 text-xs mt-1.5 font-medium" v-if="errors.phone">{{ errors.phone }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="currentStep === 3" class="w-full max-w-md mx-auto pt-2">
                            <div class="space-y-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button @click="paymentForm.method = 'zelle'" :class="[paymentForm.method === 'zelle' ? 'border-brand-500 bg-brand-500/10 text-white' : 'border-surface-300/10 bg-surface-light text-surface-300', 'p-4 rounded-xl border-2 text-left transition-all']">Zelle</button>
                                    <button @click="paymentForm.method = 'binance'" :class="[paymentForm.method === 'binance' ? 'border-brand-500 bg-brand-500/10 text-white' : 'border-surface-300/10 bg-surface-light text-surface-300', 'p-4 rounded-xl border-2 text-left transition-all']">Binance</button>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Referencia / Nota</label>
                                    <input v-model="paymentForm.reference" type="text" placeholder="Ej. transferencia o referencia"
                                        class="w-full bg-surface-light border-2 border-surface-300/10 rounded-xl px-4 py-3.5 text-white focus:outline-none focus:border-brand-500">
                                </div>

                                <PaymentUploader v-model="paymentProof" />
                            </div>
                        </div>

                        <div v-else-if="currentStep === 4" class="w-full h-full flex flex-col items-center justify-center text-center p-8 animate-fade-in" style="min-height: 400px;">
                            <div class="w-20 h-20 rounded-full bg-brand-500/10 flex items-center justify-center text-4xl mb-6">✓</div>
                            <h3 class="text-2xl font-black text-white mb-2">Compra enviada</h3>
                            <p class="text-surface-400 max-w-md">Recibimos tu solicitud y el comprobante fue adjuntado correctamente.</p>
                        </div>
                    </transition>
                </div>
            </div>

            <div
                class="w-full md:w-80 bg-surface-lighter border-t md:border-t-0 md:border-l border-surface-300/20 p-6 flex flex-col justify-between"
                v-if="currentStep < 4 && raffle"
            >
                <div>
                    <h3 class="text-lg font-bold text-white mb-4">Resumen</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-surface-400">
                            <span>Seleccionados</span>
                            <span class="text-white font-bold">{{ totals.quantity }}</span>
                        </div>
                        <div class="flex justify-between text-surface-400">
                            <span>Subtotal</span>
                            <span class="text-white font-bold">{{ totals.subtotal.toFixed(2) }} {{ raffle.currency }}</span>
                        </div>
                        <div class="flex justify-between text-surface-400">
                            <span>Descuento</span>
                            <span class="text-white font-bold">-{{ totals.discount.toFixed(2) }} {{ raffle.currency }}</span>
                        </div>
                        <div class="flex justify-between text-white font-black text-base pt-2 border-t border-surface-300/10">
                            <span>Total</span>
                            <span>{{ totals.total.toFixed(2) }} {{ raffle.currency }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <button v-if="currentStep > 1" @click="prevStep" class="w-full py-3.5 bg-surface text-white font-bold rounded-xl transition-colors hover:bg-surface-light border border-surface-300/20">
                        Atras
                    </button>
                    <button @click="nextStep" :disabled="processing || (currentStep === 1 && totals.quantity === 0)" class="w-full py-4 rounded-xl font-black text-white bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="processing">Procesando...</span>
                        <span v-else>{{ currentStep === 1 ? 'Siguiente paso' : currentStep === 2 ? 'Continuar' : 'Confirmar envio' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 10px; }
</style>
