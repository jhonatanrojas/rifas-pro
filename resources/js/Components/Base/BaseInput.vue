<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    label: String,
    error: String,
    type: {
        type: String,
        default: 'text',
    },
    placeholder: String,
    required: Boolean,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="block text-sm font-bold text-zinc-400 ml-1">
            {{ label }} <span v-if="required" class="text-brand-500">*</span>
        </label>
        
        <div class="relative">
            <input
                ref="input"
                :type="type"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-all outline-none text-white placeholder-zinc-600"
                :placeholder="placeholder"
                :class="{ 'border-red-500/50 ring-red-500/10 focus:ring-red-500/20 focus:border-red-500': error }"
            />
        </div>
        
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
        >
            <p v-if="error" class="text-sm text-red-500 font-medium ml-1">
                {{ error }}
            </p>
        </transition>
    </div>
</template>
