<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import gsap from 'gsap'
import { useBreakpoints } from '@/composables/useBreakpoints'

const props = defineProps({
    tickets: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
    ticketPrice: { type: Number, default: 0 },
    currency: { type: String, default: 'USD' },
    maxSelection: { type: Number, default: 100 },
    showSummary: { type: Boolean, default: true },
    numberDigits: { type: Number, default: 4 },
})

const emit = defineEmits(['update:modelValue', 'continue'])

const { isMobile, isTablet } = useBreakpoints()

const scrollRoot = ref(null)
const scrollTop = ref(0)
const viewportHeight = ref(0)

// Filters
const activeFilter = ref('all') // all | available | selected
const activeRange = ref(null)
const searchQuery = ref('')
let searchDebounceTimer = null

// Computed
const gridCols = computed(() => {
    if (isMobile.value) return 5
    if (isTablet.value) return 8
    return 10
})

const availableRanges = computed(() => {
    const ranges = [
        { label: '1-100', min: 1, max: 100 },
        { label: '101-200', min: 101, max: 200 },
        { label: '201-500', min: 201, max: 500 },
        { label: '501+', min: 501, max: Infinity },
    ]

    return ranges.filter(r => props.tickets.some(t => t.number >= r.min && t.number <= r.max))
})

const debouncedSearch = ref('')
watch(searchQuery, (val) => {
    clearTimeout(searchDebounceTimer)
    searchDebounceTimer = setTimeout(() => {
        debouncedSearch.value = val.trim()
    }, 300)
})

function matchesSearch(ticket) {
    const q = debouncedSearch.value
    if (!q) return true

    const num = String(ticket.number)
    const padded = ticket.display_number || String(ticket.number).padStart(props.numberDigits, '0')

    if (num === q || padded === q) return true
    if (num.endsWith(q)) return true

    if (q.toLowerCase() === 'capicua' || q.toLowerCase() === 'capicúa') {
        return padded === padded.split('').reverse().join('')
    }

    if (num.startsWith(q)) return true

    return false
}

const filteredTickets = computed(() => {
    let list = props.tickets

    if (activeRange.value) {
        list = list.filter(t => t.number >= activeRange.value.min && t.number <= activeRange.value.max)
    }

    if (activeFilter.value === 'available') {
        list = list.filter(t => t.status === 'available')
    } else if (activeFilter.value === 'selected') {
        list = list.filter(t => props.modelValue.includes(t.number))
    }

    if (debouncedSearch.value) {
        list = list.filter(t => matchesSearch(t))
    }

    return list
})

const isHighlighted = (ticket) => {
    return debouncedSearch.value ? matchesSearch(ticket) : false
}

const rowHeight = 58
const overscanRows = 3

const shouldVirtualize = computed(() => filteredTickets.value.length > 500)
const totalRows = computed(() => Math.ceil(filteredTickets.value.length / gridCols.value))
const visibleRowCount = computed(() => Math.max(1, Math.ceil((viewportHeight.value || 0) / rowHeight) + overscanRows * 2))
const startRow = computed(() => {
    if (!shouldVirtualize.value) return 0
    return Math.max(0, Math.floor(scrollTop.value / rowHeight) - overscanRows)
})
const endRow = computed(() => {
    if (!shouldVirtualize.value) return totalRows.value
    return Math.min(totalRows.value, startRow.value + visibleRowCount.value)
})
const startIndex = computed(() => startRow.value * gridCols.value)
const endIndex = computed(() => Math.min(filteredTickets.value.length, endRow.value * gridCols.value))
const visibleTickets = computed(() => {
    if (!shouldVirtualize.value) return filteredTickets.value
    return filteredTickets.value.slice(startIndex.value, endIndex.value)
})
const topSpacer = computed(() => (shouldVirtualize.value ? startRow.value * rowHeight : 0))
const bottomSpacer = computed(() => {
    if (!shouldVirtualize.value) return 0
    return Math.max(0, (totalRows.value - endRow.value) * rowHeight)
})

function syncViewport() {
    viewportHeight.value = scrollRoot.value?.clientHeight ?? 0
}

