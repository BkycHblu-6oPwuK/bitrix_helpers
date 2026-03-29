<script setup lang="ts">
import type { CatalogPriceDTO } from '~/types/iblock/catalog';

const props = defineProps<{
    price: CatalogPriceDTO
}>()

</script>

<template>
    <div v-if="price" class="text-lg font-bold text-primary-600 dark:text-primary-400 mb-2">
        {{ formatPrice(price.price, price.currency) }}
    </div>
</template>
