<script setup>
import { nextTick, ref, computed, useTemplateRef } from 'vue';
import { catalogPageUrl, headerModals } from '@/common/js/variables';
import { debounce } from '@/common/js/helpers';
import { search } from '@/api/catalog';
import Swiper from 'swiper';
import storeAbout from '@/store/about';
const searchModal = useTemplateRef('searchModal');
const searchCatalog = useTemplateRef('searchCatalog');
const hints = ref(null);
const isMobile = computed(() => storeAbout.getters.isMobile)
const products = ref(null);
const query = ref('');
const catalogUrl = computed(() => `${catalogPageUrl}?q=${encodeURIComponent(query.value)}`)
const clickHandler = () => {

}
const queryHandler = debounce(async () => {
    try {
        const result = await search(query.value);
        if (result.data.success) {
            hints.value = result.data.data.hints;
            products.value = result.data.data.products;
        } else {
            hints.value = null;
            products.value = null;
        }
        await nextTick();
        toggleSwiper();
    } catch (error) {
        console.error(error);
    }
}, 500);
const swiperRef = useTemplateRef('swiperRef');
const swiper = ref(null);
const toggleSwiper = () => {
    if (swiper.value) {
        swiper.value.destroy();
        swiper.value = null;
    }
    if (!swiperRef.value) return;
    swiper.value = new Swiper(swiperRef.value, {
        spaceBetween: 20,
        slidesPerView: 'auto',
        slidesPerGroup: 1,
        freeMode: true,
        breakpoints: {
            320: {
                spaceBetween: 12,
            },
            768: {
                spaceBetween: 20,
            },
            1320: {},
        }
    })
}
const goToSearch = () => {
    window.location.href = catalogUrl.value;
}
</script>

<template>
    <div @click="clickHandler" :class="{
        'header__main-search': !isMobile,
        'm-header__top-search': isMobile,
    }">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
            <path
                d="M12.8333 22.1667C17.988 22.1667 22.1667 17.988 22.1667 12.8333C22.1667 7.67868 17.988 3.5 12.8333 3.5C7.67868 3.5 3.5 7.67868 3.5 12.8333C3.5 17.988 7.67868 22.1667 12.8333 22.1667Z"
                stroke="#0F1523" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M24.5001 24.4998L19.425 19.4248" stroke="#0F1523" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </div>
    <teleport to="#modal-container-header">
        <div ref="searchModal" class="header__main-search-catalog-container header-modal">
            <div ref="searchCatalog" class="header__main-search-catalog hover-menu-search">
                <input class="header__main-search-input" @input="queryHandler" @keyup.enter="goToSearch" v-model="query"
                    placeholder="Хочу найти">
                <div class="header__main-search-content">
                    <div v-if="hints" class="header__main-catalog-list">

                        <div class="header__main-catalog-col header__main-catalog-col_wo-border">
                            <a :href="catalogUrl" class="header__main-catalog-item">Все позиции</a>
                            <a v-for="hint in hints" :key="hint.id" :href="hint.url"
                                class="header__main-catalog-item">{{ hint.name }}</a>
                        </div>

                    </div>
                    <div v-if="products" class="header__main-search-example" ref="swiperRef">
                        <div class="swiper-wrapper">
                            <a v-for="product in products" :key="product.id" :href="product.url"
                                class="header__main-catalog-example-item swiper-slide">
                                <div class="header__main-catalog-example-item-photo">
                                    <img :src="product.imageSrc">
                                </div>
                                <span>{{ product.name }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>