<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import BaseButton from '@/Components/Base/BaseButton.vue';

const page = usePage();
const showConsent = ref(false);
const isSubscribing = ref(false);
const DEDUP_KEY = 'push_consent_dismissed_v1';

const checkSubscription = async () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
    if (localStorage.getItem(DEDUP_KEY) === '1') return;
    
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();
    
    if (!subscription && Notification.permission !== 'denied') {
        showConsent.value = true;
    }
};

const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
};

const subscribe = async () => {
    isSubscribing.value = true;
    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            showConsent.value = false;
            return;
        }

        const registration = await navigator.serviceWorker.ready;
        const vapidPublicKey = page.props.webpush.vapidPublicKey;
        
        if (!vapidPublicKey) {
             console.error('VAPID Public Key not found.');
             return;
        }

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
        });

        await axios.post(route('push.update'), subscription);
        localStorage.setItem(DEDUP_KEY, '1');
        showConsent.value = false;
    } catch (error) {
        console.error('Error subscribing to push notifications:', error);
    } finally {
        isSubscribing.value = false;
    }
};

onMounted(() => {
    // Only show for authenticated users and after a small delay
    if (page.props.auth.user) {
        setTimeout(checkSubscription, 3000);
    }
});

function dismiss() {
    localStorage.setItem(DEDUP_KEY, '1');
    showConsent.value = false;
}
</script>

<template>
    <Transition
        enter-active-class="transform transition ease-out duration-500"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transform transition ease-in duration-300"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div v-if="showConsent" class="fixed bottom-24 left-6 right-6 lg:left-auto lg:right-10 lg:w-96 z-[100]">
            <div class="glass-panel p-8 rounded-[2.5rem] shadow-2xl border-2 border-brand-500/20">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-400 flex-shrink-0 animate-bounce">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-black text-white uppercase tracking-tight mb-1">¡No te pierdas nada!</h4>
                        <p class="text-[11px] text-zinc-400 font-medium leading-relaxed mb-6">Activa las notificaciones para avisarte al instante si tus números resultan ganadores.</p>
                        
                        <div class="flex gap-3">
                             <BaseButton @click="subscribe" :loading="isSubscribing" variant="neon" size="sm" class="flex-1">Activar</BaseButton>
                             <BaseButton @click="dismiss" variant="outline" size="sm">Después</BaseButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
