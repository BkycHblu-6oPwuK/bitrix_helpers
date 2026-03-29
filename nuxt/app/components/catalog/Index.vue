<!--
  Основной компонент-оркестратор для каталога товаров
  Управляет взаимодействием между фильтрами, списком товаров и пагинацией
  Обрабатывает события от дочерних компонентов и обновляет URL/данные
-->
<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog.ts';

// Пропсы: начальные данные каталога с сервера
const props = defineProps<{
  catalog: CatalogDTO,
  apiUrl: string
}>()

// Инициализируем store с данными и получаем методы управления
const { sectionData, isLoading, setAppendMode, loadPage, setApiUrl, selectedFilters } = useSection<CatalogDTO>(props.catalog)
const { buildFilterUrl, setSorting } = useSectionFilter()
const { getPageUrl } = usePagination()

/**
 * Обработчик применения фильтров
 * Строит URL с выбранными фильтрами и загружает новые данные
 */
const handleApplyFilter = async () => {
  if (Object.keys(selectedFilters.value).length === 0) {
    handleClearFilter()
    return
  }
  if (!sectionData.value?.filter) return
  const url = buildFilterUrl()
  await loadPage<CatalogDTO>(url, { navigateFilter: true })
}

/**
 * Обработчик сброса всех фильтров
 * Переходит на URL без фильтров и обновляет данные
 */
const handleClearFilter = async () => {
  if (!sectionData.value?.filter) return

  // Сначала очищаем выбранные фильтры в store
  const store = useSectionStore()
  store.clearFilters()

  const clearUrl = sectionData.value.filter.clearUrl
  loadPage<CatalogDTO>(new URL(clearUrl, window.location.origin), { navigateFilter: true })
}

/**
 * Обработчик изменения сортировки
 * Обновляет ID сортировки в store и загружает отсортированные данные
 */
const handleUpdateSorting = async (sortId: string) => {
  setSorting(sortId)
  const url = buildFilterUrl()
  await loadPage<CatalogDTO>(url)
}

/**
 * Обработчик кнопки "Показать еще"
 * Дозагружает следующую страницу и добавляет товары к существующим
 */
const handleShowMore = async () => {
  if (!sectionData.value?.section.pagination) return
  const nextPage = sectionData.value.section.pagination.currentPage + 1
  const pageUrl = getPageUrl(nextPage)
  if (pageUrl) {
    setAppendMode(true) // Включаем режим добавления
    loadPage<CatalogDTO>(pageUrl, { append: true })
  }
}

/**
 * Обработчик перехода на конкретную страницу
 * Заменяет текущий список товаров и прокручивает вверх
 */
const handleChangePage = async (page: number) => {
  const pageUrl = getPageUrl(page)
  if (pageUrl) {
    setAppendMode(false) // Выключаем режим добавления
    window.scrollTo({ top: 0, behavior: 'smooth' })
    loadPage<CatalogDTO>(pageUrl)
  }
}

onMounted(() => {
  setApiUrl(props.apiUrl);
})
</script>

<template>
  <div class="catalog-page">
    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <div class="text-lg">Загрузка...</div>
    </div>

    <div v-else-if="sectionData" class="grid gap-8">
      <aside class="lg:col-span-1">
        <div class="sticky top-4">
          <CatalogFilter v-if="sectionData.filter" :filter="sectionData.filter" @apply-filter="handleApplyFilter"
            @clear-filter="handleClearFilter" @update-sorting="handleUpdateSorting" />
        </div>
      </aside>

      <main class="lg:col-span-3">
        <CatalogSections v-if="sectionData.sectionList?.length" :sections="sectionData.sectionList" class="mb-8" />

        <CatalogSection v-if="sectionData.section" :section="sectionData.section" @show-more="handleShowMore"
          @change-page="handleChangePage" />
      </main>
    </div>
  </div>
</template>
