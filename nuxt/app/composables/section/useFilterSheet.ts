import type { InjectionKey, Ref } from 'vue'
import type { FilterItemDTO } from '~/types/iblock/page'

export interface FilterSheetContext {
    /** Код фильтра, значения которого сейчас показаны (null = список фильтров) */
    activeCode: Ref<string | null>
    /** CSS-селектор контейнера листа, куда FilterItem телепортирует значения */
    contentTarget: Ref<HTMLElement | null>
    /** Запросить показ значений конкретного фильтра внутри общего листа */
    requestOpen: (filterItem: FilterItemDTO) => void
}

export const FILTER_SHEET_KEY: InjectionKey<FilterSheetContext> = Symbol('filter-sheet')

/** Потребитель: получить контекст общего листа (null, если провайдера нет) */
export function useFilterSheet(): FilterSheetContext | null {
    return inject(FILTER_SHEET_KEY, null)
}

/** Провайдер: зарегистрировать контекст общего листа */
export function provideFilterSheet(ctx: FilterSheetContext) {
    provide(FILTER_SHEET_KEY, ctx)
}
