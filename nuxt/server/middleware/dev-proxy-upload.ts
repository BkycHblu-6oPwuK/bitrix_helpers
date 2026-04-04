import { getRequestHeaders, getRequestHeader } from 'h3'

export default defineEventHandler(async (event) => {
    if (process.env.NODE_ENV !== 'development') return
    const path = event.path
    if (!/^\/(upload|local|bitrix)(\/|$)/.test(path)) return

    const domain = new URL(process.env.NUXT_API_BASE_SERVER || 'http://localhost').origin
    const target = domain + path

    const headers = {
        ...getRequestHeaders(event),
        'x-forwarded-host': getRequestHeader(event, 'host') || undefined,
        'x-real-ip': getRequestHeader(event, 'x-real-ip') || event.node.req.socket.remoteAddress,
    }

    const response = await proxyRequest(event, target, {
        fetch,
        headers,
    })

    return sendProxy(event, response)
})
