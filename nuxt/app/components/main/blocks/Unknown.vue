<!--
  Заглушка для неизвестных типов блоков
  Отображается когда тип блока не найден в componentsMap
  Помогает отлаживать проблемы с новыми типами блоков
-->
<script setup lang="ts">

// Пропсы: любые данные блока
const props = defineProps<{
    data?: any
}>()
</script>

<template>
    <DevDump :data="props.data" :label="'Неизвестный блок контента'"></DevDump>
</template>
