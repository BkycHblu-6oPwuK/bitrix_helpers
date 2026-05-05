
import type { H3Event } from 'h3'

export function getUpstreamUrl(path: string): URL {
    const config = useRuntimeConfig()
    return new URL(`${config.apiBaseServer.replace(/\/+$/, '')}/${path.replace(/^\/+/, '').replace(/\/+$/, '')}`)
}

export function getApiPath(event: H3Event): string | null {
    const fromParams = getRouterParam(event, 'path')
    if (fromParams) {
        return fromParams
    }

    const url = getRequestURL(event)
    const match = url.pathname.match(/^\/api\/v1\/(.+)$/)
    return match?.[1] ?? null
}