import { hash } from 'ohash'
import { getApiPath } from './api-url-helpers'
import { getQuery, getRequestURL } from 'h3'
import type { EventHandler, H3Event } from 'h3'

type KeyPattern = string | RegExp

interface CompiledRule {
    rule: ApiCacheRule
    queryKeys: ReadonlyArray<KeyPattern>
    ignoreKeys: ReadonlyArray<KeyPattern>
    strict: boolean
    cached: EventHandler | null
}

interface ApiCacheRule {
    name: string
    match: (path: string, event: H3Event) => boolean
    cache?: boolean
    maxAge?: number
    swr?: boolean
    /** Параметры, влияющие на ответ: входят в ключ кеша */
    queryKeys?: ReadonlyArray<KeyPattern>
    /** Дополнительные игнорируемые параметры. Мержатся с DEFAULT_IGNORE_KEYS */
    ignoreKeys?: ReadonlyArray<KeyPattern>
    /** false - не подмешивать DEFAULT_IGNORE_KEYS (по умолчанию true) */
    inheritIgnoreKeys?: boolean
    /** true (по умолчанию) - параметр вне queryKeys и ignoreKeys отключает кеш для запроса */
    strict?: boolean
    varies?: string[]
}

/** Параметры, которые не влияют на ответ ни в одном маршруте (трекинг и т.п.) */
const DEFAULT_IGNORE_KEYS: ReadonlyArray<KeyPattern> = [
    /^utm_/,
    'yclid',
    'ysclid',
    'gclid',
    'fbclid',
    '_openstat',
]

const matchesAny = (patterns: ReadonlyArray<KeyPattern>, key: string) =>
    patterns.some((p) => (typeof p === 'string' ? p === key : p.test(key)))

/** Параметры для ключа или null, если запрос нельзя кешировать */
function pickQuery(event: H3Event, c: CompiledRule): Record<string, string> | null {
    const query = getQuery(event)
    const picked: Record<string, string> = {}

    for (const key of Object.keys(query).sort()) {
        // queryKeys проверяется первым, поэтому имеет приоритет над ignoreKeys
        if (matchesAny(c.queryKeys, key)) {
            const value = query[key]
            if (value !== undefined) {
                picked[key] = Array.isArray(value) ? value.join(',') : String(value)
            }
            continue
        }
        if (matchesAny(c.ignoreKeys, key)) continue
        if (c.strict) return null
    }
    return picked
}

function pathSlug(pathname: string) {
    try {
        return decodeURI(pathname).replace(/\W/g, '').slice(0, 16) || 'index'
    } catch {
        return '-'
    }
}

export function isUnder(path: string, prefix: string) {
    return path === prefix || path.startsWith(`${prefix}/`)
}

export function hasSegment(path: string, segment: string) {
    return path.split('/').includes(segment)
}

/**
 * Расширенное управление кешированием API-запросов, для простых запросов достаточно routeRules в nuxt.config.ts
 */
export function defineApiCachedHandler(handler: EventHandler, rules: ApiCacheRule[]) {
    const compiled: CompiledRule[] = rules.map((rule) => {
        const c: CompiledRule = {
            rule,
            queryKeys: rule.queryKeys ?? [],
            ignoreKeys: [
                ...((rule.inheritIgnoreKeys ?? true) ? DEFAULT_IGNORE_KEYS : []),
                ...(rule.ignoreKeys ?? []),
            ],
            strict: rule.strict ?? true,
            cached: null,
        }

        if (rule.cache !== false) {
            c.cached = defineCachedEventHandler(handler, {
                group: 'nitro/routes',
                name: rule.name,
                maxAge: rule.maxAge ?? 300,
                swr: rule.swr ?? true,
                varies: rule.varies,
                getKey: (event) => {
                    const { pathname } = getRequestURL(event)
                    const picked = pickQuery(event, c)
                    return `${pathSlug(pathname)}_${hash({ name: rule.name, pathname, picked })}`
                },
            })
        }
        return c
    })

    return defineEventHandler((event) => {
        if (useRuntimeConfig().dev || event.method !== 'GET') {
            return handler(event)
        }

        const path = getCleanPath(getApiPath(event) ?? '')
        const hit = compiled.find((c) => c.rule.match(path, event))

        if (!hit?.cached) return handler(event)
        if (pickQuery(event, hit) === null) return handler(event)

        return hit.cached(event)
    })
}