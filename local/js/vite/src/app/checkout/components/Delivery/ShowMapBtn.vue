<script setup>
import { getImagePublicPath } from '@/common/js/helpers';

const emits = defineEmits(['showMap']);
const props = defineProps({
    showMap: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <button @click="emits('showMap')" class="checkout-transport-service__map-btn">
        <img :src="getImagePublicPath('/checkout/pin-round.svg')">
        <div>{{ showMap ? 'Скрыть карту' : 'Указать точку на карте' }}</div>
    </button>
</template>

<style scoped>
.checkout-transport-service__map-btn {
    display: flex;
    align-items: center;
    align-self: center;
    gap: 10px;
    padding: 8px 15px;
    border-radius: 5px;
    width: fit-content;
    font-size: 14px;
    border: 1px solid #00A344;
}

@media (min-width: 768px) {
    .checkout-transport-service__map-btn {
        align-self: start;
    }
}
</style>