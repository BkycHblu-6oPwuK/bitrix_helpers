<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { MenuBlock } from '~/types/menu'

const props = defineProps<{
  data: MenuBlock
}>()
</script>

<template>
  <Dump :data="props" :label="'FooterMenu.vue'"></Dump>
</template>