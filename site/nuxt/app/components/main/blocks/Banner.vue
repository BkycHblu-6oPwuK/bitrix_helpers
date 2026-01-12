<!--
  Блок главного баннера на главной странице
  TODO: Реализовать верстку баннера
  Пока используется Dump для отладки
-->
<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { MainBannerContent } from '~/types/iblock/content'

// Пропсы: данные баннера (текст, изображения, ссылки)
const props = defineProps<{
  data: MainBannerContent['result']
}>()
</script>

<template>
  <Dump :data="props.data" :label="'MainBanner.vue'"></Dump>
</template>
