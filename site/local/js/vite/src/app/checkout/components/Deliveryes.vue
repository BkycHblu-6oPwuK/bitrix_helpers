<script setup>
import { computed, inject } from 'vue';
import { useStore } from 'vuex';
import Subtitle from './Subtitle.vue';
import Shop from './Delivery/Shop.vue';
import RadioCard from './RadioCard.vue';
import TransportService from './Delivery/TransportService.vue';
import OwnService from './Delivery/OwnService.vue';
import { getImagePublicPath } from '@/common/js/helpers';
import { isOwnDelivery, isShopDelivery } from '@/store/checkout/helpers';

const store = useStore();
const isMobile = inject('isMobile');
const delivery = computed(() => store.getters.getDelivery);
const selectedDeliveryItem = computed(() => store.getters.getSelectedDeliveryItem);
const selectedId = computed({
    get: () => delivery.value.selectedId,
    set: (val) => {
        if (delivery.value.selectedId === val) return;
        store.commit('setDeliverySelectedId', val);
    }
});
const changeDeliveryHandler = () => {
        store.dispatch('resetLocation');
    store.dispatch('refresh');
}
const selectedTransportDelivery = computed(() => store.getters.selectedTransportDelivery);
const selectedOwnDelivery = computed(() => store.getters.selectedOwnDelivery);
const selectedShopDelivery = computed(() => store.getters.selectedShopDelivery);

const selectTransport = () => {
    for (let key in delivery.value.deliveries) {
        const item = delivery.value.deliveries[key];
        if (item.isTransport) {
            selectedId.value = item.id;
            return;
        }
    }
};
</script>

<template>
    <div class="checkout-info__delivery" :key="selectedId">
        <Subtitle>2. Где и как вы хотите получить заказ?</Subtitle>
        <div class="checkout-radio-group checkout-delivery__radio-group">
            <template v-for="deliveryItem in delivery.deliveries" :key="deliveryItem.id">
                <RadioCard v-if="!deliveryItem.isTransport" :value="deliveryItem.id" v-model="selectedId" @change="changeDeliveryHandler">
                    <img v-if="deliveryItem.logotip" :src="deliveryItem.logotip" />
                    <span>{{ deliveryItem.name }}</span>
                </RadioCard>
                <template v-if="isMobile">
                    <Shop v-if="selectedId === deliveryItem.id && isShopDelivery(deliveryItem)" />
                    <OwnService v-if="selectedId === deliveryItem.id && isOwnDelivery(deliveryItem)"
                        :deliveryItem="deliveryItem" />
                </template>
            </template>

            <RadioCard :checked="selectedTransportDelivery" @click="selectTransport" @change="changeDeliveryHandler">
                <img :src="getImagePublicPath('/checkout/truck.png')" />
                <span>Транспортной компанией</span>
            </RadioCard>
            <template v-if="isMobile && selectedTransportDelivery">
                <TransportService v-model:selectedId="selectedId" />
            </template>
        </div>

        <template v-if="!isMobile">
            <Shop v-if="selectedShopDelivery" />
            <OwnService v-if="selectedOwnDelivery" :deliveryItem="selectedDeliveryItem" />
            <TransportService v-if="selectedTransportDelivery" v-model:selectedId="selectedId" />
        </template>
    </div>
</template>
