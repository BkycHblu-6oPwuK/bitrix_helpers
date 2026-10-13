
import { createError } from 'h3'
import type { H3Event } from 'h3'

export function getUpstreamUrl(path: string): URL {
    const config = useRuntimeConfig()

    if (!config.apiBaseServer) {
        throw createError({
            statusCode: 502,
            statusMessage: 'Upstream API base URL is not configured (NUXT_API_BASE_SERVER is missing or empty)',
        })
    }

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