<script setup>
import BaseAvatar from '@/Components/Base/BaseAvatar.vue'
import BaseBadge from '@/Components/Base/BaseBadge.vue'

defineProps({
    winner: {
        type: Object,
        required: true,
        // {
        //   user: { name, avatar },
        //   ticket: { number },
        //   prize_description,
        //   raffle: { title },
        //   testimony,
        //   photo_path,
        // }
    },
})

function truncate(text, max = 100) {
    if (!text) return ''
    return text.length > max ? text.slice(0, max).trimEnd() + '...' : text
}
</script>

<template>
    <div class="glass-card rounded-2xl overflow-hidden border border-surface-300/10 hover:border-brand-500/30 transition-all duration-300 group">

        <!-- Winner photo (if available) -->
        <div v-if="winner.photo_path" class="relative h-40 overflow-hidden">
            <img
                :src="`/storage/${winner.photo_path}`"
                :alt="winner.user?.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-surface-light via-surface-light/30 to-transparent"></div>
        </div>

        <div class="p-5 space-y-3">
            <!-- Header row: avatar + name + ticket badge -->
            <div class="flex items-start gap-3">
                <BaseAvatar
                    :src="winner.user?.avatar ? `/storage/${winner.user.avatar}` : null"
                    :name="winner.user?.name"
                    size="lg"
                    shape="circle"
                />

                <div class="flex-1 min-w-0 space-y-1">
                    <p class="font-bold text-white truncate leading-tight">
                        {{ winner.user?.name }}
                    </p>
                    <!-- Ticket badge (golden) -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                        🎟️ #{{ winner.ticket?.display_number ?? String(winner.ticket?.number ?? 0).padStart(winner.raffle?.number_digits || 4, '0') }}
                    </span>
                </div>
            </div>

            <!-- Prize -->
            <div>
                <p class="text-[10px] text-surface-400 uppercase tracking-widest font-semibold mb-0.5">Premio</p>
                <p class="text-brand-400 font-bold text-sm leading-snug">
                    {{ winner.prize_description }}
                </p>
            </div>

            <!-- Testimony excerpt -->
            <p
                v-if="winner.testimony"
                class="text-surface-300 text-xs leading-relaxed italic border-l-2 border-brand-500/30 pl-3"
            >
                "{{ truncate(winner.testimony, 100) }}"
            </p>

            <!-- Raffle name badge -->
            <p v-if="winner.raffle?.title" class="text-surface-400 text-xs truncate">
                {{ winner.raffle.title }}
            </p>
        </div>
    </div>
</template>
