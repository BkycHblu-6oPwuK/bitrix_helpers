import { createHash } from 'node:crypto'

export default defineNitroPlugin((nitroApp) => {
    nitroApp.hooks.hook('render:response', (_html, { event }) => {
        const etags = event.context.__apiEtags as string[] | undefined
        const lastModified = event.context.__lastModified as number | undefined

        if (!etags?.length || !lastModified) {
            return
        }

        const config = useRuntimeConfig(event)
        const buildTimestamp = Date.parse(config.buildAt)

        const effectiveLastModified = Math.max(
            lastModified,
            Number.isNaN(buildTimestamp) ? 0 : buildTimestamp,
        )

        const path = event.path.split('?')[0]

        const etag = `"${createHash('sha256')
            .update(JSON.stringify({
                path,
                buildAt: config.buildAt,
                etags: [...etags].sort(),
            }))
            .digest('hex')}"`

        const res = event.node.res

        res.setHeader('ETag', etag)
        res.setHeader(
            'Last-Modified',
            new Date(effectiveLastModified).toUTCString(),
        )

        const ifNoneMatch = event.node.req.headers['if-none-match']
        const ifModifiedSince = event.node.req.headers['if-modified-since']

        let notModified = false

        if (ifNoneMatch !== undefined) {
            const candidates = Array.isArray(ifNoneMatch)
                ? ifNoneMatch
                : [ifNoneMatch]

            notModified = candidates.some((header) =>
                header.split(',').some((value) => {
                    const candidate = value.trim()

                    return candidate === '*' ||
                        candidate.replace(/^W\//, '') === etag
                }),
            )
        } else if (ifModifiedSince) {
            const since = Date.parse(ifModifiedSince)

            notModified =
                !Number.isNaN(since) &&
                Math.floor(effectiveLastModified / 1000) <=
                Math.floor(since / 1000)
        }

        event.context.__notModified = notModified
    })

    nitroApp.hooks.hook('beforeResponse', (event, response) => {
        if (!event.context.__notModified) {
            return
        }

        event.node.res.statusCode = 304

        event.node.res.removeHeader('Content-Length')
        event.node.res.removeHeader('Content-Type')
        event.node.res.removeHeader('Transfer-Encoding')

        response.body = null
    })
})