<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseAlert from '@/Components/Base/BaseAlert.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';

const props = defineProps({
    raffle: Object,
});

const isEdit = computed(() => !!props.raffle?.data?.id);
const activeTab = ref('basic');
const coverPreview = ref(props.raffle?.data?.cover_image ?? null);
const coverInput = ref(null);

const initialPrizes = (props.raffle?.data?.prizes ?? []).length
    ? props.raffle.data.prizes.map((prize) => ({
        title: prize.title ?? '',
        description: prize.description ?? '',
        image: null,
        preview_url: prize.image_path ?? null,
        is_featured: !!prize.is_featured,
    }))
    : [{ title: '', description: '', image: null, preview_url: null, is_featured: true }];

const initialCombos = (props.raffle?.data?.combos ?? []).map((combo) => ({
    quantity: combo.quantity ?? 0,
    price: combo.price ?? 0,
    label: combo.label ?? '',
}));

const form = useForm({
    title: props.raffle?.data?.title ?? '',
    category: props.raffle?.data?.category ?? '',
    description: props.raffle?.data?.description ?? '',
    ticket_price: props.raffle?.data?.ticket_price ?? 0,
    total_tickets: props.raffle?.data?.total_tickets ?? 1000,
    currency: props.raffle?.data?.currency ?? 'USD',
    draw_date: props.raffle?.data?.draw_date ? new Date(props.raffle.data.draw_date).toISOString().split('T')[0] : '',
    draw_type: props.raffle?.data?.draw_type ?? 'internal_random',
    status: props.raffle?.data?.status ?? 'draft',
    is_featured: props.raffle?.data?.is_featured ?? false,
    combos: initialCombos,
    prizes: initialPrizes,
    cover_image: null,
});

const tabs = [
    { id: 'basic', label: 'Información básica' },
    { id: 'numbers', label: 'Números' },
    { id: 'pricing', label: 'Precios y combos' },
    { id: 'draw', label: 'Sorteo' },
];

function openCoverPicker() {
    coverInput.value?.click();
}

function handleCoverImageChange(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    form.cover_image = file;
    coverPreview.value = URL.createObjectURL(file);
}

function addCombo() {
    form.combos.push({ quantity: 0, price: 0, label: '' });
}

function removeCombo(index) {
    form.combos.splice(index, 1);
}

function addPrize() {
    form.prizes.push({
        title: '',
        description: '',
        image: null,
        preview_url: null,
        is_featured: false,
    });
}

function removePrize(index) {
    form.prizes.splice(index, 1);
}

function handlePrizeImageChange(index, event) {
    const file = event.target.files?.[0];
    if (!file) return;

    form.prizes[index].image = file;
    form.prizes[index].preview_url = URL.createObjectURL(file);
}

function numberRangeLabel(totalTickets) {
    const total = Math.max(1, Number(totalTickets || 0));
    const maxNumber = Math.max(0, total - 1);
    const digits = Math.max(3, String(maxNumber).length);

    return `${String(0).padStart(digits, '0')} - ${String(maxNumber).padStart(digits, '0')}`;
}

