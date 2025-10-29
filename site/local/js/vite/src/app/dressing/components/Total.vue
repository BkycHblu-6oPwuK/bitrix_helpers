<script setup>

import { computed } from 'vue';
import { useStore } from 'vuex';

const store = useStore();
const summary = computed(() => store.getters.getSummary);
</script>

<template>
    <div class="dressing-block__result-price-block">
        <div>
            <span class="dressing-block__result-name">Товары</span>
            <span class="dressing-block__result-quantity">{{ summary.totalQuantity }} шт.</span>
        </div>
        <span class="dressing-block__result-price" v-html="`${summary.totalPriceFormatted} ₽`"></span>
    </div>
    <div class="dressing-block__result-discount-block">
        <span class="dressing-block__result-discount-name">Скидка</span>
        <span class="dressing-block__result-discount-num" v-html="`- ${summary.totalDiscountFormatted} ₽`"></span>
    </div>
    <div class="dressing-block__result-total-block">
        <span class="dressing-block__result-total-name">Итого</span>
        <span class="dressing-block__result-total-price" v-html="`${summary.totalPriceFormatted} ₽`"></span>
    </div>
</template>