<script setup lang="ts">
import type { MainPageContent } from '~/types/iblock/content'

const props = defineProps<{ data: MainPageContent}>()

const groupedBlocks = computed(() => {
   const blocks = props.data || []
  return blocks.reduce((acc, block) => {
    if(!acc[block.type]) {
      acc[block.type] = []
    }
    acc[block.type].push(block.result)
    return acc
  }, {} as Record<string, any[]>)
})

</script>

<template>
  <div class="page-content">
    <MainBlocksBanner v-if="groupedBlocks['main_banner']" v-for="value in groupedBlocks['main_banner']" :key="value.id" :data="value" />
    <MainBlocksSlider v-if="groupedBlocks['slider']" v-for="value in groupedBlocks['slider']" :key="value.id" :data="value" />
    <MainBlocksSliderArticles v-if="groupedBlocks['slider_articles']" v-for="value in groupedBlocks['slider_articles']" :key="value.id" :data="value" />
    <MainBlocksVideo v-if="groupedBlocks['video']" v-for="value in groupedBlocks['video']" :key="value.id" :data="value" />
  </div>
</template>
