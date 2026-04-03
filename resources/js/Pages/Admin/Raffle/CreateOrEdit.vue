<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseAlert from '@/Components/Base/BaseAlert.vue';

const props = defineProps({
    raffle: Object,
});

const isEdit = !!props.raffle;

const form = useForm({
    title: props.raffle?.data?.title ?? '',
    description: props.raffle?.data?.description ?? '',
    ticket_price: props.raffle?.data?.ticket_price ?? 0,
    total_tickets: props.raffle?.data?.total_tickets ?? 0,
    currency: props.raffle?.data?.currency ?? 'USD',
    draw_date: props.raffle?.data?.draw_date ? new Date(props.raffle.data.draw_date).toISOString().split('T')[0] : '',
    combos: props.raffle?.data?.combos ?? [],
    cover_image: null,
});

const previewImage = ref(props.raffle?.data?.cover_image ?? null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cover_image = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

const addCombo = () => {
    form.combos.push({ ticket_count: 0, price: 0 });
};

const removeCombo = (index) => {
    form.combos.splice(index, 1);
};

const submit = () => {
    if (isEdit) {
        form.post(route('admin.raffles.update', props.raffle.data.id), {
            _method: 'put',
            onSuccess: () => alert('Rifa actualizada con éxito'),
        });
    } else {
        form.post(route('admin.raffles.store'), {
            onSuccess: () => alert('Rifa creada con éxito'),
        });
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Editar Rifa' : 'Nueva Rifa'" />

    <AdminLayout>
        <template #header>{{ isEdit ? 'Editar' : 'Crear' }} Sorteo</template>
        
        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-12 mt-8">
            <div class="lg:col-span-2 space-y-12">
                <!-- Main Info Section -->
                <div class="glass-panel p-10 rounded-[3rem] space-y-8">
                    <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest border-b border-white/5 pb-4">Información General</h3>
                    
                    <div class="space-y-6">
                        <BaseInput 
                            v-model="form.title" 
                            label="Título del Sorteo" 
                            placeholder="Ej: Gran Rifa del iPhone 15 Pro Max"
                            :error="form.errors.title"
                        />

                        <div class="space-y-2">
                             <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Descripción detallada</label>
                             <textarea 
                                v-model="form.description" 
                                class="w-full bg-white/5 border-2 border-white/5 rounded-[1.5rem] p-4 text-white focus:border-brand-500 focus:outline-none focus:ring-0 transition-all text-sm min-h-[150px]"
                                placeholder="Describe el premio, las condiciones y la fecha del sorteo..."
                             ></textarea>
                             <p v-if="form.errors.description" class="text-red-400 text-[10px] font-bold mt-1">{{ form.errors.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="glass-panel p-10 rounded-[3rem] space-y-8">
                    <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest border-b border-white/5 pb-4">Precios y Boletos</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <BaseInput 
                            v-model="form.ticket_price" 
                            type="number" 
                            label="Precio del Ticket" 
                            :error="form.errors.ticket_price"
                        />
                        <BaseInput 
                            v-model="form.total_tickets" 
                            type="number" 
                            label="Total de Boletos" 
                            :error="form.errors.total_tickets"
                        />
                        <BaseInput 
                            v-model="form.currency" 
                            label="Moneda (ISO)" 
                            placeholder="USD, VES, COP"
                            :error="form.errors.currency"
                        />
                    </div>

                    <div class="pt-8">
                         <div class="flex items-center justify-between mb-6">
                             <h4 class="text-sm font-bold text-white uppercase tracking-tight">Combos de Descuento</h4>
                             <BaseButton type="button" size="xs" variant="outline" @click="addCombo">+ Nuevo Combo</BaseButton>
                         </div>

                         <div v-if="form.combos.length > 0" class="space-y-4">
                             <div v-for="(combo, index) in form.combos" :key="index" class="flex items-center gap-4 group">
                                 <BaseInput v-model="combo.ticket_count" type="number" label="Tickets" class="flex-1" />
                                 <BaseInput v-model="combo.price" type="number" label="Precio Promo" class="flex-1" />
                                 <button type="button" @click="removeCombo(index)" class="mt-4 p-2 text-zinc-500 hover:text-red-400 transition-colors">
                                     <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-14v4m-4-4L9 9h6l-1-2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                 </button>
                             </div>
                         </div>
                         <div v-else class="text-zinc-600 text-[10px] font-black tracking-widest text-center py-8 border-2 border-dashed border-white/5 rounded-3xl">SIN COMBOS ACTIVOS</div>
                    </div>
                </div>
            </div>

            <!-- Side configuration -->
            <div class="space-y-8">
                 <div class="glass-panel p-10 rounded-[3rem] space-y-6">
                    <h3 class="text-xs font-black uppercase text-brand-400 tracking-widest border-b border-white/5 pb-4">Configuración Final</h3>
                    
                    <BaseInput v-model="form.draw_date" type="date" label="Fecha del Sorteo" :error="form.errors.draw_date" />

                    <div class="space-y-4 pt-4">
                         <label class="text-[10px] uppercase font-black tracking-widest text-zinc-500">Imagen de Portada</label>
                         <div class="aspect-video bg-zinc-900 rounded-3xl border-2 border-white/5 overflow-hidden group relative cursor-pointer hover:border-brand-500/50 transition-all duration-500" @click="$refs.fileInput.click()">
                              <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                              <div class="absolute inset-0 flex flex-col items-center justify-center text-zinc-500 bg-black/40 group-hover:opacity-0 transition-opacity" v-if="previewImage">
                                  <span class="text-[8px] font-black uppercase tracking-widest bg-zinc-900 border border-white/10 px-2 py-1 rounded">Haz clic para cambiar</span>
                              </div>
                              <div class="w-full h-full flex flex-col items-center justify-center space-y-2 opacity-50" v-else>
                                  <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="1"/></svg>
                                  <span class="text-[10px] font-black">SUBIR PORTADA</span>
                              </div>
                              <input type="file" ref="fileInput" class="hidden" @change="handleImageChange" accept="image/*" />
                         </div>
                    </div>

                    <div class="pt-10 flex flex-col gap-3">
                         <BaseButton :loading="form.processing" variant="neon" class="w-full">{{ isEdit ? 'Actualizar' : 'Crear Sorteo' }}</BaseButton>
                         <BaseButton :href="route('admin.raffles.index')" variant="outline" class="w-full">Cancelar</BaseButton>
                    </div>
                 </div>

                 <BaseAlert v-if="isEdit" variant="warning" icon="info" title="Aviso Importante">
                     Editar el total de tickets o el precio una vez iniciada la venta puede causar inconsistencias. Proceder con precaución.
                 </BaseAlert>
            </div>
        </form>
    </AdminLayout>
</template>
