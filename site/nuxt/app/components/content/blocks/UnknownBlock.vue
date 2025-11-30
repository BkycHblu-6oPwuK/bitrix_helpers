<!--
  Заглушка для неизвестных типов блоков
  Отображается когда тип блока не найден в componentsMap
  Помогает отлаживать проблемы с новыми типами блоков
-->
<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';

// Пропсы: любые данные блока
const props = defineProps<{
  data?: any
}>()
</script>

<template>
  <Dump :data="data" :label="'Неизвестный блок контента'"></Dump>
</template>
