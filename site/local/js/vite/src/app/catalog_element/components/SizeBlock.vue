<script setup>
import { useStore } from 'vuex';
import { computed, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import Swiper from 'swiper';

const store = useStore();
const product = computed(() => store.getters.getProduct);
const propertiesDefault = computed(() => store.getters.getPropertiesDefault);
const swiperRef = useTemplateRef('swiperRef');
const swiper = ref(null);
const toggleSwiper = () => {
    if (swiper.value) {
        swiper.value.destroy();
        swiper.value = null;
    }
    if (!swiperRef.value) return;
    swiper.value = new Swiper(swiperRef.value, {
        spaceBetween: 12,
        slidesPerView: 'auto',
        slidesPerGroup: 1,
        freeMode: true,
        breakpoints: {
            320: {
                spaceBetween: 8,
            },
            768: {
                spaceBetween: 12,
            },
            1320: {},
        }
    });
}
const setOffer = (id) => {
    store.dispatch('setOffer', id);
};

onMounted(async () => {
    await nextTick();
    toggleSwiper();
})
watch(() => store.getters.getProductId, async () => {
    await nextTick();
    toggleSwiper()
})
</script>

<template>
    <div class="product-card-details__size-block">
        <div class="product-card-details__size-title-block">
            <span>Выбор размера</span>
            <a href="/sizing/">Таблица размеров</a>
        </div>
        <div class="product-card-details__size-container" ref="swiperRef">
            <div class="swiper-wrapper">
                <div class="product-card-details__size swiper-slide" v-for="offer in product.offers" :key="offer.id"
                    :class="{
                        'product-card-details__size_active': offer.id == product.preselectedOffer.id
                    }" @click="setOffer(offer.id)">
                    <span>{{ offer.razmer }}</span>
                </div>
            </div>
        </div>
        <a href="#" class="product-card-details__size-link-mobile">Таблица размеров</a>
        <div class="product-card-details__description-mobile">
            <span v-if="propertiesDefault.RAZMER_MODELI_NA_FOTO">Размер на модели: {{
                propertiesDefault.RAZMER_MODELI_NA_FOTO.VALUE }} RUS.</span>
            <span v-if="propertiesDefault.ROST_MODELI_NA_FOTO">Параметры модели: рост {{
                propertiesDefault.ROST_MODELI_NA_FOTO.VALUE }} см</span>
        </div>
    </div>
</template>