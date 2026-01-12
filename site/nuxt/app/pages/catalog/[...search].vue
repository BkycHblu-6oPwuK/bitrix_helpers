<script setup lang="ts">
import type { CatalogPageApiResponse } from '~/types/iblock/catalog.ts'

const route = useRoute()
const search = route.params.search.join('/')
const apiUrl = `catalog/${search}`;
const { data } = await useApi<CatalogPageApiResponse>(apiUrl)
if (data.value?.data?.seo) {
  useSeoPage(data.value.data.seo)
}
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <Catalog v-if="data?.data?.page" :catalog="data.data.page" :api-url="apiUrl"/>
  </div>
</template>