<script setup lang="ts">
import Sections from '~/components/catalog/Sections.vue'
import type { SectionsPageApiResponse } from '~/types/iblock/catalog'

const { data } = await useApi<SectionsPageApiResponse>('catalog')

if (data.value?.data?.seo) {
  useSeoPage(data.value.data.seo)
}
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <Sections v-if="data?.data?.page?.catalogSectionList" :sections="data.data.page.catalogSectionList" />
  </div>
</template>