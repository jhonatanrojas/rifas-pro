<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Escribe aqui...',
    },
    error: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);
const editorRef = ref(null);

const toolbar = [
    { label: 'B', action: 'bold', title: 'Negrita' },
    { label: 'I', action: 'italic', title: 'Cursiva' },
    { label: 'U', action: 'underline', title: 'Subrayado' },
];

function syncValue() {
    emit('update:modelValue', editorRef.value?.innerHTML ?? '');
}

function format(action) {
    document.execCommand(action, false);
    syncValue();
    editorRef.value?.focus();
}

function clearEditor() {
    if (!editorRef.value) {
        return;
    }

    editorRef.value.innerHTML = '';
    syncValue();
}

watch(() => props.modelValue, (value) => {
    if (!editorRef.value) {
        return;
    }

    if (editorRef.value.innerHTML !== value) {
        editorRef.value.innerHTML = value || '';
    }
}, { immediate: true });

onMounted(() => {
    if (editorRef.value) {
        editorRef.value.innerHTML = props.modelValue || '';
    }
});
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="text-[10px] uppercase font-black tracking-widest text-zinc-500">{{ label }}</label>

        <div class="rounded-[1.5rem] border border-white/10 bg-white/5 overflow-hidden">
            <div class="flex items-center gap-2 border-b border-white/10 p-2 bg-white/5">
                <button
                    v-for="item in toolbar"
                    :key="item.action"
                    type="button"
                    class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest text-white bg-white/5 hover:bg-white/10 transition-colors"
                    :title="item.title"
                    @click="format(item.action)"
                >
                    {{ item.label }}
                </button>
                <button
                    type="button"
                    class="ml-auto px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest text-zinc-400 hover:text-white hover:bg-white/10 transition-colors"
                    @click="clearEditor"
                >
                    Limpiar
                </button>
            </div>

            <div
                ref="editorRef"
                class="min-h-[180px] p-4 text-white text-sm leading-6 outline-none"
                :data-placeholder="placeholder"
                contenteditable="true"
                @input="syncValue"
                @blur="syncValue"
            ></div>
        </div>

        <p v-if="error" class="text-red-400 text-[10px] font-bold mt-1">{{ error }}</p>
    </div>
</template>

<style scoped>
[contenteditable="true"]:empty:before {
    content: attr(data-placeholder);
    color: rgba(161, 161, 170, 0.7);
}
</style>
