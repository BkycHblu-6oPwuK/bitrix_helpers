<!--
  Блок слайдера (карусель изображений/контента)
  TODO: Реализовать верстку слайдера с навигацией
  Пока используется Dump для отладки
-->
<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { SliderContent } from '~/types/iblock/content'

// Пропсы: данные слайдов (изображения, тексты, ссылки)
const props = defineProps<{
  data: SliderContent['result']
}>()
</script>

<template>
  <Dump :data="props.data" :label="'Slider.vue'"></Dump>
</template>
