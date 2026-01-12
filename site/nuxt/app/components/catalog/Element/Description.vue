<!--
  Компонент описания товара
-->
<script setup lang="ts">
const catalogDetail = useCatalogDetailStore()
</script>

<template>
  <div v-if="catalogDetail.item?.detailText" class="product-description prose dark:prose-invert max-w-none">
    <h2 class="text-xl font-semibold mb-3">Описание</h2>
    <div v-html="catalogDetail.item.detailText"></div>
  </div>
</template>
