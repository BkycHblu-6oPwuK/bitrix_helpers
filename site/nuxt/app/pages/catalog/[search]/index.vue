<script setup lang="ts">
import Catalog from '~/components/catalog/Catalog.vue'
import type { CatalogPageApiResponse } from '~/types/iblock/catalog'

const route = useRoute()
const { data } = await useApi<CatalogPageApiResponse>(`catalog/${route.params.search}`)
if (data.value?.data?.seo) {
  useSeoPage(data.value.data.seo)
}
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <Catalog v-if="data?.data?.page" :catalog="data.data.page" />
  </div>
</template>