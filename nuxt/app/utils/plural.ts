/**
 * Возвращает правильную форму слова в зависимости от числа.
 * @param variants - массив из трех форм слова: [единственное число, несколько, множественное число]
 * @returns правильная форма слова в зависимости от числа
 */
export function plural(count: number, variants: [string, string, string]): string {
  const num = count % 100
  const tens = Math.floor(num / 10)

  if (tens === 1) return variants[2]
  
  const ones = num % 10
  if (ones === 1) return variants[0]
  if (ones >= 2 && ones <= 4) return variants[1]
  
  return variants[2]
}
