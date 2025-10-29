<script setup>
import { computed } from 'vue';
import FavouriteBtn from './FavouriteBtn.vue';
import BasketBtn from './BasketBtn.vue';
import DressingBtn from './DressingBtn.vue';
import storeAbout from '@/store/about';

const isMobile = computed(() => storeAbout.getters.isMobile);
</script>

<template>
    <div class="product-card-details__btn-container">
        <BasketBtn v-if="!isMobile"></BasketBtn>
        <div class="product-card-details__btn-block">
            <DressingBtn></DressingBtn>
            <FavouriteBtn></FavouriteBtn>
        </div>
    </div>
</template>