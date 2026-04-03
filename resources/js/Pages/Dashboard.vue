<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Panel de Usuario" />

    <AppLayout>
        <template #header>
            Panel de Usuario
        </template>

        <div class="space-y-6">
            <div class="glass-panel p-8 rounded-[2rem]">
                <h3 class="text-2xl font-bold mb-4">Bienvenido, {{ $page.props.auth.user.name }}!</h3>
                <p class="text-zinc-400">Aquí podrás ver tus tickets comprados, tus participaciones activas y los premios que has ganado.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                 <div class="glass-panel p-6 rounded-3xl border-l-4 border-brand-500">
                    <p class="text-xs font-black uppercase text-zinc-500 mb-1">Tickets Activos</p>
                    <p class="text-3xl font-black">0</p>
                 </div>
                 <div class="glass-panel p-6 rounded-3xl border-l-4 border-accent-neon">
                    <p class="text-xs font-black uppercase text-zinc-500 mb-1">Sorteos Participando</p>
                    <p class="text-3xl font-black">0</p>
                 </div>
                 <div class="glass-panel p-6 rounded-3xl border-l-4 border-green-500">
                    <p class="text-xs font-black uppercase text-zinc-500 mb-1">Premios Ganados</p>
                    <p class="text-3xl font-black">0</p>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>
