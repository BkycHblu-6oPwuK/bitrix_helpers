import { getRequestHeaders, getRequestHeader } from 'h3' // или откуда у тебя эти функции

export default defineEventHandler(async (event) => {
  if (process.env.NODE_ENV !== 'development') return

  const path = event.path
  if (!/^\/(upload|api|local|bitrix)(\/|$)/.test(path)) return

  const target = `http://nginx${path}`

  const headers = {
    ...getRequestHeaders(event),               // остальные заголовки клиента
    'x-forwarded-host': getRequestHeader(event, 'host') || undefined,
    'x-real-ip': getRequestHeader(event, 'x-real-ip') || event.node.req.socket.remoteAddress,
  }

  const response = await proxyRequest(event, target, {
    fetch,
    headers,
  })

  return sendProxy(event, response)
})
