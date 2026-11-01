import { createError } from 'h3'
import { fetchProxy } from '../../utils/api'

export default defineEventHandler(async (event) => {
    try {
        return await fetchProxy(event)
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