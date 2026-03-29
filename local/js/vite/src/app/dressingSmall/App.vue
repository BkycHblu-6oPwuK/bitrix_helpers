<script setup>
import { inject } from 'vue';
import DressingSmall from "@/common/components/DressingSmall.vue";
const offerId = inject('offerId');
</script>

<template>
    <DressingSmall :offerId="offerId" />
</template>