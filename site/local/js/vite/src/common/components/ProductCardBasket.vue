<script setup>
import { toRef } from 'vue';
import FavouritesSmall from './FavouritesSmall.vue';
import ProductCardSmallPhoto from './ProductCardSmallPhoto.vue';

const props = defineProps({
    item: Object
});
const item = toRef(() => props.item);
const emits = defineEmits(['delete']);
</script>

<template>
    <div class="product-card-small">
        <ProductCardSmallPhoto :item="item"></ProductCardSmallPhoto>
        <div class="product-card-small__main-block">
            <div class="product-card-small__main">
                <div class="product-card-small__main-info">
                    <span class="product-card-small__main-info-name">{{ item.name }}</span>
                    <span class="product-card-small__main-info-size" v-if="item.razmer">Размер: {{ item.razmer
                    }}</span>
                    <span class="product-card-small__main-info-color" v-if="item.tsvet">Цвет: {{ item.tsvet
                    }}</span>
                </div>
                <div class="product-card-small__main-price-block">
                    <span class="product-card-small__main-price" v-html="`${item.fullPrice} ₽`"></span>
                    <span class="product-card-small__main-discount" v-if="item.discountPercent">-{{
                        item.discountPercent }}%</span>
                    <span class="product-card-small__main-full-price" v-if="item.discountPercent"
                        v-html="`${item.fullOldPrice} ₽`"></span>
                </div>
            </div>
            <div class="product-card-small__icons">
                <FavouritesSmall :productId="item.productId"
                    :classes="{ default: 'product-card-small__icon-favourites', active: 'product-card-small__icon-favourites_active' }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M13.6565 4.28738L13.6567 4.28726C14.1261 3.79777 14.6817 3.4112 15.291 3.14807C15.9002 2.88497 16.552 2.75 17.2095 2.75C17.8669 2.75 18.5187 2.88497 19.1279 3.14807C19.7372 3.4112 20.2929 3.79777 20.7623 4.28726L20.7625 4.28751C21.2322 4.77683 21.6064 5.35948 21.8622 6.00293C22.118 6.64643 22.25 7.33716 22.25 8.03541C22.25 8.73366 22.118 9.42439 21.8622 10.0679C21.6064 10.7113 21.2322 11.294 20.7625 11.7833L20.7624 11.7834L19.6468 12.9462L11.9997 20.9167L4.35266 12.9462L3.23704 11.7834C2.28822 10.7945 1.75 9.44682 1.75 8.03541C1.75 6.62399 2.28822 5.27632 3.23704 4.28738C4.18484 3.2995 5.46355 2.75059 6.78997 2.75059C8.11639 2.75059 9.3951 3.2995 10.3429 4.28738L11.4585 5.45018L11.9997 6.01427L12.5409 5.45018L13.6565 4.28738Z"
                            stroke="#525964" stroke-width="1.5" stroke-linecap="round"></path>
                    </svg>
                </FavouritesSmall>
                <div class="product-card-small__icon-delete" @click.prevent="emits('delete')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M4 6H20M16 6L15.7294 5.18807C15.4671 4.40125 15.3359 4.00784 15.0927 3.71698C14.8779 3.46013 14.6021 3.26132 14.2905 3.13878C13.9376 3 13.523 3 12.6936 3H11.3064C10.477 3 10.0624 3 9.70951 3.13878C9.39792 3.26132 9.12208 3.46013 8.90729 3.71698C8.66405 4.00784 8.53292 4.40125 8.27064 5.18807L8 6M18 6V16.2C18 17.8802 18 18.7202 17.673 19.362C17.3854 19.9265 16.9265 20.3854 16.362 20.673C15.7202 21 14.8802 21 13.2 21H10.8C9.11984 21 8.27976 21 7.63803 20.673C7.07354 20.3854 6.6146 19.9265 6.32698 19.362C6 18.7202 6 17.8802 6 16.2V6M14 10V17M10 10V17"
                            stroke="#525964" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>