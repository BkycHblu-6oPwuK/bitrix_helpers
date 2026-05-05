import { createError } from 'h3'
import { fetchProxy } from '../../utils/api'
import { getUpstreamUrl, getApiPath } from './utils/api'

export default defineEventHandler((event) => {
    const path = getApiPath(event)
    if (!path) {
        throw createError({
            statusCode: 400,
            statusMessage: 'API path is required',
        })
    }

    return fetchProxy(event, getUpstreamUrl(path))
})