function handleScroll() {
    scrollTop.value = scrollRoot.value?.scrollTop ?? 0
}

onMounted(() => {
    syncViewport()
    window.addEventListener('resize', syncViewport)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncViewport)
    clearTimeout(searchDebounceTimer)
})

watch([filteredTickets, gridCols], async () => {
    scrollTop.value = 0
    if (scrollRoot.value) {
        scrollRoot.value.scrollTop = 0
    }
    await nextTick()
    syncViewport()
})

// Ticket actions
function toggleTicket(ticket, event) {
    if (ticket.status !== 'available') return

    const selected = [...props.modelValue]
    const idx = selected.indexOf(ticket.number)

    if (idx > -1) {
        selected.splice(idx, 1)
    } else {
        if (selected.length >= props.maxSelection) return
        selected.push(ticket.number)
    }

    if (event?.currentTarget) {
        animateSelect(event.currentTarget)
    }

    navigator.vibrate?.(10)

    emit('update:modelValue', selected)
}

function animateSelect(el) {
    gsap.fromTo(el, { scale: 1 }, { scale: 1.15, duration: 0.15, yoyo: true, repeat: 1, ease: 'power2.out' })
}

function selectLucky() {
    const available = props.tickets.filter(t => t.status === 'available' && !props.modelValue.includes(t.number))
    if (!available.length) return

    const lucky = available[Math.floor(Math.random() * available.length)]
    const selected = [...props.modelValue, lucky.number]
    navigator.vibrate?.(20)
    emit('update:modelValue', selected)

    nextTick(() => {
        const el = document.getElementById(`ticket-${lucky.number}`)
        if (el) animateSelect(el)
    })
}

function clearSelection() {
    emit('update:modelValue', [])
}

function setFilter(f) {
    activeFilter.value = f
}

function setRange(r) {
    activeRange.value = activeRange.value?.label === r.label ? null : r
}

// Totals
const totalAmount = computed(() => {
    return props.modelValue.length * props.ticketPrice
})

const formattedTotal = computed(() => {
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: props.currency || 'USD',
        }).format(totalAmount.value)
    } catch {
        return `${props.currency} ${totalAmount.value.toFixed(2)}`
    }
})

function getTicketClass(ticket) {
    const isSelected = props.modelValue.includes(ticket.number)
    const highlighted = isHighlighted(ticket)

    if (ticket.status === 'winner') {
        return 'bg-yellow-500 border-yellow-400 text-black cursor-default'
    }
    if (isSelected) {
        return 'bg-brand-600 border-brand-400 text-white ring-2 ring-brand-500/50 scale-105 cursor-pointer'
    }
    if (ticket.status === 'reserved') {
        return 'bg-yellow-900/40 border-yellow-500/40 text-yellow-300 cursor-not-allowed'
    }
    if (ticket.status === 'sold') {
        return 'bg-surface-lighter/30 border-transparent text-surface-400/40 cursor-not-allowed line-through'
    }

    const base = 'bg-surface-light border-surface-300/20 hover:border-brand-500 text-white cursor-pointer'
    return highlighted ? `${base} ring-1 ring-yellow-400/60` : base
}
</script>

