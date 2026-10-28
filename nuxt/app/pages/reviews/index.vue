<script setup lang="ts">
import type { ReviewsListPageApiResponse } from '~/types/pages/reviews';
const { data } = await useApi<ReviewsListPageApiResponse>('reviews')
useSeoPage(data.value?.data?.seo);
</script>

<template>
    <DevDump :data="data" :label="'отзывы'"></DevDump>
</template>