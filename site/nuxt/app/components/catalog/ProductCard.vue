<!--
  Компонент карточки товара
  Отображает фото, название, цену и статус наличия
-->
<script setup lang="ts">
import type { CatalogItemDTO } from '~/types/iblock/catalog';
import Price from './Price.vue';

const props = defineProps<{
    item: CatalogItemDTO
}>()
const item = toRef(props, 'item');
const { isAvailable, images, price } = useCatalogItem(item);

</script>

<template>
    <div class="product-card bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 transition-transform hover:scale-105">
        <NuxtLink :to="item.detailPageUrl" class="block">
            <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-md mb-4 overflow-hidden">
                <img v-if="images && images.length > 0" :src="images[0]" :alt="item.name" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2 line-clamp-2 min-h-[3rem]">
                {{ item.name }}
            </h3>

            <Price v-if="price" :price="price"/>

            <div class="mt-2 text-sm">
                <span :class="[
                    'inline-block px-2 py-1 rounded',
                    isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                    {{ isAvailable ? 'В наличии' : 'Нет в наличии' }}
                </span>
            </div>
        </NuxtLink>
    </div>
</template>
