<!--
  Основной компонент-оркестратор для каталога товаров
  Управляет взаимодействием между фильтрами, списком товаров и пагинацией
  Обрабатывает события от дочерних компонентов и обновляет URL/данные
-->
<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog.ts';
import CatalogFilter from './CatalogFilter.vue'
import CatalogSection from './CatalogSection.vue'
import Sections from './Sections.vue'

// Пропсы: начальные данные каталога с сервера
const props = defineProps<{
  catalog: CatalogDTO
}>()

// Инициализируем store с данными и получаем методы управления
const { catalogData, isLoading, setAppendMode, loadCatalogPage } = useSection<CatalogDTO>(props.catalog)
const { buildFilterUrl, setSorting, filterData } = useSectionFilter()
const { getPageUrl } = usePagination()

/**
 * Обработчик применения фильтров
 * Строит URL с выбранными фильтрами и загружает новые данные
 */
const handleApplyFilter = async () => {
  if (!catalogData.value?.filter) return
  const url = buildFilterUrl(window.location.href)
  await loadCatalogPage<CatalogDTO>(url, { navigateFilter: true })
}

/**
 * Обработчик сброса всех фильтров
 * Переходит на URL без фильтров и обновляет данные
 */
const handleClearFilter = async () => {
  if (!catalogData.value?.filter) return
  
  const clearUrl = catalogData.value.filter.clearUrl
  loadCatalogPage<CatalogDTO>(new URL(clearUrl, window.location.origin), { navigateFilter: true })
}

/**
 * Обработчик изменения сортировки
 * Обновляет ID сортировки в store и загружает отсортированные данные
 */
const handleUpdateSorting = async (sortId: string) => {
  setSorting(sortId)
  const url = buildFilterUrl(window.location.href)
  await loadCatalogPage<CatalogDTO>(url)
}

/**
 * Обработчик кнопки "Показать еще"
 * Дозагружает следующую страницу и добавляет товары к существующим
 */
const handleShowMore = async () => {
  if (!catalogData.value?.section.pagination) return
  
  const nextPage = catalogData.value.section.pagination.currentPage + 1
  const pageUrl = getPageUrl(nextPage)
  
  if (pageUrl) {
    setAppendMode(true) // Включаем режим добавления
    loadCatalogPage<CatalogDTO>(pageUrl, { append: true })
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
    loadCatalogPage<CatalogDTO>(pageUrl)
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
            v-if="catalogData.filter"
            :filter="catalogData.filter"
            @apply-filter="handleApplyFilter"
            @clear-filter="handleClearFilter"
            @update-sorting="handleUpdateSorting"
          />
        </div>
      </aside>

      <main class="lg:col-span-3">
        <Sections
          v-if="catalogData.sectionList?.length"
          :sections="catalogData.sectionList"
          class="mb-8"
        />

        <CatalogSection
          v-if="catalogData.section"
          :section="catalogData.section"
          @show-more="handleShowMore"
          @change-page="handleChangePage"
        />
      </main>
    </div>
  </div>
</template>
