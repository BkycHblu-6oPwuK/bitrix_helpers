<!--
  Компонент списка дочерних разделов (подкатегорий)
  Отображает сетку с карточками разделов
  Показывается вверху страницы каталога для навигации
-->
<script setup lang="ts">
import type { SectionDTO } from '~/types/iblock/catalog.ts';
import SectionItem from './SectionItem.vue';

// Пропсы: массив дочерних разделов
const props = defineProps<{
  sections: Array<SectionDTO>
}>()
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <SectionItem
            v-for="item in sections"
            :key="item.id"
            :item="item"
        />
    </div>
</template>