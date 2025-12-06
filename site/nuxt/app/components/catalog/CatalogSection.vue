<!--
  Компонент секции с товарами
  Отображает сетку товаров и компонент пагинации
-->
<script setup lang="ts">
import type { SectionItemsDTO } from '~/types/iblock/page';
import Pagination from './Pagination.vue';
import ProductCard from './ProductCard.vue';
import type { CatalogItemDTO } from '~/types/iblock/catalog.ts';

// Пропсы: данные секции с массивом товаров и пагинацией
const props = defineProps<{
  section: SectionItemsDTO<CatalogItemDTO>
}>()

// События для родительского компонента
const emit = defineEmits<{
  showMore: [] // Дозагрузка следующей страницы
  changePage: [page: number] // Переход на конкретную страницу
}>()
</script>

<template>
  <div class="catalog-section">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <ProductCard
        v-for="item in section.items"
        :key="item.id"
        :item="item"
      />
    </div>
    <Pagination :pagination="section.pagination" @showMore="emit('showMore')" @changePage="emit('changePage', $event)"></Pagination>
  </div>
</template>
