<script setup>
import { computed, defineAsyncComponent, nextTick, ref, useTemplateRef, watch } from 'vue';
import { useStore } from 'vuex';
import LoadingMapComponent from './Map/LoadingMapComponent.vue';
import { getImagePublicPath, wait } from '@/common/js/helpers';

const store = useStore();
const storeSelectedDelivery = computed(() => store.getters.getSelectedDeliveryItem)
const storeSelectedItem = computed(() => {
    return storeSelectedDelivery.value.storeList[store.getters.getDelivery.storeSelectedId] || null
});
const showMap = ref(false);
const showPhone = ref(false);
const yandexMap = useTemplateRef('yandexMap')
const YandexMapPvz = defineAsyncComponent({
    loader: () => import('./Map/YandexMapPvz.vue'),
    loadingComponent: LoadingMapComponent,
});

const selectShop = (shop) => {
    store.commit('setAddress', shop.address);
    store.commit('setSelectedStoreId', shop.id);
}

const showMapHandler = () => {
    showMap.value = !showMap.value
}

const buildRouteToPvz = async () => {
    let location = storeSelectedItem.value.location
    if (!location) return
    if (!showMap.value) {
        showMapHandler();
    }
    await nextTick();
    await wait(() => yandexMap.value && yandexMap.value.buildRouteToPvz)

    yandexMap.value.buildRouteToPvz([location.latitude, location.longitude]);
}

watch(storeSelectedItem, (newVal) => {
    if (newVal) {
        store.commit('setAddress', newVal.address);
    }
}, { immediate: true });
</script>

<template>
    <div class="checkout-info__pickup-content" v-if="storeSelectedItem">
        <div class="pickup-content-row">
            <img :src="getImagePublicPath('/yamap/item_home.svg')">
            <div class="pickup-content-text">
                <span class="checkout-info__pickup-content-label">{{ storeSelectedItem.name }}</span>
                <div class="checkout-info__pickup-content-address">
                    <span>{{ storeSelectedItem.address }}</span>
                </div>
            </div>
        </div>
        <div class="pickup-content-bottom">
            <div>
                <div class="pickup-content-row">
                    <img :src="getImagePublicPath('/yamap/item_time.svg')">
                    <div class="checkout-info__pickup-content-label">Часы работы: <span>{{
                        storeSelectedItem.schedule }}</span></div>
                </div>
                <div class="pickup-content-row">
                    <img :src="getImagePublicPath('/yamap/item_pin.svg')">
                    <div class="checkout-info__pickup-content-label"><a @click="showMapHandler">{{showMap ? 'Скрыть карту' : 'Показать на карте'}}</a>
                    </div>
                </div>
                <div class="pickup-content-row">
                    <img :src="getImagePublicPath('/yamap/item_route.svg')">
                    <div class="checkout-info__pickup-content-label"><a @click="buildRouteToPvz">Построить маршрут</a>
                    </div>
                </div>
            </div>
            <div>
                <div class="pickup-content-row">
                    <img :src="getImagePublicPath('/yamap/item_mail.svg')">
                    <div class="checkout-info__pickup-content-label"><a :href="`mailto:${storeSelectedItem.email}`">{{
                        storeSelectedItem.email }}</a></div>
                </div>
                <div class="pickup-content-row">
                    <img :src="getImagePublicPath('/yamap/item_phone.svg')">
                    <div class="checkout-info__pickup-content-label">
                        <a @click="showPhone = true" v-if="!showPhone">Показать телефон</a>
                        <a v-if="showPhone" :href="`tel:${storeSelectedItem.phone}`">{{ storeSelectedItem.phone }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <YandexMapPvz ref="yandexMap" v-if="showMap && storeSelectedItem && storeSelectedDelivery.storeList"
        :pvzList="storeSelectedDelivery.storeList" :center="storeSelectedItem.location" @selectPvz="selectShop">
    </YandexMapPvz>
</template>

<style scoped>
.checkout-info__pickup-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pickup-content-row {
    display: flex;
    gap: 15px;
    align-items: start;
}

.pickup-content-bottom {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pickup-content-bottom>div {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pickup-content-row img {
    width: 18px;
    height: 20px;
    object-fit: contain;
}

.pickup-content-text {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.checkout-info__pickup-content-label {
    font-weight: 700;
    font-size: 14px;
    margin-top: 2px;
}

.checkout-info__pickup-content-label span {
    font-weight: 400;
}

.checkout-info__pickup-content-label a {
    text-decoration: underline;
    cursor: pointer;
    color: inherit;
}

.checkout-info__pickup-content-address {
    font-size: 14px;
}

@media (min-width: 768px) {
    .checkout-info__pickup-content {
        margin-top: 30px;
    }

    .pickup-content-bottom {
        flex-direction: row;
        gap: 30px;
    }
}
</style>