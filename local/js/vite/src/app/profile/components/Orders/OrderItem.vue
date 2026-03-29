<script setup>
import { inject, nextTick, onMounted, onUnmounted, ref, toRef, useTemplateRef } from 'vue';
import ProductCardSmallPhoto from '@/common/components/ProductCardSmallPhoto.vue';
import Swiper from 'swiper';

const props = defineProps({
    order: Object
});
const emits = defineEmits(['cancel'])
const isDressing = inject('isDressing')
const cancel = inject('cancel')
const order = toRef(props.order);
const cancelHandle = () => {
    cancel ? cancel(props.order.id) : null
}
const swiperRef = useTemplateRef('swiperRef');
const swiper = ref(null);
const toggleSwiper = (unMounted = false) => {
    if (swiper.value) {
        swiper.value.destroy();
        swiper.value = null;
    }
    if (unMounted || !swiperRef.value) return;
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
onMounted(async () => {
    await nextTick();
    toggleSwiper()
})
onUnmounted(() => {
    toggleSwiper(true);
})
</script>

<template>
    <div class="profile-orders__item-status-block">
        <span class="profile-orders__item-status">{{ order.status }}</span>
        <span class="profile-orders__item-date">{{ order.date }}</span>
        <span class="profile-orders__item-price" v-html="`- ${order.summary.totalPrice} ₽`"></span>
    </div>
    <span class="profile-orders__item-id">{{ order.id }}</span>
    <div class="profile-orders__item-products" ref="swiperRef">
        <div class="swiper-wrapper">
            <ProductCardSmallPhoto v-for="item in order.items" :key="item.id" :item="item">
            </ProductCardSmallPhoto>
        </div>
    </div>
    <div v-if="!isDressing && !order.isCanceled && !order.isPaid" class="profile-orders__item-buttons">
        <a class="profile-orders__item-pay-btn" v-if="order.paymentLink" :href="order.paymentLink">Оплатить заказ</a>
        <button class="profile-orders__item-cancel-btn" @click.prevent="cancelHandle">Отменить заказ</button>
    </div>
</template>