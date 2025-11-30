import { useSectionStore, type SectionData } from '~/stores/section'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

/**
 * Композабл для работы с секцией (каталог, статьи и т.д.)
 * Предоставляет доступ к данным секции, состоянию загрузки и методам управления
 * 
 * @template T - Тип элементов в списке (CatalogItemDTO, ArticleDTO и т.д.)
 * @param initialData - Начальные данные с сервера для инициализации store
 * @returns Объект с данными секции и методами управления
 */
export function useSection<T = any>(initialData?: SectionData<T>) {
  const store = useSectionStore()

  // Инициализируем store данными с сервера (только при первой загрузке)
  if (initialData) {
    store.initialize<T>(initialData)
  }

  // Получаем реактивные ссылки на данные из store
  const { items, pagination, sectionList, filterData, isLoading, error } = storeToRefs(store)

  // Объединяем данные в единую структуру для удобного использования в компонентах
  const catalogData = computed<SectionData<T> | null>(() => {
    if (!filterData.value) return null
    return {
      filter: filterData.value,
      section: {
        items: items.value as T[],
        pagination: pagination.value
      },
      sectionList: sectionList.value
    }
  })

  return {
    catalogData, // Объединенные данные секции
    isLoading, // Флаг загрузки
    error, // Объект ошибки
    loadCatalogPage: store.loadPage, // Метод загрузки страницы
    setAppendMode: store.setAppendMode, // Установка режима дозагрузки
  }
}
