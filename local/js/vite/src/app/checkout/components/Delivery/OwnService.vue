<script setup>
import { onMounted, ref, useTemplateRef } from 'vue';
import InputJsLocation from './InputJsLocation.vue';
import ShowMapBtn from './ShowMapBtn.vue';
import Subtitle from './Subtitle.vue';
import { getClient } from '@/api/order';
import Commnet from '../Commnet.vue';
import YandexMapDeliveryDistance from './Map/YandexMapDeliveryDistance.vue';
import ExtraServcies from './ExtraServices/ExtraServcies.vue';

const showMap = ref(false);
const mapData = ref(null);
const map = useTemplateRef('map')
const props = defineProps({
    deliveryItem: Object
})

const getMapData = async () => {
    try {
        const result = await getClient();
        mapData.value = result.data;
    } catch (error) {
        console.error(error)
     }
}

const showMapHandler = () => {
    showMap.value = !showMap.value;
}

onMounted(() => {
    getMapData()
});

</script>

<template>
    <div class="checkout-transport-service">
        <Subtitle>Адрес доставки:</Subtitle>
        <div class="checkout-transport-service__location">
            <InputJsLocation v-if="map" @selectAddress="map.selectAddress"></InputJsLocation>
            <InputJsLocation v-else></InputJsLocation>
            <ShowMapBtn @showMap="showMapHandler" :showMap="showMap"></ShowMapBtn>
            <YandexMapDeliveryDistance ref="map" v-if="mapData" v-show="showMap" :data="mapData"></YandexMapDeliveryDistance>
            <Commnet></Commnet>
            <ExtraServcies></ExtraServcies>
        </div>
    </div>
</template>