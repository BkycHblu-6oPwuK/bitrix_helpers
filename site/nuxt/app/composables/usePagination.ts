import { ref, computed } from 'vue'
import type { PaginationDTO } from '~/types/pagination'

export function usePagination(initialPagination?: PaginationDTO) {
  const pagination = ref<PaginationDTO | null>(initialPagination || null)
  const setPagination = (data: PaginationDTO) => {
    pagination.value = data
  }
  const currentPage = computed(() => pagination.value?.currentPage || 1)
  const pageCount = computed(() => pagination.value?.pageCount || 1)
  const hasMore = computed(() => {
    if (!pagination.value) return false
    return pagination.value.currentPage < pagination.value.pageCount
  })
  const hasPrevious = computed(() => {
    if (!pagination.value) return false
    return pagination.value.currentPage > 1
  })

  const getPageUrl = (page: number): string | null => {
    if (!pagination.value) return null
    const url = new URL(window.location.href)
    const params = new URLSearchParams(url.search)
    
    if (page === 1) {
      params.delete(pagination.value.paginationUrlParam)
    } else {
      params.set(pagination.value.paginationUrlParam, page.toString())
    }
    
    url.search = params.toString()
    return url.pathname + url.search
  }

  const nextPageUrl = computed(() => {
    if (!hasMore.value) return null
    return getPageUrl(currentPage.value + 1)
  })

  const previousPageUrl = computed(() => {
    if (!hasPrevious.value) return null
    return getPageUrl(currentPage.value - 1)
  })

  return {
    pagination,
    currentPage,
    pageCount,
    hasMore,
    hasPrevious,
    nextPageUrl,
    previousPageUrl,
    setPagination,
    getPageUrl,
  }
}
