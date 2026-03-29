<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';
import Subtitle from './Subtitle.vue';
import RadioCard from './RadioCard.vue';

const store = useStore();
const payments = computed(() => store.getters.getPayments);
const activePay = computed({
    get: () => {
        return store.getters.getActivePay;
    },
    set: (val) => {
        if (store.getters.getActivePay === val) return;
        store.commit('setActivePay', val);
        store.dispatch('refresh');
    }
});
const selectedTransportDelivery = computed(() => store.getters.selectedTransportDelivery);
</script>

<template>
    <div class="checkout-info__pay">
        <Subtitle>{{ selectedTransportDelivery ? '3' : '4' }}. Как вам будет удобнее оплатить заказ?</Subtitle>
        <div class="checkout-radio-group">
            <template v-for="(payment, code) in payments" :key="code">
                <RadioCard :name="'pay-type'" :value="code"
                    v-model="activePay">
                    <img :src="payment.logotip">
                    <span>{{ payment.name }}</span>
                </RadioCard>
            </template>
        </div>
    </div>
</template>