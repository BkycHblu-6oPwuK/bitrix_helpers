<script setup lang="ts">
const basketStore = useBasketStore()

onMounted(async () => {
    if (!basketStore.initialized) {
        await basketStore.fetchBasket()
    }
})

useHead({
    title: 'Корзина',
})

const clearBasket = async () => {
    if (confirm('Вы уверены, что хотите очистить корзину?')) {
        await basketStore.clearBasket()
    }
}
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Корзина</h1>
            
            <UButton
                v-if="basketStore.hasItems"
                color="error"
                variant="ghost"
                icon="i-heroicons-trash"
                label="Очистить корзину"
                @click="clearBasket"
            />
        </div>

        <div v-if="basketStore.loading && !basketStore.initialized" class="text-center py-12">
            <UIcon name="i-heroicons-arrow-path" class="animate-spin text-4xl text-gray-400" />
            <p class="mt-4 text-gray-600">Загрузка корзины...</p>
        </div>

        <div v-else-if="basketStore.isEmpty" class="text-center py-12">
            <UIcon name="i-heroicons-shopping-cart" class="text-6xl text-gray-400 mb-4" />
            <h2 class="text-2xl font-semibold text-gray-600 mb-2">Корзина пуста</h2>
            <p class="text-gray-500 mb-6">Добавьте товары, чтобы оформить заказ</p>
            <UButton
                to="/catalog"
                color="primary"
                size="lg"
                label="Перейти в каталог"
            />
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <BasketItem
                    v-for="item in basketStore.items"
                    :key="item.id"
                    :item="item"
                />
            </div>

            <div class="space-y-4">
                <BasketCouponForm />
                
                <BasketSummary />
            </div>
        </div>
    </div>
</template>
