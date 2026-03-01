<script setup>
import { useStore } from 'vuex';
import { computed } from 'vue';

const store = useStore();
const product = computed(() => store.getters.getProduct);
const colors = computed(() => store.getters.getColors);
const changeColor = (id) => {
    store.dispatch('changeColor', id)
}
</script>

<template>
    <div class="product-card-details__color-block">
        <span class="product-card-details__color-title">Выбор цвета</span>
        <div class="product-card-details__color-container">
            <div v-for="color in colors" :key="color.id" class="product-card-details__color product-card-details__color" :class="{
                'product-card-details__color_active': color.id === product.id
            }" @click="changeColor(color.id)">
                <div><img :src="color.file"></div>
            </div>
        </div>
    </div>
</template>