function submit() {
    const payload = form;

    if (isEdit.value) {
        payload.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.raffles.update', props.raffle.data.slug), {
            preserveScroll: true,
            onSuccess: () => {
                activeTab.value = 'basic';
            },
        });
        return;
    }

    payload.post(route('admin.raffles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            activeTab.value = 'basic';
        },
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Editar rifa' : 'Nueva rifa'" />

    <AdminLayout>
        <template #header>{{ isEdit ? 'Editar' : 'Crear' }} rifa</template>
        <template #description>Organiza información, números, precios y sorteo sin salir del panel.</template>

        <form @submit.prevent="submit" class="grid grid-cols-1 xl:grid-cols-3 gap-8 mt-8">
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

                <div v-if="activeTab === 'basic'" class="glass-panel p-8 sm:p-10 rounded-[3rem] space-y-8">
                    <div class="flex items-center justify-between gap-4 border-b border-white/5 pb-4">
                        <div>
                            <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest">Información básica</h3>
                            <p class="text-xs text-zinc-500 mt-1">Título, descripción, portada y galería de premios.</p>
                        </div>
                        <BaseButton type="button" size="sm" variant="outline" @click="addPrize">+ Premio</BaseButton>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="form.title" label="Título" placeholder="Ej: Gran Rifa del iPhone 15" :error="form.errors.title" />
                        <BaseInput v-model="form.category" label="Categoría" placeholder="Ej: Electrónicos" :error="form.errors.category" />
                    </div>

                    <RichTextEditor
                        v-model="form.description"
                        label="Descripción enriquecida"
                        placeholder="Describe la rifa, las condiciones y la entrega de premios..."
                        :error="form.errors.description"
                    />

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Imagen destacada</label>
                            <div class="aspect-video bg-zinc-900 rounded-3xl border-2 border-white/5 overflow-hidden relative cursor-pointer hover:border-brand-500/50 transition-all duration-500" @click="openCoverPicker">
                                <img v-if="coverPreview" :src="coverPreview" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center space-y-2 text-zinc-500">
                                    <span class="text-[10px] font-black uppercase tracking-widest">Subir portada</span>
                                </div>
                                <input ref="coverInput" type="file" class="hidden" @change="handleCoverImageChange" accept="image/*" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-black uppercase tracking-widest text-white">Galería de premios</h4>
                                <span class="text-xs text-zinc-500">{{ form.prizes.length }} item(s)</span>
                            </div>

                            <div v-for="(prize, index) in form.prizes" :key="index" class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4 space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-black uppercase tracking-widest text-brand-400">Premio {{ index + 1 }}</p>
                                    <button
                                        v-if="form.prizes.length > 1"
                                        type="button"
                                        class="text-xs font-bold text-red-400 hover:text-red-300"
                                        @click="removePrize(index)"
                                    >
                                        Eliminar
                                    </button>
                                </div>

                                <BaseInput v-model="prize.title" label="Título del premio" placeholder="Ej: Motocicleta 0km" />
                                <div class="space-y-2">
                                    <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Descripción</label>
                                    <textarea
                                        v-model="prize.description"
                                        class="w-full bg-white/5 border border-white/10 rounded-[1.25rem] px-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-500 min-h-[110px]"
                                        placeholder="Describe este premio..."
                                    ></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Imagen</label>
                                        <input type="file" class="block w-full text-sm text-zinc-300" accept="image/*" @change="handlePrizeImageChange(index, $event)" />
                                    </div>
                                    <label class="flex items-center gap-2 pt-6 text-sm text-zinc-300">
                                        <input v-model="prize.is_featured" type="checkbox" class="rounded border-white/20 bg-white/5" />
                                        Destacar premio
                                    </label>
                                </div>

                                <div v-if="prize.preview_url" class="aspect-video rounded-2xl overflow-hidden border border-white/10">
                                    <img :src="prize.preview_url" class="w-full h-full object-cover" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'numbers'" class="glass-panel p-8 sm:p-10 rounded-[3rem] space-y-8">
                    <div class="border-b border-white/5 pb-4">
                        <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest">Configuración de números</h3>
                        <p class="text-xs text-zinc-500 mt-1">La tabla de tickets se genera automáticamente al guardar. El rango depende del total de boletos.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="form.total_tickets" type="number" label="Cantidad de números" :error="form.errors.total_tickets" />
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Rango estimado</label>
                            <div class="rounded-[1.5rem] border border-brand-500/20 bg-brand-500/5 px-4 py-4 text-white font-black">
                                {{ numberRangeLabel(form.total_tickets) }}
                            </div>
                        </div>
                    </div>

                    <BaseAlert type="info" title="Generación automática">
                        Al guardar la rifa, el sistema crea la disponibilidad de boletos en base de datos y evita duplicados por número.
                    </BaseAlert>
                </div>

                <div v-if="activeTab === 'pricing'" class="glass-panel p-8 sm:p-10 rounded-[3rem] space-y-8">
                    <div class="border-b border-white/5 pb-4">
                        <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest">Precios y combos</h3>
                        <p class="text-xs text-zinc-500 mt-1">Define el precio individual y los descuentos por volumen.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <BaseInput v-model="form.ticket_price" type="number" label="Precio por ticket" :error="form.errors.ticket_price" />
                        <BaseInput v-model="form.currency" label="Moneda" placeholder="USD" :error="form.errors.currency" />
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-black uppercase tracking-widest text-white">Configurador de descuentos</h4>
                            <BaseButton type="button" size="sm" variant="outline" @click="addCombo">+ Combo</BaseButton>
                        </div>

                        <div v-for="(combo, index) in form.combos" :key="index" class="grid grid-cols-1 md:grid-cols-3 gap-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                            <BaseInput v-model="combo.quantity" type="number" label="Cantidad" />
                            <BaseInput v-model="combo.price" type="number" label="Precio combo" />
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Etiqueta</label>
                                <input v-model="combo.label" type="text" class="w-full bg-white/5 border border-white/10 rounded-[1.25rem] px-4 py-3 text-white placeholder-zinc-500 focus:outline-none focus:border-brand-500" placeholder="Ej: Lleva 3 por $12" />
                                <button v-if="form.combos.length > 1" type="button" class="text-xs font-bold text-red-400 hover:text-red-300" @click="removeCombo(index)">Eliminar combo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'draw'" class="glass-panel p-8 sm:p-10 rounded-[3rem] space-y-8">
                    <div class="border-b border-white/5 pb-4">
                        <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest">Tipo de sorteo</h3>
                        <p class="text-xs text-zinc-500 mt-1">Selecciona entre ejecución automática interna o manual/externa.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <BaseInput v-model="form.draw_date" type="date" label="Fecha del sorteo" :error="form.errors.draw_date" />
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Modo de sorteo</label>
                            <select v-model="form.draw_type" class="w-full rounded-[1.25rem] bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                                <option value="internal_random">Automático</option>
                                <option value="external_lottery">Manual / Externo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Estado</label>
                            <select v-model="form.status" class="w-full rounded-[1.25rem] bg-white/5 border border-white/10 px-4 py-3 text-sm text-white">
                                <option value="draft">Borrador</option>
                                <option value="active">Activa</option>
                                <option value="paused">Pausada</option>
                                <option value="drawn">Sorteada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>

                            <label class="flex items-center gap-3 pt-7 text-sm text-zinc-300">
                                <input v-model="form.is_featured" type="checkbox" class="rounded border-white/20 bg-white/5" />
                                Marcar como destacada
                            </label>
                    </div>

                <BaseAlert type="warning" title="Importante">
                    Si cambias la cantidad de boletos después de generar tickets, el sistema agregará faltantes, pero no elimina boletos ya creados.
                </BaseAlert>
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass-panel p-8 rounded-[3rem] space-y-6">
                    <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest border-b border-white/5 pb-4">Resumen</h3>

                    <div class="space-y-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Rifa</span>
                            <span class="text-white font-semibold">{{ form.title || 'Nueva rifa' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Categoría</span>
                            <span class="text-white font-semibold">{{ form.category || 'Sin categoría' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Boletos</span>
                            <span class="text-white font-semibold">{{ form.total_tickets }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Rango</span>
                            <span class="text-white font-semibold">{{ numberRangeLabel(form.total_tickets) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Combos</span>
                            <span class="text-white font-semibold">{{ form.combos.length }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Premios</span>
                            <span class="text-white font-semibold">{{ form.prizes.length }}</span>
                        </div>
                    </div>

                    <div class="pt-4 flex flex-col gap-3">
                        <BaseButton :loading="form.processing" variant="neon" class="w-full">{{ isEdit ? 'Actualizar rifa' : 'Crear rifa' }}</BaseButton>
                        <BaseButton :href="route('admin.raffles.index')" variant="outline" class="w-full">Cancelar</BaseButton>
                    </div>
                </div>

                <BaseAlert v-if="isEdit" type="warning" title="Aviso importante">
                    Editar boletos, precio o premios en una rifa ya activa puede afectar ventas y reportes.
                </BaseAlert>
            </div>
        </form>
    </AdminLayout>
</template>
