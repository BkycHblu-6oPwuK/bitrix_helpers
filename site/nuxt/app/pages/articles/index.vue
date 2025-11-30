<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { ArticlesListPageApiResponse } from '~/types/iblock/articles';
const { data } = await useApi<ArticlesListPageApiResponse>('articles')
useSeoPage(data.value?.data?.seo);
</script>

<template>
   <Dump :data="data" :label="'Статьи'"></Dump>
</template>