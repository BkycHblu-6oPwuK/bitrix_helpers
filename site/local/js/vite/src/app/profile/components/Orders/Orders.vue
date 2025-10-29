<script setup>
import { onMounted, onUnmounted } from 'vue';
import OrderItem from './OrderItem.vue'

const props = defineProps({
    orders: Object,
    title: String,
    to: String,
});

let isRequest = false;
const handleScroll = async () => {
    if (isRequest) return;

    const scrollY = window.scrollY;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    if (scrollY + windowHeight >= documentHeight - 200) {
        isRequest = true;
        try {
            await emits('showMore');
        } finally {
            isRequest = false;
        }
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="profile-orders">
        <h3 class="profile__subtitle">{{ title }}</h3>
        <div class="profile-orders__container">
            <template v-for="order in orders" :key="order.id">
                <RouterLink :to="{ name: to, params: { id: order.id } }"
                    class="profile-orders__item order-delivered">
                    <OrderItem :order="order"></OrderItem>
                </RouterLink>
            </template>
        </div>
    </div>
</template>
