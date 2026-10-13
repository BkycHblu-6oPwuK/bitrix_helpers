<!--
  Компонент секции с товарами
  Отображает сетку товаров и компонент пагинации
-->
<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog';


// Пропсы: данные секции с массивом товаров и пагинацией
const { items } = useSection<CatalogDTO['section']['items']>()
</script>

<template>
    <div class="catalog-section">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <CatalogProductCard v-for="item in items" :key="item.id" :item="item" />
        </div>
        <CatalogPagination />
    </div>
</template>
