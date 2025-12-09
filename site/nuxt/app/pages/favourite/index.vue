<script setup lang="ts">
import type { FavouritePageApiResponse, FavouritePageDTO } from '~/types/favourite';
import FavouritePage from '~/components/catalog/FavouritePage.vue'

const apiUrl = 'favorite/page';
const page = ref<FavouritePageDTO>()
onMounted(() => {
    useApiFetch<FavouritePageApiResponse>(apiUrl).then(({data}) => {
        console.log(data)
        if(data?.page ) {
            page.value = data.page;
        }
        if (data?.seo) {
            useSeoPage(data.seo)
        }
    })
})
</script>

<template>
    <FavouritePage v-if="page" :favourite="page" :api-url="apiUrl" />
</template>