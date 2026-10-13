/**
 * Композабл для работы с пагинацией
 * Предоставляет информацию о текущей странице, общем количестве страниц
 * и методы для генерации URL различных страниц
 * 
 * @returns Объект с данными пагинации и вспомогательными методами
 */
export function usePagination() {
    const store = useSectionStore()
    const { pagination, currentPage, pageCount, hasMore, hasPrevious } = storeToRefs(store)

    return {
        /** Полный объект пагинации с сервера */
        pagination,
        /** Номер текущей страницы */
        currentPage,
        /** Общее количество страниц */
        pageCount,
        /** Есть ли следующая страница */
        hasMore,
        /** Есть ли предыдущая страница */
        hasPrevious,
        /** Установить текущую страницу */
        goToPage: store.goToPage,
        /** Показать следующую страницу */
        showMore: store.showMore,
    }
}
