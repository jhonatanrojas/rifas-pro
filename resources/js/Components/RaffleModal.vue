<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

import { useToast } from 'vue-toastification';

const props = defineProps({
    isOpen: Boolean,
    raffleId: Number,
    auth: Object // Para pillar datos si el user está logueado
});

const emit = defineEmits(['close']);
const toast = useToast();

// ---------------- STATE ---------------- //
const currentStep = ref(1); // 1: Tickets, 2: Info, 3: Pago, 4: Confirmación
const loading = ref(false);
const processing = ref(false);
const raffle = ref(null);
const tickets = ref([]);
const combos = ref([]);

// Step 1: Selection
const manualTickets = ref([]);
const randomQuantity = ref("");
const selectionMode = ref('manual');

// Step 2: User Form
const form = ref({
    name: '',
    email: '',
    phone: ''
});
const errors = ref({});

// Step 3: Payment
const paymentForm = ref({
    method: 'zelle',
    reference: '',
    proof: null,
    proofPreview: null
});

// ---------------- DATA FETCHING ---------------- //
watch(() => props.isOpen, async (newVal) => {
    if (newVal && props.raffleId) {
        loading.value = true;
        try {
            const response = await axios.get(`/api/raffles/${props.raffleId}`);
            raffle.value = response.data.raffle;
            tickets.value = response.data.tickets;
            combos.value = response.data.combos || [];
            
            // Auto-fill user si hay auth
            if (props.auth && props.auth.user) {
                form.value = {
                    name: props.auth.user.name,
                    email: props.auth.user.email,
                    phone: props.auth.user.phone || ''
                };
            }
        } catch (error) {
            console.error(error);
            toast.error("Error al cargar la información del sorteo.");
        } finally {
            loading.value = false;
        }
    } else {
        // Drop reset on close
        currentStep.value = 1;
        manualTickets.value = [];
        randomQuantity.value = "";
        paymentForm.value = { method: 'zelle', reference: '', proof: null, proofPreview: null };
        errors.value = {};
    }
});

// ---------------- COMPUTED ---------------- //
const availableTickets = computed(() => tickets.value.filter(t => t.status === 'available'));

const totals = computed(() => {
    if (!raffle.value) return { subtotal: 0, discount: 0, total: 0, quantity: 0 };
    let qty = selectionMode.value === 'manual' ? manualTickets.value.length : (parseInt(randomQuantity.value) || 0);
    let subtotal = qty * parseFloat(raffle.value.ticket_price);
    
    let maxDiscount = 0;
    if (combos.value && combos.value.length > 0) {
        combos.value.forEach(combo => {
            if (qty >= combo.quantity) {
                const comboSubtotal = combo.quantity * parseFloat(raffle.value.ticket_price);
                const desc = (comboSubtotal - parseFloat(combo.price)) * Math.floor(qty / combo.quantity);
                if (desc > maxDiscount) maxDiscount = desc;
            }
        });
    }
    return {
        subtotal: subtotal,
        discount: maxDiscount,
        total: subtotal - maxDiscount,
        quantity: qty
    };
});

// ---------------- ACTIONS ---------------- //
function toggleTicket(number) {
    const t = tickets.value.find(t => t.number === number);
    if (!t || t.status !== 'available') return;
    if (manualTickets.value.includes(number)) {
        manualTickets.value = manualTickets.value.filter(n => n !== number);
    } else {
        if(manualTickets.value.length >= 100) {
            toast.warning("Has alcanzado el límite máximo por transacción (100).");
            return;
        }
        manualTickets.value.push(number);
    }
}
function selectMachine() { selectionMode.value = 'random'; manualTickets.value = []; }
function applyCombo(qty) { selectionMode.value = 'random'; randomQuantity.value = qty; manualTickets.value = []; }

// Navigation
function nextStep() {
    if (currentStep.value === 1 && totals.value.quantity > 0) {
        currentStep.value = 2;
    } else if (currentStep.value === 2) {
        validateForm();
        if (Object.keys(errors.value).length === 0) currentStep.value = 3;
    } else if (currentStep.value === 3) {
        submitPurchase();
    }
}
function prevStep() { if (currentStep.value > 1) currentStep.value--; }

