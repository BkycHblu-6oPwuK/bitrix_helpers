<script setup>
import { onMounted } from 'vue';
import ProductCardOrder from '@/common/components/ProductCardOrder.vue';

const props = defineProps({
    order: Object,
    isDressing: {
        type: Boolean,
        default: false,
    }
})
const emits = defineEmits(['cancel'])
onMounted(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
})
</script>

<template>
    <div class="profile-delivered">
        <slot name="back-button"></slot>

        <div class="profile-delivered__status">
            <span class="profile-delivered__status-type">{{ order.status }}</span>
            <span class="profile-delivered__status-date">{{ order.date }}</span>
        </div>
        <div class="profile-delivered__container">
            <ProductCardOrder v-for="item in order.items" :key="item.id" :item="item" :showReview="order.isSuccess"></ProductCardOrder>
        </div>
        <div class="profile-delivered__pay">
            <div class="profile-delivered__pay-type">
                <span>{{ order.isPaid ? 'Оплачено онлайн' : 'Неоплачено' }}</span>
            </div>
            <div class="profile-delivered__result">
                <div class="profile-delivered__result-price-block">
                    <div class="profile-delivered__result-price-key">
                        <span class="profile-delivered__result-name">Товары</span>
                        <span class="profile-delivered__result-quantity">{{ order.summary.totalQuantity }}
                            шт.</span>
                    </div>
                    <span class="profile-delivered__result-price"
                        v-html="`${order.summary.totalItemsPriceFormatted} ₽`"></span>
                </div>
                <div class="profile-delivered__result-discount-block" v-if="order.summary.totalDiscount">
                    <span class="profile-delivered__result-discount-name">Доставка</span>
                    <span class="profile-delivered__result-discount-num"
                        v-html="`${order.summary.deliveryPriceFormatted} ₽`"></span>
                </div>
                <div class="profile-delivered__result-discount-block" v-if="order.summary.totalDiscount">
                    <span class="profile-delivered__result-discount-name">Скидка</span>
                    <span class="profile-delivered__result-discount-num"
                        v-html="`- ${order.summary.totalDiscountFormatted} ₽`"></span>
                </div>
                <div class="profile-delivered__result-total-block">
                    <span class="profile-delivered__result-total-name">Итого</span>
                    <span class="profile-delivered__result-total-price"
                        v-html="`${order.summary.totalPriceFormatted} ₽`"></span>
                </div>
            </div>
        </div>
        <div class="profile-delivered__details">
            <div class="profile-delivered__details-title">
                <span>Детали доставки</span>
            </div>
            <div class="profile-delivered__details-info">
                <div class="profile-delivered__details-item">
                    <span class="profile-delivered__details-key">Получатель</span>
                    <span class="profile-delivered__details-value">{{ order.recipient }}</span>
                </div>
                <div class="profile-delivered__details-item">
                    <span class="profile-delivered__details-key">Способ доставки</span>
                    <span class="profile-delivered__details-value">{{ order.delivery }}</span>
                </div>
                <div class="profile-delivered__details-item">
                    <span class="profile-delivered__details-key">Адрес доставки</span>
                    <span class="profile-delivered__details-value">{{ order.address }}</span>
                </div>
                <div class="profile-delivered__details-item">
                    <span class="profile-delivered__details-key">Номер заказа</span>
                    <span class="profile-delivered__details-value">№ {{ order.id }}</span>
                </div>
            </div>
        </div>
        <div class="profile-unpaid__buttons" v-if="!isDressing && order.isSuccess">
            <a class="profile-unpaid__pay-btn" v-if="order.paymentLink" :href="order.paymentLink">Оплатить</a>
            <button class="profile-unpaid__cancel-btn" @click.prevent="emits('cancel')">Отменить заказ</button>
            <!-- <button class="profile-unpaid__support-btn">Помощь тех. поддержки</button> -->
        </div>
        <!-- <div class="profile-delivered__help" v-else-if="!order.isCanceled">
            <div class="profile-delivered__help-title"><span>Помощь</span></div>
            <button class="profile-delivered__help-submit">Оформить возврат</button>
            <button class="profile-delivered__help-support">Помощь тех. поддержки</button>
        </div> -->
    </div>
</template>