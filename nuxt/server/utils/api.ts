import {
    getRequestURL,
    getRequestHeaders,
    readRawBody,
    setResponseStatus,
    appendHeader,
} from 'h3'
import { getUpstreamUrl } from './api-url-helpers'
import { filterRequestHeaders, isExcludedCookie } from './api-header-helpers'
import type { H3Event } from 'h3'

export async function fetchProxy(event: H3Event, version: string = 'v1') {
    const upstreamUrl = getUpstreamUrl(event, version)
    const requestUrl = getRequestURL(event)
    upstreamUrl.search = requestUrl.search

    const incomingHeaders = filterRequestHeaders(getRequestHeaders(event))

    const hasBody = !['GET', 'HEAD'].includes(event.method)
    const body = hasBody ? await readRawBody(event) : undefined

    const controller = new AbortController()
    const timeout = setTimeout(() => controller.abort(), 10000)

    let res: Response

    try {
        res = await fetch(upstreamUrl.toString(), {
            method: event.method,
            headers: incomingHeaders,
            body,
            signal: controller.signal,
        })
    } finally {
        clearTimeout(timeout)
    }

    setResponseStatus(event, res.status)
    const setCookies = res.headers.getSetCookie?.() ?? []

    for (const cookie of setCookies) {
        const cookieName = cookie.split(';', 1)[0]

        if (isExcludedCookie(cookieName ?? '')) continue

        appendHeader(event, 'set-cookie', cookie)
    }

    for (const [key, value] of res.headers.entries()) {
        const name = key.toLowerCase()

        if (
            name === 'transfer-encoding' ||
            name === 'connection' ||
            name === 'set-cookie' ||
            name === 'content-encoding' ||
            name === 'content-length'
        ) {
            continue
        }

        appendHeader(event, name, value)
    }

    if (event.method === 'HEAD' || res.status === 204 || res.status === 304) {
        return ''
    }

    const contentType = res.headers.get('content-type') || ''

    if (
        contentType.includes('application/json') ||
        contentType.startsWith('text/') ||
        contentType.includes('xml')
    ) {
        return await res.text()
    }

    return Buffer.from(await res.arrayBuffer())
}