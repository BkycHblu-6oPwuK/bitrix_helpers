/**
 * медиа без привязки к агенту пользователя, для работы на клиенте
 */
const MOBILE_QUERY = '(max-width: 767px)'
const TABLET_QUERY = '(min-width: 768px) and (max-width: 1023px)'
const LAPTOP_QUERY = '(min-width: 1024px) and (max-width: 1439px)'
const isMobile = ref(false)
const isTablet = ref(false)
const isLaptop = ref(false)
let initialized = false

function registerWatch(query: string, ref: Ref<boolean>) {
    let mql = window.matchMedia(query)
    ref.value = mql.matches
    mql.addEventListener('change', (e: MediaQueryListEvent) => ref.value = e.matches)
}

function init() {
    if (initialized || import.meta.server) return
    initialized = true

    registerWatch(MOBILE_QUERY, isMobile)
    registerWatch(TABLET_QUERY, isTablet)
    registerWatch(LAPTOP_QUERY, isLaptop)
}

export function useViewport() {
    init()

    return {
        isMobile: readonly(isMobile),
        isTablet: readonly(isTablet),
        isLaptop: readonly(isLaptop),
    }
}