<script setup>
import { useStore } from 'vuex';
import { computed, useTemplateRef, onMounted } from 'vue';
import PhotoBlock from './components/PhotoBlock.vue';
import NameBlock from './components/NameBlock.vue';
import PriceBlock from './components/PriceBlock.vue';
import ColorBlock from './components/ColorBlock.vue';
import SizeBlock from './components/SizeBlock.vue';
import ActionsBlock from './components/ActionsBlock.vue';
import DeliveryBlock from './components/DeliveryBlock.vue';
import DescriptionBlock from './components/DescriptionBlock.vue';
import MobileFixedMenu from './components/MobileFixedMenu.vue';
import storeAbout from '@/store/about';
import ReviewsBlock from './components/Reviews/ReviewsBlock.vue';
const store = useStore();
const product = computed(() => store.getters.getProduct);
const reviewsAvailable = computed(() => store.getters.reviewsAvailable);
const colorsIsAvailable = computed(() => store.getters.colorsIsAvailable);
const isMobile = computed(() => storeAbout.getters.isMobile);
const productCard = useTemplateRef('productCard');
onMounted(() => {
    store.dispatch('addProductVieweded');
    setTimeout(() => {
        if (new URLSearchParams(window.location.search).has('setReviews')) {
            scrollToReviews()
        }
    }, 500);
});
const scrollToReviews = () => {
    const reviewsTitle = document.querySelector('.reviews__title-block');
    if (reviewsTitle) {
        const topPosition = reviewsTitle.getBoundingClientRect().top + window.scrollY - 150;
        window.scrollTo({
            top: topPosition,
            behavior: 'smooth',
        });
    }
}
</script>

<template>
    <div class="section__container">
        <div class="product-card-details" ref="productCard">
            <NameBlock @scrollToReviews="scrollToReviews"></NameBlock>
            <PhotoBlock :productCard="productCard"></PhotoBlock>
            <div class="product-card-details__price-block" v-if="!isMobile">
                <PriceBlock></PriceBlock>
            </div>
            <ColorBlock v-if="colorsIsAvailable"></ColorBlock>
            <SizeBlock v-if="product.offers"></SizeBlock>
            <ActionsBlock></ActionsBlock>
            <DeliveryBlock></DeliveryBlock>
            <DescriptionBlock></DescriptionBlock>
            <MobileFixedMenu v-if="isMobile"></MobileFixedMenu>
        </div>
    </div>
    <ReviewsBlock v-if="reviewsAvailable"></ReviewsBlock>
</template>