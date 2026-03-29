
/**
 * Форматирует дату в заданный формат.
 *
 * @param value Дата в виде строки или объекта Date.
 * @param format Формат вывода (например, 'YYYY-MM-DD').
 * @returns Отформатированная дата.
 *
 * @example
 * formatDate('2024-06-15T12:34:56Z', 'YYYY-MM-DD') // '2024-06-15'
 * formatDate(new Date(2024, 5, 15), 'DD/MM/YYYY') // '15/06/2024'
 */
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
