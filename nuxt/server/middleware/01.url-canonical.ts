/**
 * Каноникализация URL для GET/HEAD, всегда один 301:
 * - www → без www, http → https (для канонического хоста);
 * - схлопывает слэши, приводит путь к нижнему регистру;
 * - у страниц добавляет завершающий слэш (api и файлы с расширением не трогаем);
 * - /index.php, /home.html и т.п. → /
 */
import type { H3Event } from 'h3'

const SKIP_PREFIXES = ['/_nuxt/', '/__nuxt']
const LEGACY_ROOT = /^\/(?:index|home)\.(?:php|html?)\/?$/i
const HAS_EXTENSION = /\.[^/]+$/

/** Первое значение из заголовка прокси ("a, b" → "a") */
function forwarded(event: H3Event, name: string): string | undefined {
    return getRequestHeader(event, name)?.split(',')[0]?.trim() || undefined
}

function parseUrl(value: string | undefined): URL | undefined {
    try {
        return value ? new URL(value) : undefined
    } catch {
        return undefined
    }
}

function canonicalize(pathname: string): string {
    // \ тоже схлопываем: браузер воспринимает "/\evil.com" как "//evil.com"
    let path = pathname.replace(/[\\/]+/g, '/')
    if (LEGACY_ROOT.test(path)) return '/'

    // нижний регистр, но %XX остаются в верхнем: иначе /%D0 и /%d0 дают лишний редирект
    path = path.replace(/%[0-9a-f]{2}|[^%]+/gi, (part) =>
        part[0] === '%' ? part.toUpperCase() : part.toLowerCase(),
    )
    if (!path.startsWith('/')) path = `/${path}`

    const isPage = !path.startsWith('/api/') && !HAS_EXTENSION.test(path)
    return isPage && !path.endsWith('/') ? `${path}/` : path
}

export default defineEventHandler((event) => {
    if (event.method !== 'GET' && event.method !== 'HEAD') return

    // сырой путь: без декодирования и нормализации, как пришёл от клиента
    const i = event.path.indexOf('?')
    const pathname = i === -1 ? event.path : event.path.slice(0, i)
    const search = i === -1 ? '' : event.path.slice(i)

    if (SKIP_PREFIXES.some((prefix) => pathname.startsWith(prefix))) return

    const path = canonicalize(pathname)
    const target = `${path}${search}`

    const host = (forwarded(event, 'x-forwarded-host') || getRequestHost(event)).toLowerCase()
    const proto = forwarded(event, 'x-forwarded-proto') || getRequestProtocol(event)

    const config = useRuntimeConfig(event)
    const canonical = parseUrl(config.effectiveHost || config.public?.baseUrl)

    const upgradeToHttps =
        proto === 'http' && canonical?.protocol === 'https:' && host === canonical.host

    if (host.startsWith('www.') || upgradeToHttps) {
        return sendRedirect(event, `https://${host.replace(/^www\./, '')}${target}`, 301)
    }

    if (path !== pathname) {
        return sendRedirect(event, target, 301)
    }
})