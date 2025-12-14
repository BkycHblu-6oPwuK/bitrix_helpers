<script setup lang="ts">
import type { FavouritePageApiResponse, FavouritePageDTO } from '~/types/favourite';
import FavouritePage from '~/components/catalog/FavouritePage.vue'

const urlParams = useRoute().query;
const apiUrl = `favorite/page?${new URLSearchParams(urlParams as Record<string, string>).toString()}`;
const page = ref<FavouritePageDTO>()
onMounted(() => {
    useApiFetch<FavouritePageApiResponse>(apiUrl).then(({data}) => {
        if(data?.page) {
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