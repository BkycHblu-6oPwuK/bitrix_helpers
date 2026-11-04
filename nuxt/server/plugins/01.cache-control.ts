import { createHash } from 'node:crypto'

/** If-None-Match: список ETag, слабое сравнение (W/ игнорируется) или "*" */
function matchesEtag(header: string, etag: string): boolean {
    return header.split(',').some((value) => {
        const candidate = value.trim()
        return candidate === '*' || candidate.replace(/^W\//, '') === etag
    })
}

function notModifiedSince(header: string, lastModified: number): boolean {
    const since = Date.parse(header)
    return !Number.isNaN(since) && Math.floor(lastModified / 1000) <= Math.floor(since / 1000)
}

export default defineNitroPlugin((nitroApp) => {
    nitroApp.hooks.hook('render:response', (_html, { event }) => {
        if (getResponseStatus(event) >= 400) {
            setResponseHeader(event, 'cache-control', 'no-store')
            return
        }
        setResponseHeader(event, 'cache-control', 'no-cache')

        const etags = event.context.__apiEtags as string[] | undefined
        const lastModified = event.context.__lastModified as number | undefined
        if (!etags?.length || !lastModified) return

        const { buildAt } = useRuntimeConfig(event)
        const buildTimestamp = Date.parse(buildAt)
        const effectiveLastModified = Math.max(
            lastModified,
            Number.isNaN(buildTimestamp) ? 0 : buildTimestamp,
        )

        const [path] = event.path.split('?')
        const etag = `"${createHash('sha256')
            .update(JSON.stringify({ path, buildAt, etags: [...etags].sort() }))
            .digest('hex')}"`

        setResponseHeaders(event, {
            'etag': etag,
            'last-modified': new Date(effectiveLastModified).toUTCString(),
        })

        const ifNoneMatch = getRequestHeader(event, 'if-none-match')
        const ifModifiedSince = getRequestHeader(event, 'if-modified-since')

        // If-None-Match приоритетнее If-Modified-Since (RFC 9110)
        event.context.__notModified = ifNoneMatch !== undefined
            ? matchesEtag(ifNoneMatch, etag)
            : !!ifModifiedSince && notModifiedSince(ifModifiedSince, effectiveLastModified)
    })

    nitroApp.hooks.hook('beforeResponse', (event, response) => {
        if (!event.context.__notModified) return

        setResponseStatus(event, 304)
        for (const name of ['content-length', 'content-type', 'transfer-encoding']) {
            removeResponseHeader(event, name)
        }
        response.body = null
    })
})