function validateForm() {
    errors.value = {};
    if (!form.value.name.trim()) errors.value.name = "El nombre es obligatorio.";
    if (!form.value.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) errors.value.email = "Correo electrónico inválido.";
    if (!form.value.phone.trim() || form.value.phone.length < 7) errors.value.phone = "Teléfono de contacto inválido.";

    if (Object.keys(errors.value).length > 0) {
        toast.error("Por favor, corrige los errores en el formulario.");
    }
}

let selectedProofFile = null;

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        if(file.size > 2 * 1024 * 1024) {
            toast.error("La imagen es demasiado grande. El límite en tu servidor actual es de 2MB.");
            return;
        }
        selectedProofFile = file; // Almacenamiento NO reactivo para prevenir corrupción
        paymentForm.value.proof = file.name; // Solo guardamos nombre para validaciones del front
        paymentForm.value.proofPreview = URL.createObjectURL(file);
    }
}

async function submitPurchase() {
    if (!selectedProofFile) {
        toast.warning("Debes subir la captura de pantalla de tu pago para continuar.");
        return;
    }
    processing.value = true;
    try {
        const payload = new FormData();
        payload.append('name', form.value.name);
        if (form.value.email) payload.append('email', form.value.email);
        payload.append('phone', form.value.phone);
        payload.append('quantity', totals.value.quantity);
        payload.append('amount', totals.value.total);
        payload.append('payment_method', paymentForm.value.method);
        payload.append('reference', paymentForm.value.reference || '-');
        
        // Adjuntamos el objeto File puro sin tocar por Vue
        payload.append('proof', selectedProofFile);

        if (selectionMode.value === 'manual') {
            payload.append('manual_tickets', JSON.stringify(manualTickets.value));
        }

        await axios.post(`/api/raffles/${raffle.value.id}/purchase`, payload);
        
        toast.success("¡Pago recibido con éxito!");
        currentStep.value = 4; // Success Anim!
    } catch (error) {
        console.error(error);
        if(error.response && error.response.status === 422) {
            toast.error("Verifica los datos ingresados e intenta de nuevo.");
        } else {
            toast.error("Ocurrió un error al procesar tu pago. Inténtalo de nuevo.");
        }
    } finally {
        processing.value = false;
    }
}

