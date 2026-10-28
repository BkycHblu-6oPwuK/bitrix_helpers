<script setup lang="ts">
import type { FavouritePageApiResponse, FavouritePageDTO } from '~/types/pages/favourite'
 
const route = useRoute()
const query = route.fullPath.split('#')[0]!.split('?')[1] ?? ''
const apiUrl = `favorite/page${query ? `?${query}` : ''}`
 
// Избранное индивидуально — грузим только на клиенте, как и раньше
const page = ref<FavouritePageDTO>()
onMounted(() => {
    useApiFetch<FavouritePageApiResponse>(apiUrl).then(({ data }) => {
        if (data?.page) page.value = data.page
        if (data?.seo) useSeoPage(data.seo)
    })
})
</script>
 
<template>
    <FavouritePage v-if="page" :favourite="page" :api-url="apiUrl" />
</template>