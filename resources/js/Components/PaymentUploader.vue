<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: { type: [File, null], default: null },
})

const emit = defineEmits(['update:modelValue'])

const isDragging = ref(false)
const isOptimizing = ref(false)
const preview = ref(null)
const ocrState = ref('idle') // idle | scanning | detected
const ocrFields = ref({ amount: '', reference: '', bank: '', confidence: '' })

const MAX_DIRECT_UPLOAD_BYTES = 1.8 * 1024 * 1024
const MAX_IMAGE_DIMENSION = 1600

function startOcrSim() {
    ocrState.value = 'scanning'
    setTimeout(() => {
        ocrState.value = 'detected'
    }, 2000)
}

function canvasToBlob(canvas, type, quality) {
    return new Promise((resolve) => {
        canvas.toBlob((blob) => resolve(blob), type, quality)
    })
}

function nextFilename(file, fallback = 'comprobante.jpg') {
    const baseName = file?.name ? file.name.replace(/\.[^.]+$/, '') : fallback.replace(/\.[^.]+$/, '')
    return `${baseName}.jpg`
}

async function optimizeImage(file) {
    if (!file || file.size <= MAX_DIRECT_UPLOAD_BYTES) {
        return file
    }

    if (typeof createImageBitmap !== 'function') {
        return file
    }

    const bitmap = await createImageBitmap(file)
    const scale = Math.min(1, MAX_IMAGE_DIMENSION / Math.max(bitmap.width, bitmap.height))
    const width = Math.max(1, Math.round(bitmap.width * scale))
    const height = Math.max(1, Math.round(bitmap.height * scale))
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    if (!ctx) return file

    canvas.width = width
    canvas.height = height
    ctx.drawImage(bitmap, 0, 0, width, height)

    for (const quality of [0.82, 0.72, 0.62]) {
        const blob = await canvasToBlob(canvas, 'image/jpeg', quality)
        if (blob && blob.size <= MAX_DIRECT_UPLOAD_BYTES) {
            return new File([blob], nextFilename(file), {
                type: 'image/jpeg',
                lastModified: Date.now(),
            })
        }
    }

    const fallbackBlob = await canvasToBlob(canvas, 'image/jpeg', 0.55)
    if (fallbackBlob) {
        return new File([fallbackBlob], nextFilename(file), {
            type: 'image/jpeg',
            lastModified: Date.now(),
        })
    }

    return file
}

watch(() => props.modelValue, (file) => {
    if (!file) {
        if (preview.value) URL.revokeObjectURL(preview.value)
        preview.value = null
        ocrState.value = 'idle'
        ocrFields.value = { amount: '', reference: '', bank: '', confidence: '' }
        return
    }

    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = URL.createObjectURL(file)
    startOcrSim()
})

async function handleFile(file) {
    if (!file) return
    if (!file.type.startsWith('image/')) {
        alert('Solo se permiten imagenes para el comprobante.')
        return
    }

    isOptimizing.value = true
    try {
        const optimized = await optimizeImage(file)
        emit('update:modelValue', optimized)
    } finally {
        isOptimizing.value = false
    }
}

function onFileInput(e) {
    const file = e.target.files?.[0]
    if (file) handleFile(file)
}

function onDrop(e) {
    isDragging.value = false
    const file = e.dataTransfer?.files?.[0]
    if (file && file.type.startsWith('image/')) handleFile(file)
}

function onDragOver(e) {
    e.preventDefault()
    isDragging.value = true
}

function onDragLeave() {
    isDragging.value = false
}

function removeFile() {
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = null
    emit('update:modelValue', null)
}

onBeforeUnmount(() => {
    if (preview.value) URL.revokeObjectURL(preview.value)
})
</script>

