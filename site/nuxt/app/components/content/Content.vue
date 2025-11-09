<script setup lang="ts">
import type { PageData } from '~/types/content';
import MainBanner from './blocks/MainBanner.vue'
import SliderArticles from './blocks/SliderArticles.vue'
import Form from './blocks/Form.vue'
import Slider from './blocks/Slider.vue'

const props = defineProps<{
  pathName: string
}>()

const page = await useApi<PageData>('get-content', {
  query: {
    pathName: props.pathName
  },
})

const componentsMap = {
  main_banner: MainBanner,
  slider_articles: SliderArticles,
  form: Form,
  slider: Slider,
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
