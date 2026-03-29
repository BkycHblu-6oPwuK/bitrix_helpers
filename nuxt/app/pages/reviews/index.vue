<script setup lang="ts">
import Dump from '~/components/dev/Dump.vue';
import type { ReviewsListPageApiResponse } from '~/types/iblock/reviews';
const { data } = await useApi<ReviewsListPageApiResponse>('reviews')
useSeoPage(data.value?.data?.seo);
</script>

<template>
   <Dump :data="data" :label="'отзывы'"></Dump>
</template>