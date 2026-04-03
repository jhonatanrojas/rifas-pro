<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary', // primary, secondary, neon, outline, brand
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
    href: {
        type: String,
        default: null,
    },
    method: {
        type: String,
        default: 'get',
    },
    disabled: Boolean,
    loading: Boolean,
});

const sizeClasses = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-6 py-3',
    lg: 'px-8 py-4 text-lg',
};

const variantClasses = {
    primary: 'btn-primary',
    secondary: 'btn-secondary',
    neon: 'btn-neon',
    brand: 'bg-brand-500 hover:bg-brand-400 text-white shadow-lg shadow-brand-500/20',
    outline: 'bg-transparent border border-white/20 hover:bg-white/5 text-white',
};

const classes = computed(() => {
    return [
        'inline-flex items-center justify-center gap-2 font-bold transition-all duration-300 rounded-2xl active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed',
        sizeClasses[props.size],
        variantClasses[props.variant],
    ];
});
</script>

<template>
    <a
        v-if="href"
        :href="href"
        :class="classes"
        :aria-disabled="disabled || loading ? 'true' : 'false'"
    >
        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <slot />
    </a>

    <button
        v-else
        :class="classes"
        :disabled="disabled || loading"
        :type="method === 'submit' ? 'submit' : 'button'"
    >
        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <slot />
    </button>
</template>
