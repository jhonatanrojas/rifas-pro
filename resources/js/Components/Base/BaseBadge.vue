<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'brand', // brand, accent, success, warning, error, info, surface
    },
    size: {
        type: String,
        default: 'md', // sm, md
    },
    rounded: {
        type: Boolean,
        default: false,
    },
});

const variantClasses = {
    brand: 'bg-brand-500/10 border-brand-500/30 text-brand-400',
    accent: 'bg-accent-neon/10 border-accent-neon/30 text-accent-neon',
    success: 'bg-green-500/10 border-green-500/30 text-green-500',
    warning: 'bg-amber-500/10 border-amber-500/30 text-amber-500',
    error: 'bg-red-500/10 border-red-500/30 text-red-500',
    info: 'bg-blue-500/10 border-blue-500/30 text-blue-500',
    surface: 'bg-white/5 border-white/10 text-zinc-400',
};

const sizeClasses = {
    sm: 'px-1.5 py-0.5 text-[10px]',
    md: 'px-2.5 py-1 text-xs',
};

const classes = computed(() => {
    return [
        'inline-flex items-center font-black uppercase tracking-widest border transition-all duration-300',
        variantClasses[props.variant],
        sizeClasses[props.size],
        props.rounded ? 'rounded-full' : 'rounded-lg',
    ];
});
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
