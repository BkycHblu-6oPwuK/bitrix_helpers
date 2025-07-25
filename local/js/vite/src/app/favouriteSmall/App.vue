<script setup>
import { inject } from 'vue';
import FavouritesSmall from "@/common/components/FavouritesSmall.vue";
const productId = inject('productId');
</script>

<template>
    <FavouritesSmall :productId="productId" />
</template>