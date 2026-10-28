<script setup lang="ts">
import type { CatalogPageApiResponse } from '~/types/pages/catalog'
import type { SectionInitParams } from '~/composables/section/useSectionPage'

definePageMeta({
    key: route => `catalog-section-page-${getBasePathNotFilter(route)}`,
    scrollToTop: stopScrollToTopForFilter,
})

const route = useRoute()
const search = (route.params.search as string[]).join('/')
const query = new URLSearchParams(route.fullPath.split('?')[1] ?? '').toString()
const apiUrl = `/catalog/${search}${query ? `?${query}` : ''}`
const { data } = await useApi<CatalogPageApiResponse>(apiUrl)
// при смене маршрута внутри каталога, но не при смене фильтров (когда ключ страницы не меняется)
const onStopSyncUrl: SectionInitParams['onStopSyncUrl'] = ((to, from) => {
    if(to.fullPath !== from.fullPath && getBasePathNotFilter(to) !== getBasePathNotFilter(from)) {
        return true
    }
    return false
})
if (data.value?.data?.seo) useSeoPage(data.value.data.seo)
provideSectionPage(data?.value?.data?.page, { apiUrl, syncUrl: true, onStopSyncUrl })
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <Catalog v-if="data?.data?.page" />
    </div>
</template>