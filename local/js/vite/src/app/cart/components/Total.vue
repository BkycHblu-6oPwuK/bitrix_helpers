<script setup>
import { computed, ref, watch } from 'vue';
import { useStore } from 'vuex';
import { debounce } from '@/common/js/helpers';

const store = useStore();
const summary = computed(() => store.getters.getSummary);
const promocode = ref('');
const checkoutUrl = store.getters.getCheckoutUrl;
const promocodeHandler = debounce(async () => {
    if(promocode.value){
        store.dispatch('addCoupon', promocode.value);
    }
}, 500);
watch(() => store.getters.getCoupon, (newValue) => {
    promocode.value = newValue;
})
</script>

<template>
    <div class="cart-block__result">
        <div class="cart-block__result-price-block">
            <div>
                <span class="cart-block__result-name">Товары</span>
                <span class="cart-block__result-quantity">{{ summary.totalQuantity }} шт.</span>
            </div>
            <span class="cart-block__result-price" v-html="`${summary.totalPriceFormatted} ₽`"></span>
        </div>
        <div class="cart-block__result-discount-block" v-if="summary.totalDiscount">
            <span class="cart-block__result-discount-name">Скидка</span>
            <span class="cart-block__result-discount-num" v-html="`- ${summary.totalDiscountFormatted} ₽`"></span>
        </div>
        <div class="cart-block__result-total-block">
            <span class="cart-block__result-total-name">Итого</span>
            <span class="cart-block__result-total-price" v-html="`${summary.totalPriceFormatted} ₽`"></span>
        </div>
        <div class="cart-block__result-promo-input-block">
            <input type="text" v-model="promocode" @input="promocodeHandler" placeholder="Ваш промокод" class="cart-block__result-promo-input">
            <div class="cart-block__result-promo-input-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                    <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                </svg>
            </div>
            <div class="cart-block__result-promo-input-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                        stroke-linecap="square" />
                </svg>
            </div>
        </div>
        <a :href="checkoutUrl"><button class="cart-block__submit-btn">Перейти к оформлению</button></a>
    </div>
</template>