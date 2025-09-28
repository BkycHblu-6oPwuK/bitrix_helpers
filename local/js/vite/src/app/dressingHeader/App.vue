<script setup>
import { computed, inject, useTemplateRef } from 'vue';
import storeAbout from '@/store/about';
const count = computed(() => storeAbout.getters['dressing/getDressingCount']);
const dressingModal = useTemplateRef('dressingModal');
const pathToDressing = inject('pathToDressing');
const clickHandler = () => {
    if (count.value) {
        window.location.href = pathToDressing;
    } else if (dressingModal.value) {
    }
}
</script>

<template>
    <a @click.prevent="clickHandler" class="header__main-dressing"> <!-- примерочная -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
            <path
                d="M10.8106 6.77484C10.8106 5.12719 12.1893 3.7915 13.8899 3.7915C15.5906 3.7915 16.9693 5.12719 16.9693 6.77484C16.9693 7.94215 16.2773 8.95287 15.2691 9.44298C14.5633 9.78605 13.8899 10.3783 13.8899 11.1433V13.0088M13.8899 13.0088C13.6063 13.026 13.3261 13.1097 13.075 13.26L3.17112 19.8865C1.66307 20.7891 2.32334 23.0415 4.096 23.0415H23.9039C25.6765 23.0415 26.3368 20.7891 24.8287 19.8865L14.9248 13.26C14.609 13.0709 14.2468 12.9872 13.8899 13.0088Z"
                stroke="#0F1523" stroke-width="2" stroke-linecap="round" />
        </svg>
        <div v-if="count" class="header__main-dressing-num"><span>{{ count }}</span></div>
    </a>
    <teleport to="#modal-container-header">
        <div ref="dressingModal" v-if="!count" class="header__dressing_empty-overlay header-modal">
            <div class="header__dressing_empty drag-menu"> <!-- окно пустой примерочной -->
                <div class="header__dressing-image">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80" fill="none">
                        <path
                            d="M30.8877 19.3571C30.8877 14.6495 34.8268 10.8333 39.6859 10.8333C44.545 10.8333 48.4841 14.6495 48.4841 19.3571C48.4841 22.6922 46.507 25.58 43.6263 26.9803C41.6099 27.9605 39.6859 29.6527 39.6859 31.8384V37.1685M39.6859 37.1685C38.8755 37.2175 38.0748 37.4567 37.3576 37.886L9.06064 56.8188C4.75195 59.3979 6.63844 65.8333 11.7032 65.8333H68.297C73.3617 65.8333 75.2482 59.3978 70.9395 56.8188L42.6426 37.886C41.7402 37.3459 40.7055 37.1067 39.6859 37.1685Z"
                            stroke="#111827" stroke-width="1.925" stroke-linecap="round" />
                    </svg>
                </div>
                <span class="header__dressing-text">В примерочной пусто!</span>
                <span class="header__dressing-info">Добавьте товары в примерочную, <br>чтобы записаться на
                    примерку</span>
                <div class="header__mobile-grab"></div>
            </div>
            <div class="m-header-popup-button-container">
                <button class="m-header-popup-button">К покупкам</button>
            </div>
        </div>
    </teleport>
</template>