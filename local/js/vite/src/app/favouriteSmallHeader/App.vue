<script setup>
import { computed, inject, useTemplateRef } from 'vue';
import store from '@/store/about';
import { checkModal, isHoveredWithOverlay, toggleModalVisibility } from '@/common/js/helpers';
import { headerModals } from '@/common/js/variables';
const count = computed(() => store.getters['favourite/getFavouriteCount']);
const favouritesModal = useTemplateRef('favouritesModal');
const pathToFavourites = inject('pathToFavourites');
const clickHandler = () => {
    if (count.value) {
        window.location.href = pathToFavourites;
    } else if (favouritesModal.value) {
        checkModal(headerModals, favouritesModal.value)
        toggleModalVisibility(favouritesModal.value)
        isHoveredWithOverlay(favouritesModal.value, favouritesModal.value)
    }
}
</script>

<template>
    <a @click.prevent="clickHandler" class="header__main-favourites">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M14 6.99994C11.9007 4.55342 8.39274 3.79733 5.76243 6.03761C3.13213 8.27789 2.76182 12.0235 4.82741 14.6731C6.54481 16.8761 11.7423 21.5223 13.4457 23.0261C13.6363 23.1943 13.7316 23.2784 13.8427 23.3115C13.9397 23.3403 14.0459 23.3403 14.1429 23.3115C14.254 23.2784 14.3493 23.1943 14.5399 23.0261C16.2434 21.5223 21.4408 16.8761 23.1582 14.6731C25.2238 12.0235 24.8987 8.25433 22.2232 6.03761C19.5477 3.82089 16.0993 4.55342 14 6.99994Z"
                stroke="#0F1523" stroke-width="2" stroke-linecap="round" />
        </svg>
        <div v-if="count" class="header__main-favourites-num"><span>{{ count }}</span></div>
    </a>
    <teleport to="#modal-container-header">
        <div ref="favouritesModal" v-if="!count" class="header__favourites_empty-overlay header-modal">
            <div class="header__favourites_empty drag-menu">
                <div class="header__favourites-image">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M40 19.9998C34.0019 13.0098 23.9792 10.8495 16.4641 17.2503C8.94893 23.6511 7.8909 34.3529 13.7926 41.9232C18.6995 48.2173 33.5493 61.4922 38.4163 65.7887C38.9608 66.2694 39.2331 66.5098 39.5506 66.6042C39.8278 66.6866 40.1311 66.6866 40.4083 66.6042C40.7258 66.5098 40.9981 66.2694 41.5426 65.7887C46.4096 61.4922 61.2595 48.2173 66.1663 41.9232C72.068 34.3529 71.1391 23.5838 63.4948 17.2503C55.8505 10.9168 45.9981 13.0098 40 19.9998Z"
                            stroke="black" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
                <span class="header__favourites-text">Нет товаров в избранном!</span>
                <span class="header__favourites-info">Добавьте товары в избранное, <br>чтобы перейти в раздел</span>
                <div class="header__mobile-grab"></div>
            </div>
            <div class="m-header-popup-button-container">
                <button class="m-header-popup-button">К покупкам</button>
            </div>
        </div>
    </teleport>
</template>