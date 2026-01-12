<script setup lang="ts">
import type { CatalogDetailPageApiResponse } from '~/types/iblock/catalog';

const route = useRoute();

const { data } = await useApi<CatalogDetailPageApiResponse>(`product/${route.params.code}`)

if (data.value?.data?.seo) {
    useSeoPage(data.value.data.seo)
}
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <CatalogElement 
            v-if="data?.data?.page.element" 
            :item="data?.data?.page.element" 
        />
    </div>
</template>