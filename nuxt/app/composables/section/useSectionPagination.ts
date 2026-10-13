import type { PaginationDTO } from '~/types/pagination'

export function useSectionPagination(pagination: Ref<PaginationDTO | null>) {
    const currentPage = computed(() => pagination.value?.currentPage || 1)
    const pageCount = computed(() => pagination.value?.pageCount || 1)

    const hasMore = computed(() =>
        pagination.value ? pagination.value.currentPage < pagination.value.pageCount : false
    )
    const hasPrevious = computed(() =>
        pagination.value ? pagination.value.currentPage > 1 : false
    )

    return {
        /** Текущая страница */
        currentPage,
        /** Общее количество страниц */
        pageCount,
        /** Есть ли следующая страница */
        hasMore,
        /** Есть ли предыдущая страница */
        hasPrevious
    }
}
