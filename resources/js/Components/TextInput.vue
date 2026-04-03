<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-white shadow-sm outline-none transition-all placeholder-zinc-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/40"
        v-model="model"
        ref="input"
    />
</template>
