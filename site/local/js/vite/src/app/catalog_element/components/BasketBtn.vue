<script setup>
import { computed } from 'vue';
import storeAbout from '@/store/about';
import { useStore } from 'vuex';

const store = useStore();
const isMobile = computed(() => storeAbout.getters.isMobile);
const productId = computed(() => store.getters.getOfferId);
const addBasket = () => {
    storeAbout.dispatch('basket/addBasket', productId.value)
}
</script>
<template>
    <button @click="addBasket" type="submit" :class="{
        'product-card-details__btn-cart': !isMobile,
        'product-card-details__mobile-btn-cart': isMobile,
    }">Добавить в корзину</button>
</template>