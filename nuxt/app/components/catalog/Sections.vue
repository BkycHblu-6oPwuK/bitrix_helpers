<!--
  Компонент списка дочерних разделов (подкатегорий)
  Отображает сетку с карточками разделов
  Показывается вверху страницы каталога для навигации
-->
<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog'

defineProps<{
    sectionList: CatalogDTO['sectionList']
}>()
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <CatalogSectionItem v-for="item in sectionList" :key="item.id" :item="item" />
    </div>
</template>