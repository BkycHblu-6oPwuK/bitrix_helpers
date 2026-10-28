<!-- Действия: добавление в корзину и в избранное -->
<script setup lang="ts">
const { item, purchasableId, quantity, isAvailable } = useCatalogProduct()
</script>

<template>
    <div class="product-actions">
        <div class="flex items-center gap-4">
            <!-- purchasableId: оффер или (для простого товара) сам товар. Раньше у простых товаров кнопки не было -->
            <BasketAdd v-if="purchasableId" :offer-id="purchasableId" :quantity="quantity" :disabled="!isAvailable"
                size="lg" variant="default" class="flex-1" />

            <Favourite :product-id="item.id" :absolute="false" />
        </div>
    </div>
</template>