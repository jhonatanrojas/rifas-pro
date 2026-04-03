<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    refreshing: Boolean,
});

const emit = defineEmits(['refresh']);

const container = ref(null);
const pullDownDistance = ref(0);
const MAX_PULL_UP = 80;
const REFRESH_THRESHOLD = 60;
const startY = ref(0);
const isPulling = ref(false);

const handleTouchStart = (e) => {
    // Only pull down if at the top
    if (window.scrollY > 0) return;
    startY.value = e.touches[0].clientY;
};

const handleTouchMove = (e) => {
    if (window.scrollY > 0 || props.refreshing) return;
    
    const deltaY = e.touches[0].clientY - startY.value;
    if (deltaY > 0) {
        isPulling.value = true;
        pullDownDistance.value = Math.min(deltaY * 0.4, MAX_PULL_UP);
    }
};

const handleTouchEnd = () => {
    if (pullDownDistance.value > REFRESH_THRESHOLD) {
        emit('refresh');
    }
    
    isPulling.value = false;
    pullDownDistance.value = 0;
};

onMounted(() => {
    window.addEventListener('touchstart', handleTouchStart, { passive: true });
    window.addEventListener('touchmove', handleTouchMove, { passive: true });
    window.addEventListener('touchend', handleTouchEnd);
});

onUnmounted(() => {
    window.removeEventListener('touchstart', handleTouchStart);
    window.removeEventListener('touchmove', handleTouchMove);
    window.removeEventListener('touchend', handleTouchEnd);
});
</script>

<template>
    <div class="relative overflow-hidden">
        <!-- Pull Down Indicator -->
        <div 
            class="absolute top-[-50px] left-0 right-0 h-[50px] flex items-center justify-center transition-transform duration-200"
            :style="`transform: translateY(${Math.max(pullDownDistance, refreshing ? 50 : 0)}px)`"
        >
            <div class="p-2 rounded-full glass-panel border border-white/10 shadow-lg shadow-brand-500/10">
                <svg 
                    v-if="!refreshing"
                    class="w-6 h-6 text-brand-400 transition-transform" 
                    :style="`transform: rotate(${pullDownDistance * 2}deg)`"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                <div v-else class="w-6 h-6 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>

        <div class="transition-transform duration-200" :style="`transform: translateY(${Math.max(pullDownDistance, refreshing ? 50 : 0)}px)`">
            <slot />
        </div>
    </div>
</template>
