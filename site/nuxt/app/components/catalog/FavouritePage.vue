<!--
  Основной компонент-оркестратор для каталога товаров
  Управляет взаимодействием между фильтрами, списком товаров и пагинацией
  Обрабатывает события от дочерних компонентов и обновляет URL/данные
-->
<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog.ts';
import CatalogSection from './CatalogSection.vue'
import Sections from './Sections.vue'
import type { FavouritePageDTO } from '~/types/favourite';

// Пропсы: начальные данные каталога с сервера
const props = defineProps<{
  favourite: FavouritePageDTO
  apiUrl: string
}>()

// Инициализируем store с данными и получаем методы управления
const { catalogData, isLoading, setAppendMode, setApiUrl, loadCatalogPage } = useSection<FavouritePageDTO>(props.favourite)
const { getPageUrl } = usePagination()

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

onMounted(() => {
  setApiUrl(props.apiUrl);
})
</script>

<template>
  <div class="catalog-page">
    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <div class="text-lg">Загрузка...</div>
    </div>

    <div v-else-if="catalogData" class="grid gap-8">

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
