import type { FilterDTO, FilterValueItemDTO } from '~/types/iblock/page'
import type { PaginationDTO } from '~/types/pagination'

/**
 * Тип для хранения значений фильтров в виде объекта, где ключ — controlId, а значение — htmlValue
 */
type FilterValues = Record<string, string>

/**
 * Хук для работы с фильтрами и сортировкой раздела
 */
export function useSectionFilters(
    filterData: Ref<FilterDTO | null>,
    pagination: Ref<PaginationDTO | null>,
    baseUrl: Ref<string | null>,
) {
    const selectedFilters = ref<FilterValues>({})
    const appliedFilters = ref<FilterValues>({})
    const currentSortId = ref<string | null>(null)
    const appliedSortId = ref<string | null>(null)
    const selectedFilterCount = computed(() => Object.keys(selectedFilters.value).length)
    const hasActiveFilters = computed(() => selectedFilterCount.value > 0)

    const stateKey = (f: FilterValues, sortId: string | null) =>
        JSON.stringify([
            Object.entries(f).sort(([a], [b]) => a.localeCompare(b)),
            sortId ?? filterData.value?.sorting.defaultSortId ?? null,
        ])

    const isDirty = computed(() =>
        stateKey(selectedFilters.value, currentSortId.value) !== stateKey(appliedFilters.value, appliedSortId.value))

    const initializeFiltersFromChecked = () => {
        const next: FilterValues = {}
        for (const item of Object.values(filterData.value?.items ?? {})) {
            for (const val of Object.values<FilterValueItemDTO>(item?.values ?? {})) {
                if (val.checked) next[val.controlId] = val.htmlValue
            }
        }
        selectedFilters.value = next
        appliedFilters.value = { ...next }
        currentSortId.value = appliedSortId.value = filterData.value?.sorting?.currentSortId ?? null
    }
    const buildUrl = (filters: FilterValues, sortId: string | null, page: number): URL => {
        const url = new URL(baseUrl.value!)
        const params = new URLSearchParams(url.search)
        console.log('Building URL with filters:', url, baseUrl.value)

        Object.entries(filters).forEach(([id, v]) => params.set(id, v))

        const sorting = filterData.value?.sorting
        if (sorting && sortId && sortId !== sorting.defaultSortId) params.set(sorting.requestParam || 'sort', sortId)

        const pageParam = pagination.value?.paginationUrlParam
        if (pageParam && page > 1) params.set(pageParam, String(page))

        url.search = params.toString()
        return url
    }

    const buildFilterUrl = () => buildUrl(selectedFilters.value, currentSortId.value, 1)
    const buildPageUrl = (page: number) => buildUrl(appliedFilters.value, appliedSortId.value, page)
    const toggleFilter = (controlId: string, value: string) => {
        if (selectedFilters.value[controlId]) {
            const { [controlId]: _, ...rest } = selectedFilters.value
            selectedFilters.value = rest
        } else {
            selectedFilters.value = { ...selectedFilters.value, [controlId]: value }
        }
    }
    const clearFilters = () => { selectedFilters.value = {} }
    const setSorting = (sortId: string) => { currentSortId.value = sortId }

    return {
        /** Выбранные фильтры, которые пользователь изменил, но ещё не применил */
        selectedFilters,
        /** Фильтры, которые уже применены */
        appliedFilters,
        /** Текущий выбранный способ сортировки */
        currentSortId,
        /** Есть ли активные фильтры */
        hasActiveFilters,
        /** Количество выбранных фильтров */
        selectedFilterCount,
        /** Есть ли неприменённые изменения */
        isDirty,
        /** Инициализация фильтров на основе отмеченных значений */
        initializeFiltersFromChecked,
        /** Переключение состояния фильтра */
        toggleFilter,
        /** Очистка всех выбранных фильтров */
        clearFilters,
        /** Установка способа сортировки */
        setSorting,
        /** Построение URL с выбранными фильтрами, всегда с первой страницы */
        buildFilterUrl,
        /** Построение URL для указанной страницы с применёнными фильтрами */
        buildPageUrl,
    }
}