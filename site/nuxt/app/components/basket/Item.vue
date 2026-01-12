<script setup lang="ts">
import type { BasketItemDTO } from '~/types/basket'

const props = defineProps<{
    item: BasketItemDTO
}>()

const basketStore = useBasketStore()

const quantity = ref(props.item.quantity)

watch(() => props.item.quantity, (newVal) => {
    quantity.value = newVal
})

const updateQuantity = async (newQuantity: number) => {
    await basketStore.updateItem(props.item.offerId, newQuantity)
}

const increment = async () => {
    await basketStore.incrementItem(props.item.offerId)
}

const decrement = async () => {
    await basketStore.decrementItem(props.item.offerId)
}

const remove = async () => {
    await basketStore.removeItem(props.item.offerId)
}
</script>

<template>
    <UCard class="basket-item">
        <div class="flex gap-4">
            <NuxtLink :to="item.url" class="flex-shrink-0">
                <img
                    :src="item.previewPictureSrc || item.detailPictureSrc"
                    :alt="item.name"
                    class="w-24 h-24 object-cover rounded-lg"
                />
            </NuxtLink>

            <div class="flex-1 min-w-0">
                <NuxtLink :to="item.url" class="hover:text-primary-500">
                    <h3 class="text-lg font-semibold truncate">{{ item.name }}</h3>
                </NuxtLink>

                <div class="mt-2 flex items-center gap-2">
                    <div>
                        <span class="text-xl font-bold">{{ item.priceFormatted }}</span>
                        <span v-if="item.oldPrice" class="ml-2 text-sm text-gray-500 line-through" v-html="item.oldPriceFormatted">
                        </span>
                    </div>

                    <UBadge v-if="item.discountPercent" color="error" variant="soft">
                        -{{ item.discountPercent }}%
                    </UBadge>
                </div>

                <div class="mt-4 flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <UButton
                            icon="i-heroicons-minus"
                            size="sm"
                            color="secondary"
                            variant="outline"
                            :disabled="quantity <= 1"
                            @click="decrement"
                        />
                        
                        <UInput
                            v-model.number="quantity"
                            type="number"
                            min="1"
                            class="w-20 text-center"
                            @blur="updateQuantity(quantity)"
                        />
                        
                        <UButton
                            icon="i-heroicons-plus"
                            size="sm"
                            color="secondary"
                            variant="outline"
                            @click="increment"
                        />
                    </div>

                    <div class="ml-auto">
                        <div class="text-right">
                            <div class="text-lg font-bold" v-html="item.fullPriceFormatted"></div>
                            <div v-if="item.fullOldPrice" class="text-sm text-gray-500 line-through" v-html="item.fullOldPriceFormatted">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0">
                <UButton
                    icon="i-heroicons-trash"
                    color="error"
                    variant="ghost"
                    size="sm"
                    @click="remove"
                />
            </div>
        </div>
    </UCard>
</template>