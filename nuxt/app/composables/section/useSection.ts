/**
 * Данные секции: элементы, хлебные крошки, состояние загрузки.
 * @template TItem - тип элементов секции
 * @template TAdditional - тип остальных полей ответа
 */
export function useSection<TItem = unknown, TAdditional extends object = Record<string, unknown>>() {
    const section = useSectionPage()
    const { items, additionalData, isLoading, error } = toRefs(section)

    return {
        /** Элементы секции */
        items: items as Ref<TItem>,
        /** Дополнительные данные секции, неописанные в типах ответа */
        additional: additionalData as Ref<TAdditional>,
        /** Состояние загрузки секции */
        isLoading,
        /** Ошибка загрузки секции */
        error,
        /** Загрузить указанную страницу */
        loadPage: section.loadPage,
    }
}