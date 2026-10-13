<script setup lang="ts">
import type { CatalogPageApiResponse } from '~/types/pages/catalog'

definePageMeta({ key: 'catalog' })

const route = useRoute()
const search = (route.params.search as string[]).join('/')
const query = new URLSearchParams(route.fullPath.split('?')[1] ?? '').toString()
const apiUrl = `/catalog/${search}${query ? `?${query}` : ''}`
const { data } = await useApi<CatalogPageApiResponse>(apiUrl)
if (data.value?.data?.seo) useSeoPage(data.value.data.seo)
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <Catalog v-if="data?.data?.page" :catalog="data.data.page" :api-url="apiUrl" sync-url />
    </div>
</template>