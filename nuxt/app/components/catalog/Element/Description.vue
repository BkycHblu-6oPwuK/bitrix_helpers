<!-- Описание товара -->
<script setup lang="ts">
const { item } = useCatalogProduct()
</script>

<template>
    <div v-if="item.detailText" class="product-description prose dark:prose-invert max-w-none">
        <h2 class="text-xl font-semibold mb-3">Описание</h2>
        <!-- HTML из Bitrix: убедитесь, что он санитизируется на бэкенде (или через DOMPurify) -->
        <div v-html="item.detailText"></div>
    </div>
</template>