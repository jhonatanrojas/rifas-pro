import { ref, onMounted, onUnmounted } from 'vue'

export function usePWAInstall() {
    const canInstall = ref(false)
    let deferredPrompt = null

    function handleBeforeInstallPrompt(e) {
        e.preventDefault()
        deferredPrompt = e
        canInstall.value = true
    }

    async function install() {
        if (!deferredPrompt) return
        deferredPrompt.prompt()
        const { outcome } = await deferredPrompt.userChoice
        if (outcome === 'accepted') {
            canInstall.value = false
            deferredPrompt = null
        }
    }

    onMounted(() => {
        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
    })

    onUnmounted(() => {
        window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
    })

    return { canInstall, install }
}
