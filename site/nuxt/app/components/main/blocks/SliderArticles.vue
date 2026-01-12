<!--
  Блок слайдера со статьями
  Отображает карусель с последними статьями
  TODO: Реализовать верстку с карточками статей
  Пока используется Dump для отладки
-->
<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { SliderArticlesContent } from '~/types/iblock/content'

// Пропсы: массив статей для слайдера
const props = defineProps<{
  data: SliderArticlesContent['result']
}>()
</script>

<template>
  <Dump :data="props.data" :label="'SliderArticles.vue'"></Dump>
</template>
