<script setup>
import { computed, inject, useTemplateRef } from 'vue';
import { headerModals } from '@/common/js/variables';
import { checkModal, isHoveredWithOverlay, toggleModalVisibility } from '@/common/js/helpers';
import store from '@/store/about';
const pathToBasket = inject('pathToBasket');
const count = computed(() => store.getters['basket/getCount']);
const basketModal = useTemplateRef('basketModal');
const clickHandler = () => {
    if (count.value) {
        window.location.href = pathToBasket;
    } else if (basketModal.value) {
        checkModal(headerModals, basketModal.value)
        toggleModalVisibility(basketModal.value)
        isHoveredWithOverlay(basketModal.value, basketModal.value)
    }
}
</script>

<template>
    <a @click.prevent="clickHandler" class="header__main-cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
            <path
                d="M20.7267 11.5339L14 3.78174L7.27328 11.5339M20.7267 11.5339H25.2212L21.763 24.2188H6.1903L2.77881 11.5339H7.27328M20.7267 11.5339H7.27328"
                stroke="#0F1523" stroke-width="2" stroke-linejoin="round" />
        </svg>
        <div v-if="count" class="header__main-cart-num"><span>{{ count }}</span></div>
    </a>
    <teleport to="#modal-container-header">
        <div ref="basketModal" v-if="!count" class="header__cart_empty-overlay header-modal">
            <div class="header__cart_empty drag-menu"> <!-- окно пустой корзины -->
                <div class="header__cart-image">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80" fill="none">
                        <path
                            d="M59.2192 32.9538L40 10.8047L20.7808 32.9538M59.2192 32.9538H72.0605L62.1801 69.1962H17.6866L7.93945 32.9538H20.7808M59.2192 32.9538H20.7808"
                            stroke="black" stroke-width="1.75" stroke-linejoin="round" />
                    </svg>
                </div>
                <span class="header__cart-text">В корзине пусто!</span>
                <span class="header__cart-info">Добавьте товары в корзину, <br>чтобы продолжить покупку</span>
                <div class="header__mobile-grab"></div>
            </div>
            <div class="m-header-popup-button-container">
                <button class="m-header-popup-button">К покупкам</button>
            </div>
        </div>
    </teleport>
</template>