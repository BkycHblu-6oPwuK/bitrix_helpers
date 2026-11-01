const EXCLUDED_HEADERS = new Set([
    'csrf-token',
    'host',
    'connection',
    'content-length',
    'accept-encoding',
    'if-none-match',
    'if-modified-since'
])

const EXCLUDED_COOKIES = new Set([
    'PHPSESSID',
    'BITRIX_SM_SOUND_LOGIN_PLAYED',
    'CSRF'
])

function filterCookie(value: string): string {
    return value
        .split(';')
        .map(cookie => cookie.trim())
        .filter(cookie => !isExcludedCookie(cookie))
        .join('; ')
}

export function isExcludedCookie(value: string): boolean {
    const name = value.trim().split('=', 1)[0]?.toUpperCase()
    if (!name) {
        return false
    }

    return [...EXCLUDED_COOKIES].some(cookie => {
        const excluded = cookie.toUpperCase()

        return name === excluded || name.startsWith(`${excluded}_`)
    })
}

export function filterRequestHeaders(
    headers: Record<string, string | undefined>,
): Record<string, string> {
    const result: Record<string, string> = {}

    for (const [key, value] of Object.entries(headers)) {
        const name = key.toLowerCase()

        if (typeof value !== 'string') continue
        if (EXCLUDED_HEADERS.has(name)) continue

        if (name === 'cookie') {
            const filtered = filterCookie(value)

            if (filtered) {
                result[name] = filtered
            }

            continue
        }

        result[name] = value
    }

    return result
}