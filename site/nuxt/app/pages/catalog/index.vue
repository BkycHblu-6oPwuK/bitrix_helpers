<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { CatalogData } from '~/types/catalog/catalog';
const { data } = await useApi<CatalogData>('catalog')
useSeoPage(data.value?.data?.seo);
</script>

<template>
   <Dump :data="data" :label="'Каталог'"></Dump>
</template>