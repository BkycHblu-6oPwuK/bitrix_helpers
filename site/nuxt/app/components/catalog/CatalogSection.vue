<!--
  Компонент секции с товарами
  Отображает сетку товаров и компонент пагинации
  Каждая карточка товара - это ссылка на детальную страницу
-->
<script setup lang="ts">
import type { SectionItemsDTO } from '~/types/iblock/page';
import Pagination from './Pagination.vue';
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
      <div
        v-for="item in section.items"
        :key="item.id"
        class="product-card bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 transition-transform hover:scale-105"
      >
        <NuxtLink :to="item.detailPageUrl" class="block">
          <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-md mb-4"></div>
          
          <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2 line-clamp-2">
            {{ item.name }}
          </h3>
          
          <div v-if="item.prices" class="text-lg font-bold text-primary-600 dark:text-primary-400">
            
          </div>
          
          <div v-if="item.catalog" class="mt-2 text-sm">
            <span
              :class="[
                'inline-block px-2 py-1 rounded',
                item.catalog.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
              ]"
            >
              {{ item.catalog.available ? 'В наличии' : 'Нет в наличии' }}
            </span>
          </div>
        </NuxtLink>
      </div>
    </div>
    <Pagination :pagination="section.pagination" @showMore="emit('showMore')" @changePage="emit('changePage', $event)"></Pagination>
  </div>
</template>
