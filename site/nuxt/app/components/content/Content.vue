<script setup lang="ts">
import type { PageData } from '~/types/content';

const props = defineProps<{
  pathName: string
}>()

const page = await useApi<PageData>('get-content', {
  query: {
    pathName: props.pathName
  },
})
console.log(page.data.value?.data?.page)

const componentsMap = {
  main_banner: defineAsyncComponent(() => import('./blocks/MainBanner.vue')),
  slider: defineAsyncComponent(() => import('./blocks/Slider.vue')),
}

const resolveComponent = (type: string) => {
  return componentsMap[type as keyof typeof componentsMap] ||
    defineAsyncComponent(() => import('./blocks/UnknownBlock.vue'))
}
</script>

<template>
  <div class="page-content">
    <component v-for="(block, i) in page.data.value?.data?.page" :is="resolveComponent(block.type)" :key="i" :data="block.result" />
  </div>
</template>
