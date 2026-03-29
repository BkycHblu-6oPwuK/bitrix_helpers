<script setup lang="ts">
import type { ArticleElementPageApiResponse } from '~/types/articles';
const route = useRoute();
const { data } = await useApi<ArticleElementPageApiResponse>(`articles/${route.params.code}`)
useSeoPage(data.value?.data?.seo);
</script>

<template>
   <DevDump :data="data" :label="'Элемент статьи'"></DevDump>
</template>