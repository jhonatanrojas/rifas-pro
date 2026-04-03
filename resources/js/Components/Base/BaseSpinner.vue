<script setup>
defineProps({
    size: { type: String, default: 'md' },   // sm | md | lg
    color: { type: String, default: 'brand' }, // brand | white | neon
})

const sizeMap = { sm: 'w-4 h-4', md: 'w-6 h-6', lg: 'w-10 h-10' }
const strokeMap = { sm: '3', md: '2.5', lg: '2' }
const colorMap = {
    brand: 'text-brand-400',
    white: 'text-white',
    neon:  'text-accent-neon',
}
</script>

<template>
    <svg
        :class="['animate-spin', sizeMap[size], colorMap[color]]"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        aria-label="Cargando"
    >
        <circle
            class="opacity-20"
            cx="12" cy="12" r="10"
            stroke="currentColor"
            :stroke-width="strokeMap[size]"
        />
        <path
            class="opacity-80"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
    </svg>
</template>
