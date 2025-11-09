<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { ArticleData } from '~/types/articles';
const route = useRoute();
const { data } = await useApi<ArticleData>(`articles/${route.params.code}`)
useSeoPage(data.value?.data?.seo);
</script>

<template>
   <Dump :data="data" :label="'Элемент статьи'"></Dump>
</template>