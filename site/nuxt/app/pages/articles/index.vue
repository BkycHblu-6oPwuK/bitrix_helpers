<script setup lang="ts">
import type { ArticlesListPageApiResponse } from '~/types/iblock/articles';
const { data } = await useApi<ArticlesListPageApiResponse>('articles')
useSeoPage(data.value?.data?.seo);
</script>

<template>
   <DevDump :data="data" :label="'Статьи'"></DevDump>
</template>