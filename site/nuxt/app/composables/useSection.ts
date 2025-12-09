import { useSectionStore } from '~/stores/section'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'
import type { SectionData } from '~/types/iblock/page'

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

  // Отслеживаем навигацию по истории браузера (кнопки Назад/Вперед)
  if (process.client) {
    onMounted(() => {
      const handlePopState = async () => {
        if(!apiUrl.value) {
          throw new Error('API URL is not set in section store');
        }
        apiUrl.value.search = window.location.search;
        await store.loadPage<T>(apiUrl.value)
      }
      
      window.addEventListener('popstate', handlePopState)
      
      onUnmounted(() => {
        window.removeEventListener('popstate', handlePopState)
      })
    })
  }

  // Получаем реактивные ссылки на данные из store
  const { items, pagination, selectedFilters, sectionList, path, filterData, isLoading, error, apiUrl } = storeToRefs(store)

  // Объединяем данные в единую структуру для удобного использования в компонентах
  const catalogData = computed<SectionData>(() => {
    return {
      filter: filterData.value,
      section: {
        items: items.value,
        pagination: pagination.value,
        path: path.value
      },
      sectionList: sectionList.value
    }
  })

  return {
    catalogData, // Объединенные данные секции
    isLoading, // Флаг загрузки
    error, // Объект ошибки
    selectedFilters, // Выбранные фильтры
    loadCatalogPage: store.loadPage, // Метод загрузки страницы
    setAppendMode: store.setAppendMode, // Установка режима дозагрузки
    setApiUrl: store.setApiUrl, // Установка API URL
  }
}
