<script setup lang="ts">
const basketStore = useBasketStore()

const couponCode = ref('')
const loading = ref(false)

const applyCoupon = async () => {
    if (!couponCode.value.trim()) return

    loading.value = true
    try {
        await basketStore.applyCoupon(couponCode.value.trim())
        couponCode.value = ''
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <UCard>
        <template #header>
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Промокод</h3>
                <UBadge v-if="basketStore.coupon?.isActive" color="success" variant="soft">
                    Применен
                </UBadge>
            </div>
        </template>

        <div v-if="basketStore.coupon?.isActive" class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/10 rounded-lg">
                <span class="font-medium text-green-700 dark:text-green-400">
                    {{ basketStore.coupon.code }}
                </span>
                <UIcon name="i-heroicons-check-circle" class="text-green-600 text-xl" />
            </div>
        </div>

        <div v-else class="space-y-3">
            <UInput
                v-model="couponCode"
                placeholder="Введите промокод"
                size="lg"
                @keyup.enter="applyCoupon"
            />
            
            <UButton
                block
                color="primary"
                variant="outline"
                label="Применить"
                :loading="loading"
                :disabled="!couponCode.trim()"
                @click="applyCoupon"
            />
        </div>
    </UCard>
</template>
