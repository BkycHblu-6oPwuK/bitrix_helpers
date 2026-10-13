/**
 * Данные секции: элементы, хлебные крошки, состояние загрузки.
 * @template TItem - тип элементов секции
 * @template TAdditional - тип остальных полей ответа
 */
export function useSection<TItem = unknown, TAdditional extends object = Record<string, unknown>>() {
    const store = useSectionStore()
    const { items, path, additionalData, seo, isLoading, error } = storeToRefs(store)

    return {
        /** Элементы секции */
        items: items as Ref<TItem>,
        /** Хлебные крошки секции */
        path,
        /** Дополнительные данные секции, неописанные в типах ответа */
        additional: additionalData as Ref<TAdditional>,
        /** SEO данные секции */
        seo,
        /** Состояние загрузки секции */
        isLoading,
        /** Ошибка загрузки секции */
        error,
    }
}