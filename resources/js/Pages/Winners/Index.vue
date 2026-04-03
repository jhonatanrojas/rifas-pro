<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import WinnerWall from '@/Components/WinnerWall.vue'

defineProps({
    winners: { type: Array, default: () => [] },
    raffles: { type: Array, default: () => [] },
})
</script>

<template>
    <AppLayout>
        <Head title="Ganadores" />

        <template #header>Ganadores</template>

        <WinnerWall :winners="winners" :raffles="raffles" />
    </AppLayout>
</template>
