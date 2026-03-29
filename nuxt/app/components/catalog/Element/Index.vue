<!--
  Компонент детальной страницы товара
  Отображает галерею, название, цену, описание и переключатель предложений
-->
<script setup lang="ts">
import type { CatalogItemDTO } from '~/types/iblock/catalog';

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
      <CatalogElementGallery />
      
      <div class="product-info space-y-6">
        <CatalogElementInfo />
        <CatalogElementOffers v-if="item.offers.length > 0" />
        <CatalogElementActions />
        <CatalogElementDescription />
      </div>
    </div>
  </div>
</template>
