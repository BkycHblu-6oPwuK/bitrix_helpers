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
      <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
        <button
          @click="catalogDetail.decrementQuantity()"
          class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
          -
        </button>
        <input
          :value="catalogDetail.quantity"
          @input="catalogDetail.setQuantity(Number(($event.target as HTMLInputElement).value))"
          type="number"
          min="1"
          class="w-16 text-center border-x border-gray-300 dark:border-gray-600 py-2 bg-transparent"
        />
        <button
          @click="catalogDetail.incrementQuantity()"
          class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
          +
        </button>
      </div>
      
      <button
        @click="catalogDetail.addToBasket()"
        :disabled="!catalogDetail.isAvailable"
        class="flex-1 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-3 px-6 rounded-lg transition-colors"
      >
        В корзину
      </button>
      
      <Favourite :productId="catalogDetail.item?.id" :absolute="false" />
    </div>
  </div>
</template>
