<!--
  Блок слайдера со статьями
  Отображает карусель с последними статьями
  TODO: Реализовать верстку с карточками статей
  Пока используется Dump для отладки
-->
<script setup lang="ts">
import type { MainSliderArticlesContent } from '~/types/iblock/main'

// Пропсы: массив статей для слайдера
const props = defineProps<{
    data: MainSliderArticlesContent['result']
}>()
</script>

<template>
    <DevDump :data="props.data" :label="'SliderArticles.vue'"></DevDump>
</template>
