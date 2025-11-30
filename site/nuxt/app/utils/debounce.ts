/**
 * Утилита debounce для задержки выполнения функции
 * Полезно для обработчиков ввода, скролла, resize и т.д.
 * 
 * @template T - Тип функции
 * @param fn - Функция для выполнения с задержкой
 * @param delay - Задержка в миллисекундах (по умолчанию 300ms)
 * @returns Обернутая функция с debounce
 * 
 * @example
 * const handleSearch = debounce((query: string) => {
 *   console.log('Searching for:', query)
 * }, 500)
 * 
 * // Вызовы в течение 500ms будут игнорироваться,
 * // выполнится только последний
 * handleSearch('abc')
 * handleSearch('abcd')
 * handleSearch('abcde') // Выполнится только этот
 */
export function debounce<T extends (...args: any[]) => void>(
  fn: T,
  delay = 300
): (...args: Parameters<T>) => void {
  let timeout: ReturnType<typeof setTimeout> | undefined

  return (...args: Parameters<T>): void => {
    // Отменяем предыдущий таймер, если он есть
    if (timeout) clearTimeout(timeout)
    
    // Устанавливаем новый таймер
    timeout = setTimeout(() => fn(...args), delay)
  }
}
