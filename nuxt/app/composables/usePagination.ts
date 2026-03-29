import { useSectionStore } from '~/stores/section'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

/**
 * Композабл для работы с пагинацией
 * Предоставляет информацию о текущей странице, общем количестве страниц
 * и методы для генерации URL различных страниц
 * 
 * @returns Объект с данными пагинации и вспомогательными методами
 */
export function usePagination() {
  const store = useSectionStore()

  // Получаем реактивные данные из store
  const { pagination } = storeToRefs(store)
  const { currentPage, pageCount, hasMore, hasPrevious } = storeToRefs(store)

  // URL следующей страницы (или null если это последняя страница)
  const nextPageUrl = computed(() => {
    if (!hasMore.value) return null
    return store.getPageUrl(currentPage.value + 1)
  })

  // URL предыдущей страницы (или null если это первая страница)
  const previousPageUrl = computed(() => {
    if (!hasPrevious.value) return null
    return store.getPageUrl(currentPage.value - 1)
  })

  return {
    pagination, // Полный объект пагинации с сервера
    currentPage, // Номер текущей страницы
    pageCount, // Общее количество страниц
    hasMore, // Есть ли следующая страница
    hasPrevious, // Есть ли предыдущая страница
    nextPageUrl, // URL следующей страницы
    previousPageUrl, // URL предыдущей страницы
    getPageUrl: store.getPageUrl, // Метод получения URL любой страницы
  }
}
