// nuxt брезает ключи до 16 символов
const CACHE_GROUPS = {
    catalog: ['apiv1catalog', 'apiv1product'],
    articles: [
        'apiv1articles',
        'apiv1article'
    ],
    other: [
        'apiv1getmainpage',
        'apiv1getmenu',
        'apiv1reviews'
    ],
} as const

export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig(event)

    if (config.isDev) {
        return { success: true }
    }

    const authorization = getHeader(event, 'authorization')

    if (authorization !== `Bearer ${config.secretToken}`) {
        throw createError({
            statusCode: 401,
            statusMessage: 'Unauthorized',
        })
    }

    const body = await readBody(event)

    const storage = useStorage()
    const keys = await storage.getKeys('cache:nitro:routes:')

    let filteredKeys: string[]

    if (body?.type === 'all') {
        filteredKeys = keys
    } else if (body?.type in CACHE_GROUPS) {
        const prefixes = CACHE_GROUPS[body.type as keyof typeof CACHE_GROUPS].map(prefix => prefix.slice(0, 16)) // обрезаем до 16 символов, так как nuxt обрезает ключи, в разных версиях перепроверить корректность

        filteredKeys = keys.filter(key =>
            prefixes.some(prefix => key.includes(prefix)),
        )
    } else {
        throw createError({
            statusCode: 400,
            statusMessage: 'Invalid cache invalidation type',
        })
    }

    await Promise.all(
        filteredKeys.map(key => storage.removeItem(key)),
    )

    return {
        success: true,
        type: body.type,
        invalidated: filteredKeys.length,
        timestamp: Date.now(),
    }
})