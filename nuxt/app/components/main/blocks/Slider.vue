<!--
  Блок слайдера (карусель изображений/контента)
  TODO: Реализовать верстку слайдера с навигацией
  Пока используется Dump для отладки
-->
<script setup lang="ts">
import type { MainSliderContent } from '~/types/iblock/main'

// Пропсы: данные слайдов (изображения, тексты, ссылки)
const props = defineProps<{
    data: MainSliderContent['result']
}>()
</script>

<template>
    <DevDump :data="props.data" :label="'Slider.vue'"></DevDump>
</template>
