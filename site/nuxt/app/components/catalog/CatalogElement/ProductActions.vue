<!--
  Компонент действий с товаром
  Количество, кнопки добавления в корзину и избранное
-->
<script setup lang="ts">
import Favourite from '../Favourite.vue';
const catalogDetail = useCatalogDetailStore()
</script>

<template>
  <div class="product-actions">
    <!-- Количество и кнопки -->
    <div class="flex items-center gap-4">
      <BasketAddToBasket
        v-if="catalogDetail.item?.preselectedOffer"
        :offer-id="catalogDetail.item.preselectedOffer.id"
        :quantity="catalogDetail.quantity"
        :disabled="!catalogDetail.isAvailable"
        size="lg"
        variant="default"
        class="flex-1"
      />
      
      <Favourite v-if="catalogDetail.item?.id" :productId="catalogDetail.item?.id" :absolute="false" />
    </div>
  </div>
</template>
