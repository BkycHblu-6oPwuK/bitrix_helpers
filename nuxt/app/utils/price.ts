/**
 * Форматирует цену в соответствии с локалью и валютой.
 *
 * @param price - Сумма для форматирования.
 * @param currency - Валюта (например, 'RUB', 'USD').
 * @returns Отформатированная строка цены.
 */
export function formatPrice(price: number, currency: string): string {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(price);
}