<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { MainBannerContent } from '~/types/content'

const props = defineProps<{
  data: MainBannerContent['result']
}>()
</script>

<template>
  <Dump :data="props.data" :label="'MainBanner.vue'"></Dump>
</template>
