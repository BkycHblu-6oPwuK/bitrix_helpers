<script setup>
import { useStore } from 'vuex';
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.css';
import { computed, nextTick, onMounted, onUnmounted, ref, toRef, useTemplateRef, watch } from 'vue';
import Swiper from 'swiper';
import storeAbout from '@/store/about';

const props = defineProps({
    productCard: Object
})
const productCard = toRef(() => props.productCard);
const observedHeight = ref('auto');
let observer = null;
const store = useStore();

const isMobile = computed(() => storeAbout.getters.isMobile);
const product = computed(() => store.getters.getProduct);
const propertiesDefault = computed(() => store.getters.getPropertiesDefault);
const sizesText = computed(() => {
    let text = '';
    if (propertiesDefault.value.RAZMER_MODELI_NA_FOTO) {
        text += `Размер на модели: ${propertiesDefault.value.RAZMER_MODELI_NA_FOTO.VALUE} RUS.`
    }
    if (propertiesDefault.value.ROST_MODELI_NA_FOTO) {
        text += ` Параметры модели: рост ${propertiesDefault.value.ROST_MODELI_NA_FOTO.VALUE} см`;
    }
    return text;
})
const photos = computed(() => {
    return [
        product.value.imageSrc,
        ...Object.values(product.value.morePhoto ?? {})
    ]
})

const swiperRef = useTemplateRef('swiperRef');
const swiper = ref(null);
let lightbox;

const toggleSwiper = () => {
    if (swiper.value) {
        swiper.value.destroy(true, true);
        swiper.value = null;
    }
    if (!swiperRef.value) return;
    swiper.value = new Swiper(swiperRef.value, {
        spaceBetween: 12,
        slidesPerView: 'auto',
        slidesPerGroup: 1,
        freeMode: true,
    });
}
const initLightbox = () => {
    if (lightbox) {
        lightbox.destroy();
    }
    lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        zoomable: true,
        autoplayVideos: false,
    });
}
onMounted(async () => {
    await nextTick();
    toggleSwiper();
    initLightbox();
    if (productCard.value) {
        observer = new MutationObserver(() => {
            observedHeight.value = `${productCard.value.clientHeight}px`;
        });

        observer.observe(productCard.value, { attributes: true, childList: true, subtree: true });
    }
});
onUnmounted(() => {
    if (observer) observer.disconnect();
});
const height = computed(() => {
    if (!productCard.value || isMobile.value) return 'auto';
    return observedHeight.value;
});

watch(() => store.getters.getProductId, async () => {
    await nextTick();
    toggleSwiper();
    initLightbox();
})
</script>

<template>
    <div class="product-card-details__photo-block" :style="{
        'height': height
    }">
        <div class="product-card-details__photo-block-sticky">
            <div class="product-card-details__photo-container" ref="swiperRef">
                <div class="swiper-wrapper">
                    <div v-for="(photo, key) in photos" :key="key" class="product-card-details__photo swiper-slide">
                        <a :href="photo" class="glightbox" :data-gallery="'product-photos'">
                            <img :src="photo" alt="Фото товара">
                        </a>
                    </div>
                </div>
            </div>
            <span class="product-card-details__photo-info">{{ sizesText }}</span>
        </div>
    </div>
</template>