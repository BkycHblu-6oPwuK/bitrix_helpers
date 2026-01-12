<!--
  Компонент переключателя торговых предложений
  Позволяет выбрать вариант товара (размер, цвет и т.д.)
-->
<script setup lang="ts">
const catalogDetail = useCatalogDetailStore()
</script>

<template>
  <div v-if="catalogDetail.item?.offersTree" class="product-offers space-y-4">
    <div
      v-for="prop in catalogDetail.item.offersTree.props"
      :key="prop.code"
      class="offer-property"
    >
      <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
        {{ prop.name }}
      </h3>
      
      <div class="flex flex-wrap gap-2">
        <button
          v-for="valueItem in catalogDetail.getAvailableValuesForProp(prop.code)"
          :key="valueItem.value"
          @click="catalogDetail.selectOfferValue(prop.code, valueItem.value)"
          :class="[
            'px-4 py-2 border rounded-lg transition-colors text-sm font-medium',
            catalogDetail.selectedValues[prop.code] === valueItem.value
              ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-300'
              : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'
          ]"
        >
          <div v-if="valueItem.pictureSrc" class="flex items-center gap-2">
            <img
              :src="valueItem.pictureSrc"
              :alt="valueItem.name"
              class="w-6 h-6 rounded object-cover"
            />
            <span>{{ valueItem.value }}</span>
          </div>
          <span v-else>{{ valueItem.value }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
