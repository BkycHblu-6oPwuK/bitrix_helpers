<script setup>
import { useStore } from 'vuex';
import { computed, provide } from 'vue';
import Orders from '../components/Orders/Orders.vue';

const store = useStore();
store.dispatch('order/initialize');

const orders = computed(() => store.getters['order/getOrders']);
const showMore = async () => store.dispatch('order/showMore');
const cancel = (orderId) => store.dispatch('order/cancel', orderId)
provide('isDressing', false);
provide('cancel', cancel);
</script>

<template>
    <Orders :orders="orders" :title="'Мои заказы'" :to="'order'" @showMore="showMore"></Orders>
</template>
