<!--
  Заглушка для неизвестных типов меню
  Отображается когда menuType не найден в componentsMap
  Помогает отлаживать проблемы с новыми типами меню
-->
<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';

// Пропсы: любые данные меню
const props = defineProps<{
  data?: any
}>()
</script>

<template>
  <Dump :data="data" :label="'Неизвестное меню'"></Dump>
</template>
