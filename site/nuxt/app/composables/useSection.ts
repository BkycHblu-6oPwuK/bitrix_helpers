import { useSectionStore } from '~/stores/section'
import type { SectionData } from '~/types/iblock/section.ts'
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
export function useSection<T extends SectionData>(initialData?: T) {
  const store = useSectionStore()

  // Инициализируем store данными с сервера (только при первой загрузке)
  if (initialData) {
    store.initialize(initialData)
  }

  // Получаем реактивные ссылки на данные из store
  const { items, pagination, sectionList, filterData, isLoading, error } = storeToRefs(store)

  // Объединяем данные в единую структуру для удобного использования в компонентах
  const catalogData = computed<SectionData>(() => {
    return {
      filter: filterData.value,
      section: {
        items: items.value,
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
