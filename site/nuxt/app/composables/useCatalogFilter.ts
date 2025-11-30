import { ref, computed } from 'vue'
import type { CatalogFilterDTO, CatalogFilterItemDTO, CatalogFilterValueItemDTO } from '~/types/iblock/catalog'

export interface FilterState {
  selectedFilters: Record<string, string>
  currentSortId: string | null
}

export function useCatalogFilter(initialFilter?: CatalogFilterDTO) {
  const filterData = ref<CatalogFilterDTO | null>(initialFilter || null)
  const selectedFilters = ref<Record<string, string>>({})
  const currentSortId = ref<string | null>(null)

  const initializeSelectedFilters = () => {
    if (!filterData.value || !Array.isArray(filterData.value.items)) return
    const selected: Record<string, string> = {}
    
    filterData.value.items.forEach((item: CatalogFilterItemDTO) => {
      if (!Array.isArray(item.values)) return
      item.values.forEach((value: CatalogFilterValueItemDTO) => {
        if (value.checked) {
          selected[value.controlId] = value.htmlValue
        }
      })
    })
    
    selectedFilters.value = selected
    currentSortId.value = filterData.value.sorting.currentSortId
  }

  const setFilterData = (data: CatalogFilterDTO) => {
    filterData.value = data
    initializeSelectedFilters()
  }

  const toggleFilter = (controlId: string, htmlValue: string) => {
    if (selectedFilters.value[controlId]) {
      delete selectedFilters.value[controlId]
    } else {
      selectedFilters.value[controlId] = htmlValue
    }
  }

  const clearFilters = () => {
    selectedFilters.value = {}
  }

  const setSorting = (sortId: string) => {
    currentSortId.value = sortId
  }

  const buildFilterUrl = (baseUrl: string): URL => {
    const url = new URL(baseUrl)
    const params = new URLSearchParams(url.search)
    
    Object.entries(selectedFilters.value).forEach(([controlId, value]) => {
      params.set(controlId, value)
    })
    
    if (currentSortId.value && currentSortId.value !== filterData.value?.sorting.defaultSortId) {
      params.set(filterData.value?.sorting.requestParam || 'sort', currentSortId.value)
    }
    
    url.search = params.toString()
    return url
  }

  const filterUrl = computed(() => {
    if (!filterData.value) return null
    return buildFilterUrl(filterData.value.filterUrl)
  })

  const clearUrl = computed(() => {
    if (!filterData.value) return null
    return new URL(filterData.value.clearUrl)
  })

  const hasActiveFilters = computed(() => {
    return Object.keys(selectedFilters.value).length > 0
  })

  const selectedFilterCount = computed(() => {
    return Object.keys(selectedFilters.value).length
  })

  if (initialFilter) {
    initializeSelectedFilters()
  }

  return {
    filterData,
    selectedFilters,
    currentSortId,
    
    filterUrl,
    clearUrl,
    hasActiveFilters,
    selectedFilterCount,
    
    setFilterData,
    toggleFilter,
    clearFilters,
    setSorting,
    buildFilterUrl,
  }
}
