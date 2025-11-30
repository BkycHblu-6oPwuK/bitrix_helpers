<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog'
import CatalogFilter from './CatalogFilter.vue'
import CatalogSection from './CatalogSection.vue'
import Sections from './Sections.vue'

const props = defineProps<{
  catalog: CatalogDTO
}>()
const { catalogData, isLoading, setAppendMode } = useCatalog(props.catalog)
const { buildFilterUrl, setSorting } = useCatalogFilter(catalogData.value?.catalogFilter)
const { getPageUrl } = usePagination(catalogData.value?.catalogSection.pagination)

const handleApplyFilter = async () => {
  if (!catalogData.value?.catalogFilter) return
  
  const url = buildFilterUrl(window.location.href)
  await navigateTo(url.pathname + url.search, { replace: false })
}

const handleClearFilter = async () => {
  if (!catalogData.value?.catalogFilter) return
  
  const clearUrl = catalogData.value.catalogFilter.clearUrl
  await navigateTo(clearUrl, { replace: false })
}

const handleUpdateSorting = async (sortId: string) => {
  setSorting(sortId)
  const url = buildFilterUrl(window.location.href)
  await navigateTo(url.pathname + url.search, { replace: false })
}

const handleShowMore = async () => {
  if (!catalogData.value?.catalogSection.pagination) return
  
  const nextPage = catalogData.value.catalogSection.pagination.currentPage + 1
  const pageUrl = getPageUrl(nextPage)
  
  if (pageUrl) {
    setAppendMode(true)
    await navigateTo(pageUrl, { replace: true })
  }
}

const handleChangePage = async (page: number) => {
  const pageUrl = getPageUrl(page)
  
  if (pageUrl) {
    setAppendMode(false)
    await navigateTo(pageUrl, { replace: false })
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}
</script>

<template>
  <div class="catalog-page">
    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <div class="text-lg">Загрузка...</div>
    </div>

    <div v-else-if="catalogData" class="grid gap-8">
      <aside class="lg:col-span-1">
        <div class="sticky top-4">
          <CatalogFilter
            v-if="catalogData.catalogFilter"
            :filter="catalogData.catalogFilter"
            @apply-filter="handleApplyFilter"
            @clear-filter="handleClearFilter"
            @update-sorting="handleUpdateSorting"
          />
        </div>
      </aside>

      <main class="lg:col-span-3">
        <Sections
          v-if="catalogData.catalogSectionList?.length"
          :sections="catalogData.catalogSectionList"
          class="mb-8"
        />

        <CatalogSection
          v-if="catalogData.catalogSection"
          :section="catalogData.catalogSection"
          @show-more="handleShowMore"
          @change-page="handleChangePage"
        />
      </main>
    </div>
  </div>
</template>
