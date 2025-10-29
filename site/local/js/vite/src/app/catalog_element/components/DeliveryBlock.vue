<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';

const store = useStore();
const delivery = computed(() => store.getters.getDelivery);
</script>

<template>
    <div class="product-card-details__delivery-container">
        <div class="product-card-details__delivery-item" v-if="delivery.sdekDate">
            <span class="product-card-details__delivery-item-title">Доставка в ПВЗ</span>
            <span class="product-card-details__delivery-item-date">
                {{ delivery.sdekDate }}
            </span>
        </div>
        <a href="/buyers/how-to-buy/" class="product-card-details__delivery-link">Условия покупки</a>
        <a href="/buyers/guarantees/" class="product-card-details__delivery-link">Гарантия</a>
    </div>
</template>