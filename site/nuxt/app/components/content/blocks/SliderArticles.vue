<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { SliderArticlesContent } from '~/types/iblock/content'

const props = defineProps<{
  data: SliderArticlesContent['result']
}>()
</script>

<template>
  <Dump :data="props.data" :label="'SliderArticles.vue'"></Dump>
</template>
