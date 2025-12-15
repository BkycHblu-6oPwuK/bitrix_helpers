export function formatDate(
    value: string | Date,
    format: string
): string {
    const date = value instanceof Date ? value : new Date(value)

    if (isNaN(date.getTime())) return ''

    const pad = (n: number) => String(n).padStart(2, '0')

    const map: Record<string, string> = {
        YYYY: String(date.getFullYear()),
        MM: pad(date.getMonth() + 1),
        DD: pad(date.getDate()),
    }

    return format.replace(/YYYY|MM|DD/g, token => map[token])
}
