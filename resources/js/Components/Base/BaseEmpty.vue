<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'No hay datos disponibles',
    },
    message: {
        type: String,
        default: 'Parece que por ahora no tenemos nada que mostrarte aquí.',
    },
    icon: {
        type: String,
        default: 'empty', // empty, search, info
    },
});

const iconPaths = {
    empty: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};
</script>

<template>
    <div class="flex flex-col items-center justify-center p-12 text-center glass-panel rounded-[2.5rem] border-dashed border-2 border-white/5 bg-mesh-light">
        <div class="mb-6 p-6 rounded-full bg-surface-lighter text-zinc-600 border border-white/5 relative">
            <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" :d="iconPaths[icon] || iconPaths.empty" />
            </svg>
            <div class="absolute -top-1 -right-1 w-4 h-4 bg-brand-500 rounded-full animate-pulse opacity-50"></div>
        </div>
        
        <h3 class="text-xl font-black text-white leading-tight uppercase tracking-widest mb-2">{{ title }}</h3>
        <p class="text-zinc-500 font-medium max-w-sm mx-auto leading-relaxed">{{ message }}</p>
        
        <div v-if="$slots.actions" class="mt-8">
            <slot name="actions" />
        </div>
    </div>
</template>

<style scoped>
.bg-mesh-light {
    background-image: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.02) 0%, transparent 100%);
}
</style>
