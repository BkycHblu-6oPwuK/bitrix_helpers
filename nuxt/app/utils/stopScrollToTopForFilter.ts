import type { RouteLocationNormalized } from 'vue-router'

export function getBasePathNotFilter(route: Pick<RouteLocationNormalized, 'params'>) {
    const raw = route.params.search
    const parts = (Array.isArray(raw) ? raw : raw ? [raw] : []).filter(Boolean)
    const i = parts.indexOf('filter')
    return (i === -1 ? parts : parts.slice(0, i)).join('/')
}

export function stopScrollToTopForFilter(to: RouteLocationNormalized, from: RouteLocationNormalized) {
    if (to.name !== from.name) return true
    return getBasePathNotFilter(to) !== getBasePathNotFilter(from)
}