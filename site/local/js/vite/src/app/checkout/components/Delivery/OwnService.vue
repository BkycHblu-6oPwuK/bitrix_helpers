<script setup>
import { onMounted, ref, useTemplateRef } from 'vue';
import InputJsLocation from './InputJsLocation.vue';
import ShowMapBtn from './ShowMapBtn.vue';
import Subtitle from './Subtitle.vue';
import Comment from '../Comment.vue';
import YandexMapDeliveryDistance from './Map/YandexMapDeliveryDistance.vue';
import ExtraServices from './ExtraServices/ExtraServices.vue';
import { useEshopLogisticClientData } from '../../helpers';

const showMap = ref(false);
const { mapClientData, getClientMapData } = useEshopLogisticClientData()
const map = useTemplateRef('map')
const props = defineProps({
    deliveryItem: Object
})

const showMapHandler = () => {
    showMap.value = !showMap.value;
}

onMounted(() => {
    getClientMapData();
});

</script>

<template>
    <div class="checkout-transport-service">
        <Subtitle>Адрес доставки:</Subtitle>
        <div class="checkout-transport-service__location">
            <InputJsLocation v-if="map" @selectAddress="map.selectAddress"></InputJsLocation>
            <InputJsLocation v-else></InputJsLocation>
            <ShowMapBtn @showMap="showMapHandler" :showMap="showMap"></ShowMapBtn>
            <YandexMapDeliveryDistance ref="map" v-if="mapClientData" v-show="showMap" :data="mapClientData"></YandexMapDeliveryDistance>
            <Comment></Comment>
            <ExtraServices></ExtraServices>
        </div>
    </div>
</template>