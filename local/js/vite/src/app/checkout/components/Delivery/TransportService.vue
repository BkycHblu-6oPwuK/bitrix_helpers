<script setup>
import { useStore } from 'vuex';
import RadioCard from '../RadioCard.vue';
import InputLocation from './InputLocation.vue';
import { computed, defineAsyncComponent, onMounted, ref, watch } from 'vue';
import LoadingMapComponent from './Map/LoadingMapComponent.vue';
import { getPvz } from '@/api/order';
import Subtitle from './Subtitle.vue';
import ShowMapBtn from './ShowMapBtn.vue';

const store = useStore();
const selectedId = defineModel('selectedId');
const delivery = computed(() => store.getters.getDelivery);
const deliveries = computed(() => {
    let deliveries = delivery.value.deliveries;
    return Object.values(deliveries).filter(delivery => delivery.isTransport);
});
const showMap = ref(false);
const YandexMapPvz = defineAsyncComponent({
    loader: () => import('./Map/YandexMapPvz.vue'),
    loadingComponent: LoadingMapComponent,
});
const city = computed(() => store.getters.getDelivery.city);
const points = ref(null);

const getPoints = async () => {
    if (!showMap.value) return;
    try {
        const result = await getPvz(delivery.value.selectedId, delivery.value.location, store.getters.getActivePayId);
        points.value = result.points;
    } catch (error) { }
};

const selectPoint = (point) => {
    store.commit('setAddress', point.address);
    store.commit('setDeliveryPvzId', point.id);
};

const showMapHandler = () => {
    showMap.value = !showMap.value;
    points.value = null;
    getPoints();
};

onMounted(() => {
    getPoints();
});

watch(city, () => {
    getPoints();
});
</script>

<template>
    <div class="checkout-transport-service">
        <Subtitle>Где вы хотите получить заказ:</Subtitle>
        <div class="checkout-transport-service__location">
            <InputLocation></InputLocation>
            <ShowMapBtn @showMap="showMapHandler" :showMap="showMap"></ShowMapBtn>
            <YandexMapPvz v-if="showMap && points" :pvzList="points" :center="city" @selectPvz="selectPoint">
            </YandexMapPvz>
        </div>
        <div class="transport_services">
            <Subtitle>Транспортная компания</Subtitle>
            <div class="checkout-radio-group">
                <template v-for="delivery in deliveries" :key="delivery.id">
                    <RadioCard :value="delivery.id" v-model="selectedId">
                        <img v-if="delivery.logotip" :src="delivery.logotip" />
                        <div class="checkout-transport-service_name">
                            <span>{{ delivery.ownName }}</span>
                            <span class="checkout-transport-service_description">{{ delivery.description }}</span>
                        </div>
                    </RadioCard>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.transport_services {
    margin-top: 15px;
}
.checkout-transport-service_name {
    display: flex;
    flex-direction: column;
}
.checkout-transport-service_description {
    font-size: 12px;
    line-height: 16px;
    color: #8D9091;
}
@media (min-width: 768px) {
    .transport_services {
        margin-top: 30px;
    }
}
</style>