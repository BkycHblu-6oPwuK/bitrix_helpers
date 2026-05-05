import { getRequestURL, proxyRequest } from 'h3'
import type { H3Event } from 'h3'

export function proxyApiRequest(event: H3Event, upstreamUrl: URL) {
    const requestUrl = getRequestURL(event)
    upstreamUrl.search = requestUrl.search
    return proxyRequest(event, upstreamUrl.toString())
}

export async function fetchProxy(event: H3Event, upstreamUrl: URL) {
    const requestUrl = getRequestURL(event)
    upstreamUrl.search = requestUrl.search

    const incomingHeaders: Record<string, string> = {}

    for (const [key, value] of Object.entries(getRequestHeaders(event))) {
        if (typeof value === 'string') {
            incomingHeaders[key] = value
        }
    }

    delete incomingHeaders['host']
    delete incomingHeaders['connection']
    delete incomingHeaders['content-length']
    delete incomingHeaders['accept-encoding']

    const hasBody = !['GET', 'HEAD'].includes(event.method)

    const body = hasBody ? await readRawBody(event) : undefined

    const controller = new AbortController()
    const timeout = setTimeout(() => controller.abort(), 10000)

    const res = await fetch(upstreamUrl.toString(), {
        method: event.method,
        headers: incomingHeaders,
        body,
        signal: controller.signal,
    }).finally(() => clearTimeout(timeout))

    setResponseStatus(event, res.status)

    const setCookies = res.headers.getSetCookie?.()
    if (setCookies) {
        for (const cookie of setCookies) {
            appendHeader(event, 'set-cookie', cookie)
        }
    }

    for (const [key, value] of res.headers.entries()) {
        if (key === 'transfer-encoding') continue
        if (key === 'connection') continue
        if (key === 'set-cookie') continue
        if (key === 'content-encoding') continue
        if (key === 'content-length') continue

        appendHeader(event, key, value)
    }

    const contentType = res.headers.get('content-type') || ''

    if (contentType.includes('application/json')) {
        return await res.json()
    }

    if (contentType.startsWith('text/') || contentType.includes('xml')) {
        return await res.text()
    }

    return Buffer.from(await res.arrayBuffer())
}