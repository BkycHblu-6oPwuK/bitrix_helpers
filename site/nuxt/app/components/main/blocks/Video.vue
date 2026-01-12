<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { VideoContent } from '~/types/iblock/content'

// Пропсы: данные слайдов (изображения, тексты, ссылки)
const props = defineProps<{
  data: VideoContent['result']
}>()
</script>

<template>
  <Dump :data="props.data" :label="'Video.vue'"></Dump>
</template>
