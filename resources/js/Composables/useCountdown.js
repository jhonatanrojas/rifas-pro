import { ref, computed, onMounted, onUnmounted } from 'vue'

export function useCountdown(targetDate) {
    const now = ref(Date.now())
    let intervalId = null

    const target = computed(() => {
        if (!targetDate) return 0
        const d = targetDate instanceof Date ? targetDate : new Date(targetDate)
        return d.getTime()
    })

    const diff = computed(() => Math.max(0, target.value - now.value))

    const isExpired = computed(() => diff.value === 0)

    const days = computed(() => Math.floor(diff.value / (1000 * 60 * 60 * 24)))
    const hours = computed(() => Math.floor((diff.value % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)))
    const minutes = computed(() => Math.floor((diff.value % (1000 * 60 * 60)) / (1000 * 60)))
    const seconds = computed(() => Math.floor((diff.value % (1000 * 60)) / 1000))

    onMounted(() => {
        intervalId = setInterval(() => {
            now.value = Date.now()
        }, 1000)
    })

    onUnmounted(() => {
        if (intervalId) clearInterval(intervalId)
    })

    return { days, hours, minutes, seconds, isExpired }
}
