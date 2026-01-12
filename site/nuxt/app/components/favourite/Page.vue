<!--
  Основной компонент-оркестратор для каталога товаров
  Управляет взаимодействием между фильтрами, списком товаров и пагинацией
  Обрабатывает события от дочерних компонентов и обновляет URL/данные
-->
<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog.ts';
import type { FavouritePageDTO } from '~/types/favourite';

// Пропсы: начальные данные каталога с сервера
const props = defineProps<{
  favourite: FavouritePageDTO
  apiUrl: string
}>()

// Инициализируем store с данными и получаем методы управления
const { sectionData, isLoading, setAppendMode, setApiUrl, loadPage } = useSection<FavouritePageDTO>(props.favourite)
const { getPageUrl } = usePagination()

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

      <main class="lg:col-span-3">
        <CatalogSections
          v-if="sectionData.sectionList?.length"
          :sections="sectionData.sectionList"
          class="mb-8"
        />

        <CatalogSection
          v-if="sectionData.section"
          :section="sectionData.section"
          @show-more="handleShowMore"
          @change-page="handleChangePage"
        />
      </main>
    </div>
  </div>
</template>
