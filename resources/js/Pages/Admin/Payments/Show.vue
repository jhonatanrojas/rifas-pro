<script setup>
import { ref } from 'vue';
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
    <Head title="Revisión de Pago" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.payments.index')" class="p-2 bg-white/5 rounded-full hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </Link>
                Revisión de Pago #{{ payment.id }}
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Receipt Image -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-panel p-4 rounded-[2.5rem] overflow-hidden border-2 border-white/5">
                    <div class="text-xs font-black uppercase text-zinc-500 mb-4 px-4 pt-4 tracking-widest">Comprobante del Usuario</div>
                    <div class="aspect-[3/4] bg-zinc-900 rounded-[1.5rem] overflow-hidden group relative">
                        <img v-if="payment.receipt_image_path" :src="`/storage/${payment.receipt_image_path}`" class="w-full h-full object-contain" alt="Comprobante" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-zinc-500 italic">
                             <svg class="w-16 h-16 opacity-10 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="1"/></svg>
                             No se cargó una imagen.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Payment Info & Actions -->
            <div class="space-y-6">
                <div class="glass-panel p-8 rounded-[2.5rem]">
                    <h3 class="text-sm font-black uppercase tracking-widest mb-6 border-b border-white/5 pb-4">Detalle de la Operación</h3>
                    
                    <div class="space-y-6">
                        <div>
                             <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Monto Declado</p>
                             <p class="text-2xl font-black">{{ payment.amount }} {{ payment.currency }}</p>
                        </div>

                        <div>
                             <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Referencia / Banco</p>
                             <p class="text-lg font-bold text-white">{{ payment.reference_number || 'N/A' }}</p>
                             <p class="text-xs text-zinc-400 capitalize">{{ payment.method }}</p>
                        </div>

                        <div>
                             <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Usuario</p>
                             <p class="text-white font-bold leading-tight">{{ payment.user.name }}</p>
                             <p class="text-xs text-zinc-400">{{ payment.user.email }}</p>
                        </div>

                        <div>
                             <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Estado Actual</p>
                             <BaseBadge :variant="payment.status === 'approved' ? 'success' : (payment.status === 'pending' ? 'warning' : 'error')" class="mt-1">
                                {{ payment.status }}
                             </BaseBadge>
                        </div>
                    </div>

                    <div v-if="payment.status === 'pending'" class="mt-12 space-y-3">
                        <BaseButton @click="openReviewModal('approved')" variant="neon" class="w-full">Aprobar Pago</BaseButton>
                        <BaseButton @click="openReviewModal('rejected')" variant="outline" class="w-full border-red-500/50 text-red-400 hover:bg-red-500/10">Rechazar Pago</BaseButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <BaseModal :show="isModalOpen" @close="isModalOpen = false">
            <template #title>{{ reviewStatus === 'approved' ? 'Confirmar Aprobación' : 'Confirmar Rechazo' }}</template>
            
            <div class="space-y-6">
                <p class="text-zinc-400 text-sm">
                    {{ reviewStatus === 'approved' 
                        ? 'Al aprobar este pago, se confirmarán los números del usuario y se le notificará vía email/whatsapp.' 
                        : 'Al rechazar este pago, el usuario podrá volver a subir un comprobante o corregir sus datos.' }}
                </p>

                <BaseInput 
                    v-model="form.notes"
                    label="Notas / Motivo (opcional)" 
                    placeholder="Ej: Comprobante ilegible, Referencia no coincide..."
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
                         Confirmar Pago
                    </BaseButton>
                </div>
            </div>
        </BaseModal>
    </AdminLayout>
</template>
