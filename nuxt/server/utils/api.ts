import { getRequestURL, proxyRequest } from 'h3'
import type { H3Event } from 'h3'

function getUpstreamUrl(path: string): string {
    const config = useRuntimeConfig()
    return `${config.apiBaseServer.replace(/\/+$/, '')}/${path.replace(/^\/+/, '')}`
}

export function proxyApiRequest(event: H3Event, path: string) {
    const requestUrl = getRequestURL(event)
    const upstreamUrl = new URL(getUpstreamUrl(path))
    upstreamUrl.search = requestUrl.search

    return proxyRequest(event, upstreamUrl.toString())
}