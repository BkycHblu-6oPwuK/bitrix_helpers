<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';

const props = defineProps<{
  data?: any
}>()
</script>

<template>
  <Dump :data="data" :label="'Неизвестное меню'"></Dump>
</template>
