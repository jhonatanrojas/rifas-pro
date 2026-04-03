<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info', // info, success, warning, error
    },
    title: String,
    message: String,
    dismissible: {
        type: Boolean,
        default: true,
    },
});

const isDismissed = ref(false);

const typeClasses = {
    info: 'bg-blue-500/10 border-blue-500/30 text-blue-400',
    success: 'bg-green-500/10 border-green-500/30 text-green-400',
    warning: 'bg-amber-500/10 border-amber-500/30 text-amber-400',
    error: 'bg-red-500/10 border-red-500/30 text-red-400',
};

const iconPath = computed(() => {
    return {
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        error: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    }[props.type];
});

const dismiss = () => {
    isDismissed.value = true;
};
</script>

<template>
    <transition
        leave-active-class="transition ease-in duration-300"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-4"
    >
        <div v-if="!isDismissed" class="flex p-5 rounded-[1.5rem] border backdrop-blur-md relative overflow-hidden" :class="typeClasses[type]">
            <!-- Subtle glow base -->
            <div class="absolute inset-0 bg-white/3"></div>
            
            <div class="relative z-10 flex shrink-0 mr-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath" />
                </svg>
            </div>
            
            <div class="relative z-10 flex-1">
                <h3 v-if="title" class="text-sm font-black uppercase tracking-widest mb-1">{{ title }}</h3>
                <div class="text-sm font-medium opacity-90"><slot>{{ message }}</slot></div>
            </div>
            
            <button v-if="dismissible" @click="dismiss" class="relative z-10 p-2 -mr-1 -mt-1 ml-4 rounded-full hover:bg-white/5 transition-colors h-fit">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </transition>
</template>
