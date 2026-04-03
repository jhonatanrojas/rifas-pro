<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseBadge from '@/Components/Base/BaseBadge.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseModal from '@/Components/Base/BaseModal.vue';

const props = defineProps({
    payment: Object,
});

const form = useForm({
    status: '',
    notes: '',
});

const isModalOpen = ref(false);
const reviewStatus = ref('');
const activeTab = ref('receipt');

const paymentMethodLabels = {
    zelle: 'Zelle',
    pago_movil: 'Pago móvil',
    binance: 'Binance',
    paypal: 'PayPal',
    stripe: 'Stripe',
};

const receiptUrl = computed(() => {
    return props.payment.receipt_image_path ? `/storage/${props.payment.receipt_image_path}` : null;
});

const formatPaymentMethod = (method) => paymentMethodLabels[method] ?? (method ? method.replaceAll('_', ' ') : 'N/A');

const openReviewModal = (status) => {
    reviewStatus.value = status;
    form.status = status;
    isModalOpen.value = true;
};

const submitReview = () => {
    form.post(route('admin.payments.review', props.payment.id), {
        onSuccess: () => isModalOpen.value = false,
    });
};
</script>

<template>
    <Head title="Revisión de pago" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.payments.index')" class="p-2 bg-white/5 rounded-full hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </Link>
                Pago #{{ payment.id }}
            </div>
        </template>

        <div class="mb-6 flex gap-3 lg:hidden">
            <BaseButton :variant="activeTab === 'receipt' ? 'neon' : 'outline'" size="sm" @click="activeTab = 'receipt'">Comprobante</BaseButton>
            <BaseButton :variant="activeTab === 'review' ? 'neon' : 'outline'" size="sm" @click="activeTab = 'review'">Revisión</BaseButton>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6" :class="activeTab === 'review' ? 'hidden lg:block' : ''">
                <div class="glass-panel p-4 rounded-[2.5rem] overflow-hidden border-2 border-white/5">
                    <div class="text-xs font-black uppercase text-zinc-500 mb-4 px-4 pt-4 tracking-widest">Comprobante del usuario</div>
                    <div class="aspect-[3/4] bg-zinc-900 rounded-[1.5rem] overflow-hidden group relative">
                        <img v-if="payment.receipt_image_path" :src="receiptUrl" class="w-full h-full object-contain" alt="Comprobante" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-zinc-500 italic">
                            No se cargó imagen del comprobante.
                        </div>
                    </div>
                </div>

                <div class="glass-panel p-6 rounded-[2rem]">
                    <h3 class="text-sm font-black uppercase tracking-widest mb-4">Datos OCR / Gateway</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="rounded-2xl bg-white/5 p-4 border border-white/5">
                            <div class="text-[10px] uppercase tracking-widest text-zinc-500 mb-2">Datos OCR crudos</div>
                            <pre class="text-xs text-zinc-300 whitespace-pre-wrap break-words">{{ JSON.stringify(payment.ocr_raw_data || {}, null, 2) }}</pre>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4 border border-white/5">
                            <div class="text-[10px] uppercase tracking-widest text-zinc-500 mb-2">Respuesta del gateway</div>
                            <pre class="text-xs text-zinc-300 whitespace-pre-wrap break-words">{{ JSON.stringify(payment.gateway_response || {}, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6" :class="activeTab === 'receipt' ? 'hidden lg:block' : ''">
                <div class="glass-panel p-8 rounded-[2.5rem]">
                    <h3 class="text-sm font-black uppercase tracking-widest mb-6 border-b border-white/5 pb-4">Detalles del pago</h3>

                    <div class="space-y-5">
                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Monto</p>
                            <p class="text-2xl font-black">{{ payment.amount }} {{ payment.currency }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Referencia / Método</p>
                            <p class="text-lg font-bold text-white">{{ payment.reference_number || 'N/A' }}</p>
                            <p class="text-xs text-zinc-400">{{ formatPaymentMethod(payment.method) }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Usuario</p>
                            <p class="text-white font-bold leading-tight">{{ payment.user.name }}</p>
                            <p class="text-xs text-zinc-400">{{ payment.user.email }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Rifa</p>
                            <p class="text-white font-bold leading-tight">{{ payment.order.raffle.title }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Estado</p>
                            <BaseBadge :variant="payment.status === 'approved' ? 'success' : (payment.status === 'pending' ? 'warning' : 'error')" class="mt-1">
                                {{ payment.status === 'approved' ? 'Aprobado' : payment.status === 'pending' ? 'Pendiente' : 'Rechazado' }}
                            </BaseBadge>
                        </div>

                        <div v-if="payment.notes">
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Notas</p>
                            <p class="text-sm text-zinc-300">{{ payment.notes }}</p>
                        </div>

                        <div v-if="payment.reviewer">
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Revisado por</p>
                            <p class="text-sm text-zinc-300">{{ payment.reviewer.name }}</p>
                            <p class="text-xs text-zinc-500">{{ payment.reviewed_at }}</p>
                        </div>
                    </div>

                    <div v-if="payment.status === 'pending'" class="mt-10 space-y-3">
                        <BaseButton @click="openReviewModal('approved')" variant="neon" class="w-full">Aprobar pago</BaseButton>
                        <BaseButton @click="openReviewModal('rejected')" variant="outline" class="w-full border-red-500/50 text-red-400 hover:bg-red-500/10">Rechazar pago</BaseButton>
                    </div>

                    <div v-if="receiptUrl" class="mt-8">
                        <a :href="receiptUrl" target="_blank" class="text-brand-400 text-sm font-bold hover:underline">Abrir comprobante</a>
                    </div>
                </div>
            </div>
        </div>

        <BaseModal :show="isModalOpen" @close="isModalOpen = false">
            <template #title>{{ reviewStatus === 'approved' ? 'Confirmar aprobación' : 'Confirmar rechazo' }}</template>

            <div class="space-y-6">
                <p class="text-zinc-400 text-sm">
                    {{ reviewStatus === 'approved'
                        ? 'Aprobar este pago confirmará la venta del ticket y notificará al usuario.'
                        : 'Rechazar este pago marcará la orden como rechazada y dejará registro de la revisión.' }}
                </p>

                <BaseInput
                    v-model="form.notes"
                    label="Notas / motivo (opcional)"
                    placeholder="Ejemplo: comprobante ilegible, la referencia no coincide..."
                />

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <BaseButton variant="outline" @click="isModalOpen = false">Cancelar</BaseButton>
                    <BaseButton
                        :variant="reviewStatus === 'approved' ? 'neon' : 'brand'"
                        class="bg-red-500 hover:bg-red-600 border-none"
                        v-if="reviewStatus === 'rejected'"
                        @click="submitReview"
                        :loading="form.processing"
                    >
                        Rechazar
                    </BaseButton>
                    <BaseButton
                        variant="neon"
                        v-else
                        @click="submitReview"
                        :loading="form.processing"
                    >
                        Confirmar
                    </BaseButton>
                </div>
            </div>
        </BaseModal>
    </AdminLayout>
</template>
