<script setup>
import { useStore } from "vuex";
import RadioCard from "../RadioCard.vue";
import {
  computed,
  defineAsyncComponent,
  onMounted,
  ref,
  useTemplateRef,
  watch,
} from "vue";
import LoadingMapComponent from "./Map/LoadingMapComponent.vue";
import { getEshopLogisticPvzList } from "@/api/order";
import Subtitle from "./Subtitle.vue";
import ShowMapBtn from "./ShowMapBtn.vue";
import Checkbox from "../Checkbox.vue";
import { useEshopLogisticClientData } from "../../helpers";
import InputJsLocation from "./InputJsLocation.vue";
import YandexMapDeliveryDistance from "./Map/YandexMapDeliveryDistance.vue";
import { debounce } from "@/common/js/helpers";

const store = useStore();
const selectedId = defineModel("selectedId");
const delivery = computed(() => store.getters.getDelivery);
const selectedDeliveryItemIsDoor = ref(
  store.getters.getSelectedDeliveryItem?.isDoor === true
);
const deliveries = computed(() => {
  let deliveries = Object.values(delivery.value.deliveries);
  return deliveries.filter((delivery) => {
    if (deliveryMarkShow.value) {
      return delivery.isDoor;
    }
    return !delivery.isDoor && delivery.isTransport;
  });
});
const showMap = ref(false);
const YandexMapPvz = defineAsyncComponent({
  loader: () => import("./Map/YandexMapPvz.vue"),
  loadingComponent: LoadingMapComponent,
});
const city = computed({
  get: () => store.getters.getDelivery.city,
  set: (val) => store.commit("setCity", val),
});
const points = ref(null);
const deliveryMarkShow = ref(selectedDeliveryItemIsDoor.value);
const mapDistance = useTemplateRef("mapDistance");
const { mapClientData, getClientMapData } = useEshopLogisticClientData();

const initPoints = async () => {
  if (points.value !== null) return;
  try {
    const result = await getEshopLogisticPvzList(
      delivery.value.selectedId,
      delivery.value.location,
      store.getters.getActivePayId
    );
    points.value = result.points;
  } catch (error) {}
};

const initClientMapData = async () => {
  if (mapClientData.value !== null) return;
  await getClientMapData();
  if (mapClientData.value) {
    mapClientData.value.restrictArea = null;
    mapClientData.value.maxDistance = 0;
    mapClientData.value.maxDuration = 0;
  }
};

const selectPoint = (point) => {
  store.commit("setAddress", point.address);
  store.commit("setDeliveryPvzId", point.id);
};
const showMapHandler = () => (showMap.value = !showMap.value);
const selectPvzLocation = () => {
  points.value = null;
  initPoints();
};
const distanceMapClickHandler = debounce(() => {
  store.dispatch("refresh");
}, 300);
const changeDeliveryHandler = () => {
  if (!deliveryMarkShow.value) {
    store.dispatch("resetLocation");
  }
  store.dispatch("refresh");
};

onMounted(() => {
  if (deliveryMarkShow.value) {
    initClientMapData();
  } else {
    initPoints();
  }
});

watch(deliveryMarkShow, (newValue) => {
  store.dispatch("resetLocation");
  if (deliveries.value[0]) {
    store.commit("setDeliverySelectedId", deliveries.value[0].id);
    store.dispatch("refresh");
  }
});
</script>

<template>
  <div class="checkout-transport-service">
    <Subtitle>Где вы хотите получить заказ:</Subtitle>
    <template v-if="!deliveryMarkShow">
      <div class="checkout-transport-service__location">
        <InputJsLocation @selectAddress="selectPvzLocation"></InputJsLocation>
        <ShowMapBtn @showMap="showMapHandler" :showMap="showMap"></ShowMapBtn>
        <YandexMapPvz
          v-show="showMap"
          :pvzList="points"
          :center="city"
          @selectPvz="selectPoint"
        >
        </YandexMapPvz>
      </div>
    </template>
    <template v-else>
      <div class="checkout-transport-service__location">
        <InputJsLocation
          v-if="mapDistance"
          @selectAddress="mapDistance.selectAddress"
        ></InputJsLocation>
        <InputJsLocation v-else></InputJsLocation>
        <ShowMapBtn @showMap="showMapHandler" :showMap="showMap"></ShowMapBtn>
        <YandexMapDeliveryDistance
          ref="mapDistance"
          v-if="mapClientData"
          v-show="showMap"
          :data="mapClientData"
          :useCalculate="false"
          :useRoute="false"
          :mapClickHandler="distanceMapClickHandler"
        ></YandexMapDeliveryDistance>
      </div>
    </template>

    <div class="transport_services">
      <Subtitle>Транспортная компания</Subtitle>
      <div class="checkout-radio-group">
        <template v-for="delivery in deliveries" :key="delivery.id">
          <RadioCard
            :value="delivery.id"
            v-model="selectedId"
            @change="changeDeliveryHandler"
          >
            <img v-if="delivery.logotip" :src="delivery.logotip" />
            <div class="checkout-transport-service_name">
              <span>{{ delivery.ownName }}</span>
              <span class="checkout-transport-service_description">{{
                delivery.description
              }}</span>
            </div>
          </RadioCard>
        </template>
      </div>
    </div>
    <div class="checkout-form-row">
      <Checkbox v-model="deliveryMarkShow" :label="'Доставка до двери'"></Checkbox>
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
  color: #8d9091;
}
.checkout-form-row {
  margin-top: 15px;
}
@media (min-width: 768px) {
  .transport_services {
    margin-top: 30px;
  }
}
</style>
