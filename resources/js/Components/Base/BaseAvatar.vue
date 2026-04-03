<script setup>
import { computed } from 'vue'

const props = defineProps({
    src: { type: String, default: null },
    name: { type: String, default: '' },
    size: { type: String, default: 'md' }, // xs | sm | md | lg | xl
    shape: { type: String, default: 'circle' }, // circle | rounded
})

const sizeClasses = {
    xs:  'w-6 h-6 text-[10px]',
    sm:  'w-8 h-8 text-xs',
    md:  'w-10 h-10 text-sm',
    lg:  'w-14 h-14 text-base',
    xl:  'w-20 h-20 text-xl',
}

const shapeClasses = {
    circle:  'rounded-full',
    rounded: 'rounded-xl',
}

const initials = computed(() => {
    if (!props.name) return '?'
    return props.name
        .split(' ')
        .slice(0, 2)
        .map(w => w[0])
        .join('')
        .toUpperCase()
})

const colorIndex = computed(() => {
    if (!props.name) return 0
    return props.name.charCodeAt(0) % 6
})

const bgColors = [
    'bg-brand-500/20 text-brand-300',
    'bg-purple-500/20 text-purple-300',
    'bg-accent-neon/20 text-pink-300',
    'bg-emerald-500/20 text-emerald-300',
    'bg-amber-500/20 text-amber-300',
    'bg-cyan-500/20 text-cyan-300',
]
</script>

<template>
    <div
        :class="[
            'flex items-center justify-center flex-shrink-0 overflow-hidden font-bold select-none',
            sizeClasses[size],
            shapeClasses[shape],
            !src && bgColors[colorIndex],
            src && 'bg-surface-lighter',
        ]"
    >
        <img
            v-if="src"
            :src="src"
            :alt="name"
            class="w-full h-full object-cover"
            @error="$event.target.style.display = 'none'"
        />
        <span v-else>{{ initials }}</span>
    </div>
</template>