function closeModal() {
    if(processing.value) return;
    emit('close');
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

            <!-- Columna Izquierda: Wizard Flow -->
            <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col" v-if="raffle && !loading">
                
                <!-- HEADER WIZARD -->
                <div class="p-6 border-b border-surface-300/10 flex justify-between items-center sticky top-0 bg-surface-dark/95 backdrop-blur z-10">
                    <div>
                        <span class="text-xs font-bold text-brand-400 tracking-wider uppercase">Paso {{currentStep}} de 3</span>
                        <h2 class="text-xl font-bold text-white leading-tight">
                            {{ currentStep === 1 ? 'Selección de Tickets' : currentStep === 2 ? 'Tus Datos Personales' : currentStep === 3 ? 'Completar Pago' : '¡Compra Recibida!' }}
                        </h2>
                    </div>
                    <button v-if="currentStep < 4" @click="closeModal" class="p-2 text-surface-400 hover:text-white rounded-full bg-surface-light">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- CONTENIDO DEL PASO -->
                <div class="p-6 md:p-8 flex-1 relative">
                    <transition name="slide-fade" mode="out-in">
                        
                        <!-- P1: TICKETS -->
                        <div v-if="currentStep === 1" class="w-full">
                            <div v-if="combos.length > 0" class="mb-8">
                                <h3 class="text-sm font-bold text-surface-300 mb-3">Combos Flash ⚡</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="combo in combos" :key="combo.id" @click="applyCombo(combo.quantity)"
                                        class="group px-4 py-2 border border-brand-500/30 rounded-xl bg-surface-lighter hover:bg-brand-500/20 transition-all font-medium text-white shadow-lg">
                                        <span class="text-brand-400 text-xl font-bold mr-1">{{ combo.quantity }}</span>TICKETS
                                    </button>
                                </div>
                            </div>
                            <!-- Switcher -->
                            <div class="mb-6 flex p-1 bg-surface-lighter rounded-xl">
                                <button @click="selectionMode = 'manual'" :class="[selectionMode === 'manual' ? 'bg-surface-dark text-brand-400 shadow' : 'text-surface-400 hover:text-white', 'flex-1 py-3 text-sm font-bold rounded-lg transition-all']">Elegir Yo</button>
                                <button @click="selectMachine" :class="[selectionMode === 'random' ? 'bg-surface-dark text-accent-neon shadow' : 'text-surface-400 hover:text-white', 'flex-1 py-3 text-sm font-bold rounded-lg transition-all']">Búsqueda Rápida</button>
                            </div>
                            
                            <!-- Random / Manual contents... -->
                            <div v-if="selectionMode === 'random'" class="bg-surface-lighter p-8 rounded-2xl border border-surface-300/10 text-center animate-fade-in">
                                <div class="w-20 h-20 mx-auto bg-surface-light rounded-full flex items-center justify-center text-4xl mb-4">🎰</div>
                                <h3 class="text-xl font-bold text-white mb-2">Máquina Mágica</h3>
                                <p class="text-surface-400 mb-6 text-sm">Escoge cuántos números dejar a la suerte.</p>
                                <div class="relative flex items-center max-w-[200px] mx-auto mb-4">
                                    <button @click="randomQuantity > 1 ? randomQuantity-- : null" class="w-12 h-12 bg-surface text-white rounded-l-xl text-2xl font-bold hover:bg-surface-300/40">-</button>
                                    <input type="number" v-model="randomQuantity" min="1" :max="availableTickets.length" class="w-full h-12 bg-surface border-y border-surface-300/20 text-white text-center text-xl font-bold focus:outline-none focus:border-brand-500">
                                    <button @click="randomQuantity++" class="w-12 h-12 bg-surface text-white rounded-r-xl text-2xl font-bold hover:bg-surface-300/40">+</button>
                                </div>
                            </div>

                            <div v-if="selectionMode === 'manual'">
                                <div class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar bg-surface-lighter/10 p-2 rounded-xl border border-surface-300/10">
                                    <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 gap-2">
                                        <button v-for="ticket in tickets" :key="ticket.id" 
                                            @click="toggleTicket(ticket.number)"
                                            :disabled="ticket.status !== 'available'"
                                            :class="[
                                                'aspect-square rounded-xl font-bold text-sm transition-all border flex items-center justify-center',
                                                ticket.status === 'available' && !manualTickets.includes(ticket.number) ? 'bg-surface text-surface-200 border-surface-300/10 hover:border-brand-500' : '',
                                                manualTickets.includes(ticket.number) ? 'bg-brand-500 text-white border-brand-400 shadow-md ring-2 ring-brand-500/50 scale-105' : '',
                                                ticket.status !== 'available' ? 'opacity-30 cursor-not-allowed bg-surface-dark border-transparent' : ''
                                            ]">{{ String(ticket.number).padStart(3, '0') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- P2: DATOS -->
                        <div v-else-if="currentStep === 2" class="w-full max-w-sm mx-auto pt-4">
                            <div class="space-y-4">
                                <div class="group">
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Nombre Completo</label>
                                    <input v-model="form.name" type="text" placeholder="Ej. Juan Pérez" 
                                        :class="['w-full bg-surface-light border-2 rounded-xl px-4 py-3.5 text-white transition-all focus:bg-surface outline-none', errors.name ? 'border-red-500 focus:border-red-500' : 'border-surface-300/10 focus:border-brand-500']">
                                    <p class="text-red-400 text-xs mt-1.5 font-medium flex items-center gap-1" v-if="errors.name"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{errors.name}}</p>
                                </div>
                                <div class="group">
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Correo Electrónico</label>
                                    <input v-model="form.email" type="email" placeholder="tucorreo@email.com" 
                                        :class="['w-full bg-surface-light border-2 rounded-xl px-4 py-3.5 text-white transition-all focus:bg-surface outline-none', errors.email ? 'border-red-500 focus:border-red-500' : 'border-surface-300/10 focus:border-brand-500']">
                                    <p class="text-red-400 text-xs mt-1.5 font-medium flex items-center gap-1" v-if="errors.email"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{errors.email}}</p>
                                </div>
                                <div class="group">
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Teléfono (WhatsApp)</label>
                                    <input v-model="form.phone" type="tel" placeholder="+58 414 1234567" 
                                        :class="['w-full bg-surface-light border-2 rounded-xl px-4 py-3.5 text-white transition-all focus:bg-surface outline-none', errors.phone ? 'border-red-500 focus:border-red-500' : 'border-surface-300/10 focus:border-brand-500']">
                                    <p class="text-red-400 text-xs mt-1.5 font-medium flex items-center gap-1" v-if="errors.phone"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{errors.phone}}</p>
                                </div>
                            </div>
                        </div>

                        <!-- P3: PAGO -->
                        <div v-else-if="currentStep === 3" class="w-full max-w-sm mx-auto pt-2">
                            <div class="bg-blue-900/20 border border-brand-500/30 p-4 rounded-xl mb-6 text-sm text-brand-100 flex items-start gap-3">
                                <svg class="w-6 h-6 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p>Por favor transfiere la cantidad de <strong>${{totals.total.toFixed(2)}}</strong> a nuestra cuenta Zelle: <strong>pagos@rifasonline.com</strong>.</p>
                            </div>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Nro de Referencia de Pago</label>
                                    <input v-model="paymentForm.reference" type="text" placeholder="Ej: 1928374" 
                                        class="w-full bg-surface-light border-2 border-surface-300/10 rounded-xl px-4 py-3.5 text-white transition-all focus:border-brand-500 focus:bg-surface outline-none">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-surface-400 mb-1">Subir Comprobante (Requerido)</label>
                                    <label class="relative flex flex-col justify-center items-center w-full h-32 bg-surface-light border-2 border-dashed border-surface-300/30 hover:border-brand-500 hover:bg-surface rounded-xl cursor-pointer overflow-hidden transition-all group">
                                        <div v-if="!paymentForm.proofPreview" class="flex flex-col items-center justify-center pt-5 pb-6 text-surface-400 group-hover:text-white transition-colors duration-200">
                                            <svg class="w-8 h-8 mb-2 text-brand-500 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                            <p class="text-xs font-bold text-center px-4">Toca o arrastra la captura aquí</p>
                                        </div>
                                        <div v-else class="absolute inset-0 w-full h-full p-1 bg-surface">
                                            <img :src="paymentForm.proofPreview" class="w-full h-full object-contain rounded-lg animate-zoom-in" alt="Comprobante" />
                                        </div>
                                        <input type="file" accept="image/*" class="hidden" @change="handleFileUpload" />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- P4: COMPLETADO -->
                        <div v-else-if="currentStep === 4" class="w-full h-full flex flex-col items-center justify-center text-center p-8 animate-fade-in" style="min-height: 400px;">
                            <!-- Checkmark Animation -->
                            <div class="success-checkmark w-[80px] h-[115px] mx-auto mb-6">
                                <div class="check-icon">
                                    <span class="icon-line line-tip"></span>
                                    <span class="icon-line line-long"></span>
                                    <div class="icon-circle"></div>
                                    <div class="icon-fix"></div>
                                </div>
                            </div>
                            <h2 class="text-3xl font-black text-white mb-2 text-transparent bg-clip-text bg-gradient-to-r from-accent-neon to-brand-400">¡Pedido Reservado!</h2>
                            <p class="text-surface-300 font-medium mb-6 max-w-sm">Hemos recibido tus tickets. En cuanto el equipo valide el pago, recibirás un WhatsApp y correo con los detalles.</p>
                            <button @click="closeModal" class="px-8 py-3 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-500 hover:scale-105 transition-all shadow-lg shadow-brand-500/30">Finalizar y volver al inicio</button>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Columna Derecha: RESUMEN / CART (Sólo Steps 1 a 3) -->
            <div class="w-full md:w-80 bg-surface-lighter border-t md:border-t-0 md:border-l border-surface-300/20 p-6 flex flex-col justify-between" 
                 v-if="currentStep < 4" 
                 :class="{'slide-up-summary': true}">
                 
                <div>
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Resumen de Compra
                    </h3>
                    <div class="space-y-3 mb-6 bg-surface p-4 rounded-xl border border-surface-300/5">
                        <div class="flex justify-between text-sm">
                            <span class="text-surface-400">Tickets ({{ totals.quantity }})</span>
                            <span class="text-white font-medium">${{ totals.subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-accent-neon font-medium" v-if="totals.discount > 0">
                            <span>Descuento Combos</span>
                            <span>-${{ totals.discount.toFixed(2) }}</span>
                        </div>
                        <div class="pt-3 border-t border-surface-300/10 flex justify-between items-end">
                            <span class="text-surface-300">Total a Pagar</span>
                            <span class="text-2xl font-black text-white">${{ totals.total.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <button v-if="currentStep > 1" @click="prevStep" class="w-full py-3.5 bg-surface text-white font-bold rounded-xl transition-colors hover:bg-surface-light border border-surface-300/20">
                        Atrás
                    </button>
                    
                    <button 
                        @click="nextStep"
                        :disabled="totals.quantity === 0 || processing"
                        class="w-full py-4 relative bg-brand-600 overflow-hidden text-white font-bold rounded-xl active:scale-95 transition-all shadow-lg hover:bg-brand-500 hover:shadow-brand-500/30 disabled:opacity-50 disabled:cursor-not-allowed group">
                        
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-500"></div>
                        
                        <span v-if="processing" class="flex justify-center items-center gap-2">
                            <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Procesando...
                        </span>
                        
                        <span v-else>
                            {{ currentStep === 1 ? 'Siguiente Paso' : currentStep === 2 ? 'Realizar Pago' : 'Confirmar Envío del Pago' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }

/* Transiciones entre pasos */
.slide-fade-enter-active, .slide-fade-leave-active {
    transition: all 0.3s ease;
}
.slide-fade-enter-from { opacity: 0; transform: translateX(20px); }
.slide-fade-leave-to { opacity: 0; transform: translateX(-20px); }

@keyframes slideUpModal {
    from { opacity: 0; transform: translateY(40px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-slide-up-modal { animation: slideUpModal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
.animate-zoom-in { animation: zoomIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

/* Animación de Checkmark (Estilo Apple/Premium) */
.success-checkmark {
    position: relative;
}
.check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #4ade80;
}
.check-icon::before {
    top: 3px;
    left: -2px;
    width: 30px;
    transform-origin: 100% 50%;
    border-radius: 100px 0 0 100px;
}
.check-icon::after {
    top: 0;
    left: 30px;
    width: 60px;
    transform-origin: 0 50%;
    border-radius: 0 100px 100px 0;
    animation: rotate-circle 4.25s ease-in;
}
.icon-line {
    height: 5px;
    background-color: #4ade80;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}
.line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}
.line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}
.icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(74, 222, 128, .5);
}
.icon-fix {
    top: 8px;
    width: 5px;
    left: 26px;
    z-index: 1;
    height: 85px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: #0f172a; /* color de findo de la ui dark */
}
@keyframes icon-line-tip {
    0% { width: 0; left: 1px; top: 19px; }
    54% { width: 0; left: 1px; top: 19px; }
    70% { width: 50px; left: -8px; top: 37px; }
    84% { width: 17px; left: 21px; top: 48px; }
    100% { width: 25px; left: 14px; top: 46px; }
}
@keyframes icon-line-long {
    0% { width: 0; right: 46px; top: 54px; }
    65% { width: 0; right: 46px; top: 54px; }
    84% { width: 55px; right: 0px; top: 35px; }
    100% { width: 47px; right: 8px; top: 38px; }
}
</style>
