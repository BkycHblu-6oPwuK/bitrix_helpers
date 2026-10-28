import type { SectionData } from "~/types/iblock/page"

/**
 * Композабл для работы с фильтрацией секции
 * Предоставляет доступ к состоянию фильтров, выбранным значениям и методам управления
 * 
 * @returns Объект с данными фильтров и методами для работы с ними
 */
export function useSectionFilter<TFilterData = SectionData['filter']>() {
    const section = useSectionPage()
    const { filterData, selectedFilters, currentSortId, hasActiveFilters, selectedFilterCount, isDirty } = toRefs(section)
    const hasFilters = computed(() => !!filterData?.value?.items.length)
    const hasSorting = computed(() => !!filterData?.value?.sorting?.availableSorting?.length)

    return {
        /** Данные всех доступных фильтров */
        filterData: filterData as Ref<TFilterData>,
        /** Выбранные значения фильтров {controlId: value} */
        selectedFilters,
        /** ID текущей сортировки */
        currentSortId,
        /** Есть ли активные фильтры */
        hasActiveFilters,
        /** Есть ли фильтры для отображения */
        hasFilters,
        /** Есть ли доступные варианты сортировки */
        hasSorting,
        /** Количество выбранных фильтров */
        selectedFilterCount,
        /** Флаг, указывающий, были ли изменения в фильтрах */
        isDirty,

        /** Включить/выключить фильтр */
        toggleFilter: section.toggleFilter,
        /** Применить выбранные фильтры */
        applyFilters: section.applyFilters,
        /** Сбросить все фильтры */
        resetFilters: section.resetFilters,
        /** Применить сортировку */
        applySorting: section.applySorting,
        /** Загрузить указанную страницу */
        loadPage: section.loadPage,
    }
}
