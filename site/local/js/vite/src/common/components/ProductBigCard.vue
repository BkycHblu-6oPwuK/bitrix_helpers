<script setup>
import DressingSmall from './DressingSmall.vue';
import FavouritesSmall from './FavouritesSmall.vue';
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});
</script>
<template>
    <a :href="item.url" class="product-card-main swiper-slide product-vue-card">
        <div class="product-card-main__image">
            <img :src="item.imageSrc">
            <DressingSmall :offerId="item.preselectedOffer ? item.preselectedOffer.id : item.id"></DressingSmall>
            <FavouritesSmall :productId="item.id"></FavouritesSmall>
        </div>

        <div class="product-card-main__info">
            <span v-if="item.preselectedOffer?.price.priceValue || item.price.priceValue"
                class="product-card-main__price"
                v-html="item.preselectedOffer?.price.priceValue ? item.preselectedOffer.price.priceFormatted + ' ₽' : item.price.priceFormatted + ' ₽'"></span>
            <span class="product-card-main__name">{{ item.name }}</span>
            <ul v-if="item.offers" class="product-card-main__sizes">
                <li v-for="offer in item.offers" :key="offer.razmer"
                    :class="{ 'product-card-main__size_disabled': !offer.available }" class="product-card-main__size">
                    {{ offer.razmer }}
                </li>
            </ul>
        </div>
    </a>
</template>