<script setup lang="ts">
import type { PageData } from '~/types/iblock/content';

import MainBanner from './blocks/MainBanner.vue'
import SliderArticles from './blocks/SliderArticles.vue'
import Form from './blocks/Form.vue'
import Slider from './blocks/Slider.vue'
import UnknownBlock from './blocks/UnknownBlock.vue' // ✔ Импортируем явно

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
  unknown: UnknownBlock,
}

const resolveComponent = (type: string) => {
  return componentsMap[type as keyof typeof componentsMap] || UnknownBlock
}
</script>

<template>
  <div class="page-content">
    <component
      v-for="(block, i) in page.data.value?.data?.page"
      :is="resolveComponent(block.type)"
      :key="i"
      :data="block.result"
    />
  </div>
</template>
