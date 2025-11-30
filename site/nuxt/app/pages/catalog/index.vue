<!--
  Главная страница каталога (список разделов)
  Отображает сетку с карточками основных разделов
  Загружает данные с сервера и устанавливает SEO мета-теги
-->
<script setup lang="ts">
import Sections from '~/components/catalog/Sections.vue'
import type { SectionsPageApiResponse } from '~/types/iblock/catalog.ts'

// Загрузка списка разделов каталога
const { data } = await useApi<SectionsPageApiResponse>('catalog')

// Установка SEO мета-тегов страницы
if (data.value?.data?.seo) {
  useSeoPage(data.value.data.seo)
}
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <Sections v-if="data?.data?.page?.sectionList" :sections="data.data.page.sectionList" />
  </div>
</template>