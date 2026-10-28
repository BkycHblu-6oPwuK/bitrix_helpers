<script setup lang="ts">
import type { CatalogDetailPageApiResponse } from '~/types/pages/catalog';

const route = useRoute();
const query = new URLSearchParams(route.fullPath.split('?')[1] ?? '').toString()
const { data } = await useApi<CatalogDetailPageApiResponse>(`product/${route.params.code}${query ? `?${query}` : ''}`)
const element = data.value?.data?.page.element

if(!element) {
    throw createError({ statusCode: 404, statusMessage: 'Not Found' })
}

useSeoPage(data.value?.data?.seo)
provideCatalogProduct(() => element, { syncOfferUrl: true })
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <CatalogElement />
    </div>
</template>