<template>
    <div class="flex flex-col gap-3">
        <!-- Filters Row -->
        <div class="flex flex-wrap gap-2 items-center">
            <div class="flex gap-1 bg-surface-lighter rounded-xl p-1">
                <button
                    v-for="f in [{ key: 'all', label: 'Todos' }, { key: 'available', label: 'Disponibles' }, { key: 'selected', label: 'Seleccionados' }]"
                    :key="f.key"
                    @click="setFilter(f.key)"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                        activeFilter === f.key
                            ? 'bg-surface text-brand-400 shadow'
                            : 'text-surface-400 hover:text-white'
                    ]"
                >
                    {{ f.label }}
                    <span v-if="f.key === 'selected' && modelValue.length" class="ml-1 text-brand-400">({{ modelValue.length }})</span>
                </button>
            </div>

            <button
                @click="selectLucky"
                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-accent-neon/10 border border-accent-neon/30 text-accent-neon hover:bg-accent-neon/20 transition-all flex items-center gap-1"
            >
                <span>🍀</span> Suerte
            </button>

            <div class="flex gap-1 flex-wrap">
                <button
                    v-for="range in availableRanges"
                    :key="range.label"
                    @click="setRange(range)"
                    :class="[
                        'px-2.5 py-1 rounded-lg text-xs font-medium transition-all border',
                        activeRange?.label === range.label
                            ? 'bg-brand-600 border-brand-400 text-white'
                            : 'bg-surface-light border-surface-300/20 text-surface-300 hover:border-brand-500/50'
                    ]"
                >
                    {{ range.label }}
                </button>
            </div>
        </div>

        <!-- Search / Cábala -->
        <div class="relative">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Cábala: busca por número, terminación, capicúa..."
                class="w-full bg-surface-light border border-surface-300/20 rounded-xl px-4 py-2.5 text-sm text-white placeholder-surface-400 focus:outline-none focus:border-brand-500 transition-colors"
            />
            <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-surface-400 hover:text-white"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Stats Row -->
        <div class="flex gap-3 text-xs text-surface-400">
            <span>
                <span class="text-white font-bold">{{ tickets.filter(t => t.status === 'available').length }}</span> disponibles
            </span>
            <span>·</span>
            <span>
                <span class="text-yellow-400 font-bold">{{ tickets.filter(t => t.status === 'reserved').length }}</span> reservados
            </span>
            <span>·</span>
            <span>
                <span class="text-surface-400 font-bold">{{ tickets.filter(t => t.status === 'sold').length }}</span> vendidos
            </span>
            <span v-if="filteredTickets.length !== tickets.length" class="ml-auto text-brand-400">
                Mostrando {{ filteredTickets.length }} resultados
            </span>
        </div>

        <!-- Grid -->
        <div
            ref="scrollRoot"
            @scroll="handleScroll"
            class="max-h-[60vh] overflow-y-auto pr-1 custom-scrollbar"
        >
            <div :style="{ height: `${topSpacer}px` }"></div>

            <div
                :class="[
                    'grid gap-1.5',
                    isMobile ? 'grid-cols-5' : isTablet ? 'grid-cols-8' : 'grid-cols-10'
                ]"
            >
                <button
                    v-for="ticket in visibleTickets"
                    :key="ticket.id"
                    :id="`ticket-${ticket.number}`"
                    @click="(e) => toggleTicket(ticket, e)"
                    :disabled="ticket.status === 'sold' || ticket.status === 'reserved'"
                    :class="[
                        'aspect-square rounded-lg text-xs font-bold transition-all border flex items-center justify-center min-h-[44px] select-none',
                        getTicketClass(ticket)
                    ]"
                    :title="`Número ${ticket.display_number || String(ticket.number).padStart(props.numberDigits, '0')} — ${ticket.status}`"
                >
                    {{ ticket.display_number || String(ticket.number).padStart(props.numberDigits, '0') }}
                </button>
            </div>

            <div :style="{ height: `${bottomSpacer}px` }"></div>

            <!-- Empty state -->
            <div v-if="filteredTickets.length === 0" class="flex flex-col items-center justify-center py-16 text-surface-400">
                <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm">No hay tickets con estos filtros</p>
            </div>
        </div>

        <!-- Floating Bottom Bar -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div
                v-if="showSummary && modelValue.length > 0"
            class="sticky bottom-0 bg-surface/95 backdrop-blur border-t border-brand-500/30 rounded-xl px-4 py-3 flex items-center justify-between gap-4 shadow-xl shadow-brand-500/10"
            >
                <div>
                    <p class="text-sm font-bold text-white">
                        {{ modelValue.length }} número{{ modelValue.length > 1 ? 's' : '' }}
                        <span class="text-brand-400 ml-1">{{ formattedTotal }}</span>
                    </p>
                    <button
                        @click="clearSelection"
                        class="text-xs text-surface-400 hover:text-red-400 transition-colors"
                    >
                        Limpiar selección
                    </button>
                </div>
                <button
                    @click="emit('continue')"
                    class="btn-primary px-5 py-2.5 text-sm whitespace-nowrap"
                >
                    Continuar →
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 10px; }
</style>
