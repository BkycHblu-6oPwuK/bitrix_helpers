import { createError, getRouterParam } from 'h3'
import { proxyApiRequest } from '../../utils/api'

export default defineEventHandler((event) => {
    const path = getRouterParam(event, 'path')
    if (!path) {
        throw createError({
            statusCode: 400,
            statusMessage: 'API path is required',
        })
    }

    return proxyApiRequest(event, path)
})