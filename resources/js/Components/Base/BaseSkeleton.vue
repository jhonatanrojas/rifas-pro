<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'text', // text, circle, rect
    },
    width: {
        type: String,
        default: 'w-full',
    },
    height: {
        type: String,
        default: 'h-4',
    },
    rounded: {
        type: String,
        default: 'rounded-lg',
    },
});

const classes = computed(() => {
    return [
        'bg-surface-lighter flex-shrink-0 animate-pulse relative overflow-hidden',
        props.width,
        props.height,
        props.type === 'circle' ? 'rounded-full' : props.rounded,
    ];
});
</script>

<template>
    <div :class="classes">
        <!-- Shimmer Shimmer effect -->
        <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/5 to-transparent animate-shimmer"></div>
    </div>
</template>

<style>
@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}
.animate-shimmer {
    animation: shimmer 2s infinite ease-in-out;
}
</style>
