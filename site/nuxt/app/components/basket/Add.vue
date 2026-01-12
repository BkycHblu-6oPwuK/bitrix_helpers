<!--
  Универсальный компонент для добавления товара в корзину
  Показывает кнопку "В корзину" или счетчик количества
-->
<script setup lang="ts">
const props = defineProps<{
    offerId: number
    quantity?: number
    size?: 'sm' | 'md' | 'lg'
    disabled?: boolean
    variant?: 'default' | 'compact' | 'icon'
}>()

const basketStore = useBasketStore()

const itemInBasket = computed(() => 
    basketStore.items.find(item => item.offerId === props.offerId)
)

const isInBasket = computed(() => !!itemInBasket.value)

const localQuantity = ref(props.quantity || 1)

const addToBasket = async () => {
    await basketStore.addItem(props.offerId, localQuantity.value)
}
const increment = async () => {
    if (itemInBasket.value) {
        await basketStore.incrementItem(props.offerId)
    }
}
const decrement = async () => {
    if (itemInBasket.value) {
        await basketStore.decrementItem(props.offerId)
    }
}
const updateQuantity = async (newQuantity: number) => {
    if (itemInBasket.value && newQuantity > 0) {
        await basketStore.updateItem(props.offerId, newQuantity)
    }
}

const sizeClasses = computed(() => {
    const sizes = {
        sm: {
            button: 'px-3 py-1.5 text-sm',
            input: 'w-12 text-sm',
            counter: 'px-2 py-1',
        },
        md: {
            button: 'px-4 py-2 text-base',
            input: 'w-16 text-base',
            counter: 'px-3 py-2',
        },
        lg: {
            button: 'px-6 py-3 text-lg',
            input: 'w-20 text-lg',
            counter: 'px-4 py-2',
        },
    }
    return sizes[props.size || 'md']
})
</script>

<template>
    <div class="add-to-basket">
        <template v-if="variant === 'icon'">
            <UButton
                v-if="!isInBasket"
                icon="i-heroicons-shopping-cart"
                color="primary"
                :size="size"
                :disabled="disabled"
                @click="addToBasket"
            />
            <UBadge v-else color="primary" :size="size">
                {{ itemInBasket?.quantity }}
            </UBadge>
        </template>

        <template v-else-if="variant === 'compact'">
            <UButton
                v-if="!isInBasket"
                icon="i-heroicons-shopping-cart"
                :label="size === 'sm' ? '' : 'В корзину'"
                color="primary"
                :size="size"
                :disabled="disabled"
                @click="addToBasket"
            />
            <div v-else class="flex items-center gap-1 border border-gray-300 dark:border-gray-600 rounded-lg">
                <UButton
                    icon="i-heroicons-minus"
                    color="secondary"
                    variant="ghost"
                    :size="size"
                    :class="sizeClasses.counter"
                    @click="decrement"
                />
                <span class="min-w-[2rem] text-center font-medium">
                    {{ itemInBasket?.quantity }}
                </span>
                <UButton
                    icon="i-heroicons-plus"
                    color="secondary"
                    variant="ghost"
                    :size="size"
                    :class="sizeClasses.counter"
                    @click="increment"
                />
            </div>
        </template>

        <template v-else>
            <div v-if="!isInBasket" class="flex items-center gap-2">
                <div v-if="size !== 'sm'" class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
                    <UButton
                        icon="i-heroicons-minus"
                        color="secondary"
                        variant="ghost"
                        :size="size"
                        :disabled="localQuantity <= 1"
                        @click="localQuantity--"
                    />
                    <UInput
                        v-model.number="localQuantity"
                        type="number"
                        min="1"
                        :class="sizeClasses.input"
                        class="text-center border-0"
                    />
                    <UButton
                        icon="i-heroicons-plus"
                        color="secondary"
                        variant="ghost"
                        :size="size"
                        @click="localQuantity++"
                    />
                </div>
                
                <UButton
                    icon="i-heroicons-shopping-cart"
                    label="В корзину"
                    color="primary"
                    :size="size"
                    :class="sizeClasses.button"
                    :disabled="disabled"
                    class="flex-1"
                    @click="addToBasket"
                />
            </div>

            <div v-else class="flex items-center gap-2">
                <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
                    <UButton
                        icon="i-heroicons-minus"
                        color="secondary"
                        variant="ghost"
                        :size="size"
                        @click="decrement"
                    />
                    <UInput
                        :model-value="itemInBasket?.quantity"
                        type="number"
                        min="1"
                        :class="sizeClasses.input"
                        class="text-center border-0"
                        @blur="updateQuantity(Number($event.target.value))"
                    />
                    <UButton
                        icon="i-heroicons-plus"
                        color="secondary"
                        variant="ghost"
                        :size="size"
                        @click="increment"
                    />
                </div>
                
                <UButton
                    icon="i-heroicons-check"
                    label="В корзине"
                    color="success"
                    :size="size"
                    variant="soft"
                    class="flex-1"
                    disabled
                />
            </div>
        </template>
    </div>
</template>