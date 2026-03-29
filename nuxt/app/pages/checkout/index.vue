<script setup lang="ts">
const checkoutStore = useCheckoutStore()

onMounted(() => {
    checkoutStore.initialize()
})

useHead({
    title: 'Оформление заказа',
})
</script>

<template>
    <div class="checkout-page">
        <h1>Оформление заказа</h1>
        <div v-if="checkoutStore.loading">
        Загрузка...
        </div>
        <div v-else-if="checkoutStore.checkout">
        <!-- Здесь будет форма оформления заказа -->
        <pre>{{ checkoutStore.checkout }}</pre>
        </div>
        <div v-else>
        Ошибка загрузки данных оформления заказа.
        </div>
    </div>
</template>
