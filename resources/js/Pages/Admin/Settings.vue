<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseAlert from '@/Components/Base/BaseAlert.vue';

const props = defineProps({
    settings: Object,
    templates: Array,
});

const activeTab = ref('general');

const form = useForm({
    settings: {
        exchange_rate: props.settings?.exchange_rate ?? 0,
        reservation_minutes: props.settings?.reservation_minutes ?? 15,
        whatsapp_provider: props.settings?.whatsapp_provider ?? 'callmebot',
        push_enabled: props.settings?.push_enabled ?? true,
    },
    templates: (props.templates ?? []).map((template) => ({
        channel: template.channel,
        key: template.key,
        title: template.title ?? '',
        body: template.body ?? '',
        is_active: template.is_active ?? true,
    })),
});

const tabs = [
    { id: 'general', label: 'Configuración general' },
    { id: 'templates', label: 'Plantillas' },
];

const groupedTemplates = computed(() => {
    const groups = { whatsapp: [], email: [], push: [] };
    form.templates.forEach((template, index) => {
        groups[template.channel]?.push({ ...template, index });
    });
    return groups;
});

function submit() {
    form.put(route('admin.settings.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Configuración" />

    <AdminLayout>
        <template #header>Configuración</template>
        <template #description>Gestiona parámetros del sistema, proveedores y plantillas de notificación.</template>

        <form class="grid grid-cols-1 xl:grid-cols-3 gap-8 mt-8" @submit.prevent="submit">
            <div class="xl:col-span-2 space-y-6">
                <div class="glass-panel rounded-[2.5rem] p-3 sm:p-4 border border-white/5 flex flex-wrap gap-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        @click="activeTab = tab.id"
                        class="px-4 py-2 rounded-2xl text-sm font-bold transition-colors"
                        :class="activeTab === tab.id ? 'bg-brand-500 text-white' : 'bg-white/5 text-zinc-400 hover:text-white hover:bg-white/10'"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div v-if="activeTab === 'general'" class="glass-panel p-8 rounded-[3rem] space-y-6">
                    <div class="border-b border-white/5 pb-4">
                        <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest">Parámetros del sistema</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="form.settings.exchange_rate" type="number" label="Tasa de cambio" />
                        <BaseInput v-model="form.settings.reservation_minutes" type="number" label="Expiración de reservas (minutos)" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Proveedor WhatsApp</label>
                            <select v-model="form.settings.whatsapp_provider" class="w-full rounded-[1.25rem] bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                                <option value="callmebot">CallMeBot</option>
                                <option value="twilio">Twilio</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-3 pt-7 text-sm text-zinc-300">
                            <input v-model="form.settings.push_enabled" type="checkbox" class="rounded border-white/20 bg-white/5" />
                            Habilitar notificaciones Push
                        </label>
                    </div>

                    <BaseAlert type="info" title="Fuente de verdad">
                        Estos valores se usan en tiempo de ejecución por los servicios del sistema. No dependes de editar archivos de configuración.
                    </BaseAlert>
                </div>

                <div v-else class="glass-panel p-8 rounded-[3rem] space-y-8">
                    <div class="border-b border-white/5 pb-4">
                        <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest">Plantillas de notificación</h3>
                    </div>

                    <div v-for="channel in ['whatsapp', 'push', 'email']" :key="channel" class="space-y-4">
                        <h4 class="text-sm font-black uppercase tracking-widest text-white">{{ channel }}</h4>

                        <div v-for="item in groupedTemplates[channel]" :key="`${item.channel}-${item.key}`" class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4 space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-zinc-500 font-black">{{ item.key }}</p>
                                    <p class="text-xs text-zinc-400">Variables: {nombre}, {rifa}, {numeros}, {total}, {premio}, {motivo}</p>
                                </div>
                                <label class="flex items-center gap-2 text-xs text-zinc-300">
                                    <input v-model="form.templates[item.index].is_active" type="checkbox" class="rounded border-white/20 bg-white/5" />
                                    Activa
                                </label>
                            </div>

                            <BaseInput v-model="form.templates[item.index].title" label="Título" />
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Cuerpo</label>
                                <textarea
                                    v-model="form.templates[item.index].body"
                                    class="w-full min-h-[120px] rounded-[1.25rem] bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass-panel p-8 rounded-[3rem] space-y-4">
                    <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest border-b border-white/5 pb-4">Acciones</h3>
                    <BaseButton :loading="form.processing" variant="neon" class="w-full">Guardar cambios</BaseButton>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
