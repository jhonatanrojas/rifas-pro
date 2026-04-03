import { useBreakpoints as useVueBreakpoints } from '@vueuse/core'
import { computed } from 'vue'

export function useBreakpoints() {
    const breakpoints = useVueBreakpoints({
        sm: 640,
        md: 768,
        lg: 1024,
    })

    const isMobile = computed(() => breakpoints.smaller('sm').value)
    const isTablet = computed(() => breakpoints.between('sm', 'lg').value)
    const isDesktop = computed(() => breakpoints.greaterOrEqual('lg').value)

    return { isMobile, isTablet, isDesktop }
}
