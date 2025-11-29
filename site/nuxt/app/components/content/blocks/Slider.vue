<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { SliderContent } from '~/types/iblock/content'

const props = defineProps<{
  data: SliderContent['result']
}>()
</script>

<template>
  <Dump :data="props.data" :label="'Slider.vue'"></Dump>
</template>
