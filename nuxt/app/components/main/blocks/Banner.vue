<!--
  Блок главного баннера на главной странице
  TODO: Реализовать верстку баннера
  Пока используется Dump для отладки
-->
<script setup lang="ts">
import type { MainBannerContent } from '~/types/iblock/main'

// Пропсы: данные баннера (текст, изображения, ссылки)
const props = defineProps<{
    data: MainBannerContent['result']
}>()
</script>

<template>
    <DevDump :data="props.data" :label="'MainBanner.vue'"></DevDump>
</template>
