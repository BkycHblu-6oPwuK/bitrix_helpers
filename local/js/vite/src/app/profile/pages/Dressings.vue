<script setup>
import { useStore } from 'vuex';
import { computed, provide } from 'vue';
import Orders from '../components/Orders/Orders.vue';

const store = useStore();
store.dispatch('dressing/initialize');

const orders = computed(() => store.getters['dressing/getOrders']);
const showMore = async () => store.dispatch('dressing/showMore');
provide('isDressing', true);
</script>

<template>
    <Orders :orders="orders" :title="'Мои примерки'" :to="'dressing'" @showMore="showMore"></Orders>
</template>