<template>
    <div class="space-y-4">
        <div
            v-if="!modelValue"
            @drop.prevent="onDrop"
            @dragover.prevent="onDragOver"
            @dragleave="onDragLeave"
            :class="[
                'relative border-2 border-dashed rounded-2xl transition-all duration-200 p-6',
                isDragging
                    ? 'border-brand-500 bg-brand-500/10'
                    : 'border-surface-300/30 bg-surface-light hover:border-brand-500/60 hover:bg-surface-lighter'
            ]"
        >
            <div class="flex flex-col items-center gap-3 text-center">
                <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center">
                    <svg class="w-7 h-7 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>

                <div>
                    <p class="text-sm font-bold text-white">Arrastra tu comprobante aqui</p>
                    <p class="text-xs text-surface-400 mt-0.5">PNG, JPG o WEBP. Se optimiza automaticamente si supera 2 MB.</p>
                </div>

                <label class="hidden sm:block btn-secondary text-sm cursor-pointer">
                    <span v-if="isOptimizing">Optimizando...</span>
                    <span v-else>Seleccionar archivo</span>
                    <input type="file" accept="image/*" class="hidden" @change="onFileInput" />
                </label>

                <div class="flex gap-2 sm:hidden">
                    <label class="flex items-center gap-1.5 btn-secondary text-xs cursor-pointer px-3 py-2">
                        <span v-if="isOptimizing">...</span>
                        <span v-else>📷</span> Tomar foto
                        <input type="file" accept="image/*" capture="camera" class="hidden" @change="onFileInput" />
                    </label>
                    <label class="flex items-center gap-1.5 btn-secondary text-xs cursor-pointer px-3 py-2">
                        <span>📁</span> Subir archivo
                        <input type="file" accept="image/*" class="hidden" @change="onFileInput" />
                    </label>
                </div>
            </div>
        </div>

        <div v-else class="relative rounded-2xl overflow-hidden border border-surface-300/20 bg-surface-light">
            <img
                :src="preview"
                alt="Comprobante de pago"
                class="w-full h-[200px] object-contain bg-surface/50 p-2"
            />
            <button
                @click="removeFile"
                class="absolute top-2 right-2 w-8 h-8 bg-surface/80 rounded-full flex items-center justify-center text-surface-300 hover:text-white hover:bg-red-600/80 transition-all"
                title="Eliminar archivo"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="px-3 py-2 border-t border-surface-300/10">
                <p class="text-xs text-surface-400 truncate">{{ modelValue?.name }}</p>
            </div>
        </div>

        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
        >
            <div v-if="modelValue && ocrState !== 'idle'" class="rounded-2xl border border-surface-300/20 bg-surface-light overflow-hidden">
                <div class="px-4 py-3 border-b border-surface-300/10 flex items-center gap-2">
                    <div :class="['w-2 h-2 rounded-full', ocrState === 'scanning' ? 'bg-yellow-400 animate-pulse' : 'bg-green-400']"></div>
                    <span class="text-xs font-bold text-surface-300">
                        {{ ocrState === 'scanning' ? 'Analizando comprobante...' : 'Datos detectados' }}
                    </span>
                </div>

                <div v-if="ocrState === 'scanning'" class="p-4 space-y-2.5">
                    <div v-for="i in 4" :key="i" class="h-8 bg-surface-lighter rounded-lg animate-pulse"></div>
                </div>

                <div v-else class="p-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-surface-400 mb-1 block">Monto</label>
                        <input
                            v-model="ocrFields.amount"
                            type="text"
                            placeholder="-"
                            class="w-full bg-surface-lighter border border-surface-300/20 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-brand-500"
                        />
                    </div>
                    <div>
                        <label class="text-xs text-surface-400 mb-1 block">Referencia</label>
                        <input
                            v-model="ocrFields.reference"
                            type="text"
                            placeholder="-"
                            class="w-full bg-surface-lighter border border-surface-300/20 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-brand-500"
                        />
                    </div>
                    <div>
                        <label class="text-xs text-surface-400 mb-1 block">Banco</label>
                        <input
                            v-model="ocrFields.bank"
                            type="text"
                            placeholder="-"
                            class="w-full bg-surface-lighter border border-surface-300/20 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-brand-500"
                        />
                    </div>
                    <div>
                        <label class="text-xs text-surface-400 mb-1 block">Confianza</label>
                        <input
                            v-model="ocrFields.confidence"
                            type="text"
                            placeholder="-"
                            class="w-full bg-surface-lighter border border-surface-300/20 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-brand-500"
                        />
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
