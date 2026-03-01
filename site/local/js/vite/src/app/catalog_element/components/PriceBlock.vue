<script setup>
import { useStore } from 'vuex';
import { computed } from 'vue';
import storeAbout from '@/store/about';

const store = useStore();
const isMobile = computed(() => storeAbout.getters.isMobile);
const price = computed(() => store.getters.getPrice);
</script>

<template>
    <div :class="{
        'product-card-details__price': !isMobile,
        'product-card-details__mobile-price': isMobile
    }">
        <span :class="{
            'product-card-details__price-result': !isMobile,
            'product-card-details__mobile-price-result': isMobile
        }" v-html="`${price.priceFormatted}  ₽`"></span>
        <span :class="{
            'product-card-details__price-current': !isMobile,
            'product-card-details__mobile-price-current': isMobile
        }" v-if="price.discountPercent" v-html="`${price.oldPriceFormatted}  ₽`"></span>
        <span :class="{
            'product-card-details__price-discount': !isMobile,
            'product-card-details__mobile-price-discount': isMobile
        }" v-if="price.discountPercent">-{{price.discountPercent}}%</span>
    </div>
    <!-- <span :class="{
        'product-card-details__price-credit': !isMobile,
        'product-card-details__mobile-price-credit': isMobile
    }">от 15 000 ₽ в месяц</span> -->
</template>