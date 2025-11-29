<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { ArticlesListData } from '~/types/iblock/articles';
const { data } = await useApi<ArticlesListData>('articles')
useSeoPage(data.value?.data?.seo);
</script>

<template>
   <Dump :data="data" :label="'Статьи'"></Dump>
</template>