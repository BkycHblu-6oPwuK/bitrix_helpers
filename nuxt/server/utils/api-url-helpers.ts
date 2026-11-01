import { createError } from 'h3'
import type { H3Event } from 'h3'


function getApiPath(event: H3Event, apiVersion: string): string | null {
    const fromParams = getRouterParam(event, 'path')
    if (fromParams) {
        return fromParams
    }

    const url = getRequestURL(event)
    const match = url.pathname.match(new RegExp(`^/api/${apiVersion}/(.+)$`))
    return match?.[1] ?? null
}

export function getUpstreamUrl(event: H3Event, apiVersion: string = 'v1'): URL {
    const path = getApiPath(event, apiVersion)
    if (!path) {
        throw createError({
            statusCode: 400,
            statusMessage: 'API path is required',
        })
    }
    const config = useRuntimeConfig()

    if (!config.apiBaseServer) {
        throw createError({
            statusCode: 502,
            statusMessage: 'Upstream API base URL is not configured (NUXT_API_BASE_SERVER is missing or empty)',
        })
    }

    return new URL(`${config.apiBaseServer.replace(/\/+$/, '')}/${getCleanPath(path)}`)
}