<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useStore } from 'vuex';
import OrderDetail from '../components/Orders/OrderDetail.vue';

const store = useStore();
const route = useRoute();
store.dispatch('order/initialize');
const order = computed(() => store.getters['order/getOrderById'](route.params.id));
const cancel = () => store.dispatch('order/cancel', route.params.id)
</script>

<template>
    <OrderDetail v-if="order" :order="order" @cancel="cancel">
        <template #back-button>
            <RouterLink :to="{ name: 'orders' }" class="profile-delivered__back-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                    <path d="M11.25 14L6.75 9.5L11.25 5" stroke="#111827" stroke-width="2" stroke-linecap="square" />
                </svg>
                <span>Вернуться к списку заказов</span>
            </RouterLink>
        </template>
    </OrderDetail>
</template>