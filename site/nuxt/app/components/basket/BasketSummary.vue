<script setup lang="ts">
const basketStore = useBasketStore()
</script>

<template>
    <UCard>
        <template #header>
            <h3 class="text-xl font-bold">Итого</h3>
        </template>

        <div class="space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Товаров:</span>
                <span class="font-medium">{{ basketStore.totalQuantity }}</span>
            </div>

            <div v-if="basketStore.totalDiscount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Скидка:</span>
                <span class="font-medium text-green-600" v-html="`-${basketStore.totalDiscountFormatted}`">
                </span>
            </div>

            <div v-if="basketStore.coupon?.isActive" class="flex justify-between text-sm">
                <span class="text-gray-600">Купон:</span>
                <UBadge color="success" variant="soft">
                    {{ basketStore.coupon.code }}
                </UBadge>
            </div>

            <UDivider />

            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold">Итого:</span>
                <span class="text-2xl font-bold text-primary-600" v-html="basketStore.totalPriceFormatted">
                </span>
            </div>
        </div>

        <template #footer>
            <UButton
                block
                to="/checkout"
                size="lg"
                color="primary"
                label="Оформить заказ"
                :disabled="basketStore.isEmpty"
            />
        </template>
    </UCard>
</template>
