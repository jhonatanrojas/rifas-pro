import { ref, onMounted } from 'vue'

function getCookie(name) {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return parts.pop().split(';').shift()
    return null
}

export function useAffiliateCode() {
    const referralCode = ref(null)

    onMounted(() => {
        // First check URL query param 'ref'
        const params = new URLSearchParams(window.location.search)
        const refParam = params.get('ref')

        if (refParam) {
            referralCode.value = refParam
            // Persist in cookie for 30 days
            const expires = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString()
            document.cookie = `affiliate_code=${refParam}; expires=${expires}; path=/; SameSite=Lax`
        } else {
            // Fall back to cookie
            referralCode.value = getCookie('affiliate_code')
        }
    })

    return { referralCode }
}
