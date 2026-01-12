import { defineStore } from 'pinia'
import type { SectionDTO } from '~/types/iblock';
import type { PageData } from '~/types/iblock/content'
import type { FilterDTO, SectionData } from '~/types/iblock/page';
import type { PaginationDTO } from '~/types/pagination'

const getSelectedFilterHash = (selectedFilters: Record<string, string>) => {
  return JSON.stringify(JSON.parse(JSON.stringify(selectedFilters)));
}

/**
 * Универсальное хранилище для работы с секциями, фильтрацией и пагинацией
 * Подходит для каталогов товаров, списков статей и любых других разделов с фильтрами
 */
export const useSectionStore = defineStore('section', {
  state: () => ({
    // Данные фильтра (поля, доступные значения, настройки сортировки)
    filterData: null as FilterDTO | null,

    // Текущие выбранные значения фильтров {controlId: htmlValue}
    selectedFilters: {} as Record<string, string>,

    // ID текущей выбранной сортировки
    currentSortId: null as string | null,

    // Массив элементов (товары, статьи и т.д.)
    items: [] as any[],

    // Данные пагинации (текущая страница, общее кол-во страниц и т.д.)
    pagination: null as PaginationDTO | null,

    // Путь для хлебных крошек (объект с ключами - ID разделов)
    path: null as Record<string, SectionDTO> | null,

    // Список дочерних разделов для отображения навигации
    sectionList: null as any[] | null,

    // Индикатор загрузки данных
    isLoading: false,

    // Объект ошибки, если произошла
    error: null as Error | null,

    // Флаг режима добавления элементов (true = дозагрузка, false = замена)
    nextAppendMode: false,

    // Хеш выбранных фильтров для отслеживания изменений
    oldSelectedFilterHash: '{}' as string,

    // Флаг для пропуска следующего срабатывания watch при программной навигации
    skipNextRouteWatch: false,

    apiUrl: null as URL | null,

    // Дополнительные данные для расширенных типов
    additionalData: {} as Record<string, any>,
  }),

  getters: {
    // Проверка наличия активных фильтров
    hasActiveFilters: (state) => Object.keys(state.selectedFilters).length > 0,

    // Количество выбранных фильтров
    selectedFilterCount: (state) => Object.keys(state.selectedFilters).length,

    // Номер текущей страницы
    currentPage: (state) => state.pagination?.currentPage || 1,

    // Общее количество страниц
    pageCount: (state) => state.pagination?.pageCount || 1,

    // Есть ли следующая страница
    hasMore: (state) => state.pagination ? state.pagination.currentPage < state.pagination.pageCount : false,

    // Есть ли предыдущая страница
    hasPrevious: (state) => state.pagination ? state.pagination.currentPage > 1 : false,
  },

  actions: {
    /**
     * Инициализация store с данными с сервера
     * Вызывается при первой загрузке страницы
     */
    initialize<T extends SectionData>(sectionData: T) {
      this.filterData = sectionData.filter
      this.items = sectionData.section.items
      this.pagination = sectionData.section.pagination
      this.sectionList = sectionData.sectionList

      // Сохраняем дополнительные поля (все кроме базовых полей SectionData)
      const { filter, section, sectionList, ...additional } = sectionData as any
      this.additionalData = additional

      this.initializeFiltersFromChecked()
    },

    setApiUrl(url: URL | string) {
      if (typeof url === 'string') {
        url = new URL(url, window.location.origin);
      }
      this.apiUrl = url;
    },

    /**
     * Восстановление состояния фильтров из URL параметров
     * Читает query параметры и устанавливает соответствующие фильтры
     */
    initializeFiltersFromChecked() {
      if (!this.filterData || typeof window === 'undefined') return

      let selectedFilters: Record<string, string> = {}

      for (const item of Object.values(this.filterData.items)) {
        for (const val of Object.values(item?.values || {})) {
          if ((val as any).checked) {
            selectedFilters[(val as any).controlId] = (val as any).htmlValue;
          }
        }
      }

      this.selectedFilters = selectedFilters

      // Включаем в хеш все параметры из URL, чтобы отслеживать изменения страницы при навигации
      const urlParams = new URLSearchParams(window.location.search)
      if (Object.keys(this.selectedFilters).length > 0 && !urlParams.has('ajax')) {
        urlParams.set('ajax', 'y')
      }
      const allParams = {
        ...selectedFilters,
        ...Object.fromEntries(urlParams)
      }
      this.oldSelectedFilterHash = getSelectedFilterHash(allParams);
      this.currentSortId = this.filterData.sorting.currentSortId
    },

    /**
     * Переключение значения фильтра (включить/выключить)
     * @param controlId - ID контрола фильтра
     * @param value - Значение фильтра
     */
    toggleFilter(controlId: string, value: string) {
      if (this.selectedFilters[controlId]) {
        // Убираем фильтр, если он уже выбран
        const { [controlId]: _, ...rest } = this.selectedFilters
        this.selectedFilters = rest
      } else {
        // Добавляем фильтр
        this.selectedFilters = {
          ...this.selectedFilters,
          [controlId]: value
        }
      }
    },

    /**
     * Очистка всех выбранных фильтров
     */
    clearFilters() {
      this.selectedFilters = {}
    },

    /**
     * Установка текущей сортировки
     * @param sortId - ID варианта сортировки
     */
    setSorting(sortId: string) {
      this.currentSortId = sortId
    },

    /**
     * Построение URL с учетом выбранных фильтров и сортировки
     * @returns URL объект с параметрами фильтрации
     */
    buildFilterUrl(): URL {
      const url = this.apiUrl
      if (!url) {
        throw new Error('API URL is not set');
      }
      const params = new URLSearchParams(url.search)
      const selectedFilters = Object.entries(this.selectedFilters)

      // Добавляем все выбранные фильтры в параметры
      selectedFilters.forEach(([controlId, value]) => {
        params.set(controlId, value)
      })

      // Добавляем сортировку, если она отличается от дефолтной
      if (this.currentSortId && this.currentSortId !== this.filterData?.sorting.defaultSortId) {
        params.set(this.filterData?.sorting.requestParam || 'sort', this.currentSortId)
      }

      // Помечаем запрос как AJAX для серверной обработки
      // Добавляем флаг ajax только если в параметрах есть другие параметры
      if (Object.keys(this.selectedFilters).length > 0 && !params.has('ajax')) {
        params.set('ajax', 'y')
      }

      url.search = params.toString()
      return url
    },

    /**
     * Получение URL для конкретной страницы пагинации
     * @param page - Номер страницы
     * @returns URL объект или null если пагинация недоступна
     */
    getPageUrl(page: number): URL | null {
      if (!this.pagination || typeof window === 'undefined') return null
      const url = this.apiUrl
      if (!url) {
        throw new Error('API URL is not set');
      }
      const params = new URLSearchParams(url.search)

      // Для первой страницы удаляем параметр пагинации из URL
      if (page === 1) {
        params.delete(this.pagination.paginationUrlParam)
      } else {
        params.set(this.pagination.paginationUrlParam, page.toString())
      }
      url.search = params.toString()
      return url
    },

    /**
     * Установка режима загрузки следующей порции данных
     * @param append - true = дозагрузка элементов, false = замена списка
     */
    setAppendMode(append: boolean) {
      this.nextAppendMode = append
    },

    /**
     * Загрузка страницы с данными (применение фильтров или переход на другую страницу)
     * @param url - URL для загрузки
     * @param options - Настройки загрузки
     *   - append: добавить элементы к существующим (true) или заменить (false)
     *   - navigateFilter: навигация на URL фильтра после загрузки (по умолчанию false)
     *   - pushState: добавлять в историю url
     */
    async loadPage<T extends SectionData>(url: URL, options?: { append?: boolean; navigateFilter?: boolean; pushState?: boolean; isOnMounted?: boolean }) {
      this.isLoading = true
      this.error = null

      try {
        const path = url.pathname
        const query = Object.fromEntries(url.searchParams);
        
        const oldSelectedFilterHash = getSelectedFilterHash({
          ...this.selectedFilters,
          ...query
        });

        if (!options?.isOnMounted && oldSelectedFilterHash === this.oldSelectedFilterHash) {
          return;
        }

        this.oldSelectedFilterHash = oldSelectedFilterHash;

        // Определяем режим загрузки: дозагрузка или замена
        const shouldAppend = options?.append || false
        
        // useApi (useAsyncData) нельзя безопасно вызывать внутри Pinia actions
        // поэтому используем прямой fetch-варинт useApiFetch, который возвращает результат сразу
        const res = await useApiFetch<PageData<T>>(path, { query })
        if (!res?.data?.page) {
          throw new Error('Invalid response format')
        }

        const newPageData = res.data.page

        if (shouldAppend) {
          // Режим "показать еще" - добавляем элементы к существующим
          this.items = [...this.items, ...(newPageData.section?.items || [])]
          this.pagination = newPageData.section?.pagination || null
        } else {
          // Режим замены - обновляем всё (новая страница или применение фильтров)
          this.items = newPageData.section?.items || []
          this.pagination = newPageData.section?.pagination || null
          this.sectionList = newPageData.sectionList
          this.filterData = newPageData.filter

          // Обновляем дополнительные поля
          const { filter, section, sectionList, ...additional } = newPageData as any
          this.additionalData = additional
        }

        this.nextAppendMode = false

        if (options?.pushState) {
          let newUrl = ''
          if (options?.navigateFilter && res.data.page.filter?.filterUrl) {
            newUrl = res.data.page.filter.filterUrl // чпу фильтра
          } else {
            if (url === this.apiUrl) {
              newUrl = window.location.pathname + url.search // текущий URL с параметрами
            } else {
              newUrl = url.pathname + url.search
            }
            // полная ссылка с параметрами
          }

          // Добавляем в историю только если URL изменился
          if (window.location.pathname + window.location.search !== newUrl) {
            window.history.pushState({ timestamp: Date.now() }, '', newUrl)
          }
        }

        return res
      } catch (err) {
        this.error = err as Error
        throw err
      } finally {
        this.isLoading = false
      }
    },
  },
})
