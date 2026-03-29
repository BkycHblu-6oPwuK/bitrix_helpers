<!--
  Компонент основной информации о товаре
  Название, артикул, цена, наличие
-->
<script setup lang="ts">

const catalogDetail = useCatalogDetailStore()
</script>

<template>
  <div v-if="catalogDetail.item" class="product-info-header">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">
      {{ catalogDetail.item.name }}
    </h1>
    
    <div class="flex items-center gap-4 mb-4">
      <span class="text-gray-600 dark:text-gray-400">
        Артикул: {{ catalogDetail.item.code }}
      </span>
      <span
        :class="[
          'inline-block px-3 py-1 rounded-full text-sm font-medium',
          catalogDetail.isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
        ]"
      >
        {{ catalogDetail.isAvailable ? 'В наличии' : 'Нет в наличии' }}
      </span>
    </div>
    
    <div class="mb-6">
      <CatalogPrice v-if="catalogDetail.price"
        :price="catalogDetail.price" 
        class="text-2xl font-bold"
      />
    </div>
  </div>
</template>
