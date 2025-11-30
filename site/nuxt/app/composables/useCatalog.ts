import { ref, watch } from 'vue'
import type { CatalogDTO } from '~/types/iblock/catalog'
import type { PageData } from '~/types/iblock/content'

export function useCatalog(initialData?: CatalogDTO) {
  const catalogData = ref<CatalogDTO | null>(initialData || null)
  const isLoading = ref(false)
  const error = ref<Error | null>(null)
  const route = useRoute()
  const lastLoadedPage = ref<number>(initialData?.catalogSection?.pagination?.currentPage || 0)
  const nextAppendMode = ref<boolean>(false)

  const loadCatalogPage = async (url: URL, loadOptions?: { append?: boolean; fromWatch?: boolean }) => {
    isLoading.value = true
    error.value = null

    try {
      const path = url.pathname
      const query = Object.fromEntries(url.searchParams)

      const { data: response, error: apiError } = await useApi<PageData<CatalogDTO>>(path, {
        query,
        method: 'get'
      })

      if (apiError.value) {
        throw apiError.value
      }

      if (!response.value?.data?.page) {
        throw new Error('Invalid response format')
      }

      const newPage = response.value.data.page
      const currentPageNum = newPage.catalogSection?.pagination?.currentPage || 1

      const shouldAppend = loadOptions?.fromWatch ? nextAppendMode.value : (loadOptions?.append || false)

      if (shouldAppend && catalogData.value && newPage) {
        catalogData.value.catalogSection.items = [
          ...catalogData.value.catalogSection.items,
          ...newPage.catalogSection.items,
        ]
        catalogData.value.catalogSection.pagination = newPage.catalogSection.pagination
      } else if (newPage) {
        catalogData.value = newPage
      }

      lastLoadedPage.value = currentPageNum
      nextAppendMode.value = false // Reset after use

      return response.value
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const setCatalogData = (data: CatalogDTO) => {
    catalogData.value = data
  }

  const setAppendMode = (append: boolean) => {
    nextAppendMode.value = append
  }

  // Auto-load on route change if enabled
  watch(() => [route.path, route.query], async ([newPath, newQuery]) => {
    const url = new URL(newPath as string, window.location.origin)
    Object.entries(newQuery as Record<string, string>).forEach(([key, value]) => {
      url.searchParams.set(key, value)
    })
    await loadCatalogPage(url, { fromWatch: true })
  }, { immediate: false })

  return {
    catalogData,
    isLoading,
    error,
    loadCatalogPage,
    setCatalogData,
    setAppendMode,
  }
}
