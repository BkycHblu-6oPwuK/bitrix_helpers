<!--
  Компонент детальной страницы товара
  Отображает галерею, название, цену, описание и переключатель предложений
-->
<script setup lang="ts">
import type { CatalogItemDTO } from '~/types/iblock/catalog';
import ProductGallery from './ProductGallery.vue';
import ProductInfo from './ProductInfo.vue';
import ProductOffers from './ProductOffers.vue';
import ProductActions from './ProductActions.vue';
import ProductDescription from './ProductDescription.vue';

const props = defineProps<{
  item: CatalogItemDTO
}>()

const catalogDetail = useCatalogDetailStore()

catalogDetail.setItem(props.item)

onUnmounted(() => {
  catalogDetail.reset()
})
</script>

<template>
  <div class="product-detail">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <ProductGallery />
      
      <div class="product-info space-y-6">
        <ProductInfo />
        <ProductOffers v-if="item.offers.length > 0" />
        <ProductActions />
        <ProductDescription />
      </div>
    </div>
  </div>
</template>
