import { createError } from 'h3'
import { fetchProxy } from '../../utils/api'
import { getUpstreamUrl, getApiPath } from './utils/api'

export default defineEventHandler(async (event) => {
    const path = getApiPath(event)

    if (!path) {
        throw createError({
            statusCode: 400,
            statusMessage: 'API path is required',
        })
    }

    try {
        return await fetchProxy(event, getUpstreamUrl(path))
    } catch (err: any) {
        if (err.statusCode) throw err

        const cause = err?.cause
        const isNetworkError = cause?.code === 'ENOTFOUND' || cause?.code === 'ECONNREFUSED' || cause?.code === 'ECONNRESET'

        throw createError({
            statusCode: 502,
            statusMessage: isNetworkError
                ? `Upstream unreachable: ${cause?.hostname ?? cause?.address ?? 'unknown host'} (${cause?.code})`
                : `Upstream request failed: ${err.message}`,
            cause: err,
        })
    }
})