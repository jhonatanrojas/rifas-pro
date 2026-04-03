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

const receiptUrl = computed(() => {
    return props.payment.receipt_image_path ? `/storage/${props.payment.receipt_image_path}` : null;
});

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
    <Head title="Payment Review" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.payments.index')" class="p-2 bg-white/5 rounded-full hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </Link>
                Payment #{{ payment.id }}
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-panel p-4 rounded-[2.5rem] overflow-hidden border-2 border-white/5">
                    <div class="text-xs font-black uppercase text-zinc-500 mb-4 px-4 pt-4 tracking-widest">User Receipt</div>
                    <div class="aspect-[3/4] bg-zinc-900 rounded-[1.5rem] overflow-hidden group relative">
                        <img v-if="payment.receipt_image_path" :src="receiptUrl" class="w-full h-full object-contain" alt="Receipt" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-zinc-500 italic">
                            No receipt image uploaded.
                        </div>
                    </div>
                </div>

                <div class="glass-panel p-6 rounded-[2rem]">
                    <h3 class="text-sm font-black uppercase tracking-widest mb-4">OCR / Gateway Data</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="rounded-2xl bg-white/5 p-4 border border-white/5">
                            <div class="text-[10px] uppercase tracking-widest text-zinc-500 mb-2">OCR Raw Data</div>
                            <pre class="text-xs text-zinc-300 whitespace-pre-wrap break-words">{{ JSON.stringify(payment.ocr_raw_data || {}, null, 2) }}</pre>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4 border border-white/5">
                            <div class="text-[10px] uppercase tracking-widest text-zinc-500 mb-2">Gateway Response</div>
                            <pre class="text-xs text-zinc-300 whitespace-pre-wrap break-words">{{ JSON.stringify(payment.gateway_response || {}, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass-panel p-8 rounded-[2.5rem]">
                    <h3 class="text-sm font-black uppercase tracking-widest mb-6 border-b border-white/5 pb-4">Payment Details</h3>

                    <div class="space-y-5">
                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Amount</p>
                            <p class="text-2xl font-black">{{ payment.amount }} {{ payment.currency }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Reference / Method</p>
                            <p class="text-lg font-bold text-white">{{ payment.reference_number || 'N/A' }}</p>
                            <p class="text-xs text-zinc-400 capitalize">{{ payment.method }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">User</p>
                            <p class="text-white font-bold leading-tight">{{ payment.user.name }}</p>
                            <p class="text-xs text-zinc-400">{{ payment.user.email }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Raffle</p>
                            <p class="text-white font-bold leading-tight">{{ payment.order.raffle.title }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">State</p>
                            <BaseBadge :variant="payment.status === 'approved' ? 'success' : (payment.status === 'pending' ? 'warning' : 'error')" class="mt-1">
                                {{ payment.status }}
                            </BaseBadge>
                        </div>

                        <div v-if="payment.notes">
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Notes</p>
                            <p class="text-sm text-zinc-300">{{ payment.notes }}</p>
                        </div>

                        <div v-if="payment.reviewer">
                            <p class="text-[10px] font-black uppercase text-zinc-500 mb-1">Reviewed By</p>
                            <p class="text-sm text-zinc-300">{{ payment.reviewer.name }}</p>
                            <p class="text-xs text-zinc-500">{{ payment.reviewed_at }}</p>
                        </div>
                    </div>

                    <div v-if="payment.status === 'pending'" class="mt-10 space-y-3">
                        <BaseButton @click="openReviewModal('approved')" variant="neon" class="w-full">Approve Payment</BaseButton>
                        <BaseButton @click="openReviewModal('rejected')" variant="outline" class="w-full border-red-500/50 text-red-400 hover:bg-red-500/10">Reject Payment</BaseButton>
                    </div>

                    <div v-if="receiptUrl" class="mt-8">
                        <a :href="receiptUrl" target="_blank" class="text-brand-400 text-sm font-bold hover:underline">Open receipt file</a>
                    </div>
                </div>
            </div>
        </div>

        <BaseModal :show="isModalOpen" @close="isModalOpen = false">
            <template #title>{{ reviewStatus === 'approved' ? 'Confirm Approval' : 'Confirm Rejection' }}</template>

            <div class="space-y-6">
                <p class="text-zinc-400 text-sm">
                    {{ reviewStatus === 'approved'
                        ? 'Approving this payment will confirm the ticket sale and notify the user.'
                        : 'Rejecting this payment will mark the order as rejected and keep a review trail.' }}
                </p>

                <BaseInput
                    v-model="form.notes"
                    label="Notes / Reason (optional)"
                    placeholder="Example: receipt unreadable, reference does not match..."
                />

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <BaseButton variant="outline" @click="isModalOpen = false">Cancel</BaseButton>
                    <BaseButton
                        :variant="reviewStatus === 'approved' ? 'neon' : 'brand'"
                        class="bg-red-500 hover:bg-red-600 border-none"
                        v-if="reviewStatus === 'rejected'"
                        @click="submitReview"
                        :loading="form.processing"
                    >
                        Reject
                    </BaseButton>
                    <BaseButton
                        variant="neon"
                        v-else
                        @click="submitReview"
                        :loading="form.processing"
                    >
                        Confirm
                    </BaseButton>
                </div>
            </div>
        </BaseModal>
    </AdminLayout>
</template>
