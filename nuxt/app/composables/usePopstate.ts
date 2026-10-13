export const usePopstate = (callback: (event: PopStateEvent) => void) => {
    if (import.meta.client) {
        onMounted(() => {
            window.addEventListener('popstate', callback)
        })
        onUnmounted(() => {
            window.removeEventListener('popstate', callback)
        })
    }
}