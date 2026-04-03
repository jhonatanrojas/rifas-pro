<script setup>
defineProps({
    padding: { type: String, default: 'p-6' },
    hover: { type: Boolean, default: false },
    noBorder: { type: Boolean, default: false },
})
</script>

<template>
    <div
        :class="[
            'rounded-2xl bg-surface-light',
            padding,
            !noBorder && 'border border-white/5',
            hover && 'transition-all duration-300 hover:border-white/10 hover:bg-surface-lighter cursor-pointer hover:-translate-y-0.5 hover:shadow-xl hover:shadow-black/30',
        ]"
    >
        <div v-if="$slots.header" class="mb-4 pb-4 border-b border-white/5 flex items-center justify-between">
            <slot name="header" />
        </div>

        <slot />

        <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-white/5">
            <slot name="footer" />
        </div>
    </div>
</template>
