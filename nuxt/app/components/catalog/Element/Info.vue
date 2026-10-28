<!-- Основная информация: название, артикул, наличие, цена (цена и наличие — по выбранному предложению) -->
<script setup lang="ts">
const { item, article, isAvailable, price } = useCatalogProduct()
</script>

<template>
    <div class="product-info-header">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">
            {{ item.name }}
        </h1>

        <div class="flex items-center gap-4 mb-4">
            <span v-if="article" class="text-gray-600 dark:text-gray-400">
                Артикул: {{ article }}
            </span>
            <span :class="[
                'inline-block px-3 py-1 rounded-full text-sm font-medium',
                isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            ]">
                {{ isAvailable ? 'В наличии' : 'Нет в наличии' }}
            </span>
        </div>

        <div v-if="price" class="mb-6">
            <CatalogPrice :price="price" class="text-2xl font-bold" />
        </div>
    </div>
</template>