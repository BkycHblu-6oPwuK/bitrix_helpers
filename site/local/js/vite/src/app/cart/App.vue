<script setup>
import { computed } from 'vue';
import CardItem from './components/CardItem.vue';
import Total from './components/Total.vue';
import { useStore } from 'vuex';

const store = useStore();
const items = computed(() => store.getters.getItems);
</script>

<template>
    <div class="cart-block">
        <h2 class="cart-block__title">Корзина</h2>
        <div class="cart-block__container">
            <div class="cart-block__items">
                <CardItem v-for="item in items" :key="item.id" :item="item"></CardItem>
            </div>
            <Total></Total>
        </div>
    </div>
</template>