/**
 * Композабл для работы с пагинацией
 * Предоставляет информацию о текущей странице, общем количестве страниц
 * и методы для генерации URL различных страниц
 * 
 * @returns Объект с данными пагинации и вспомогательными методами
 */
export function usePagination() {
    const section = useSectionPage()
    const { pagination, currentPage, pageCount, hasMore, hasPrevious } = toRefs(section)

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
        goToPage: section.goToPage,
        /** Показать следующую страницу */
        showMore: section.showMore,
        /** Загрузить указанную страницу */
        loadPage: section.loadPage,
    }
}
