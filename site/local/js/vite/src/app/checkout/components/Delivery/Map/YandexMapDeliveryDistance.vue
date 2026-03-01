<script setup>
import { onMounted, onUnmounted, useTemplateRef, watch } from "vue";
import DeliveryDistanceMapHandler from "@/lib/Location/DeliveryDistanceMapHandler";
import { debounce, getImagePublicPath, wait } from "@/common/js/helpers";
import { useStore } from "vuex";
import ResultError from "@/lib/ResultError";
import { calculateDeliveryDistance } from "@/api/order";
import { showErrorNotification } from "@/app/notify";
import { map } from "jquery";

/**
 * @typedef {Object} Data
 * @property {string|array|object} location
 * @property {array} restrictArea
 * @property {number} maxDistance
 * @property {number} maxDuration
 * @property {any} from
 */
/**
 * @type {{ data: Data }}
 */
const props = defineProps({
  data: {
    type: Object,
  },
  useCalculate: {
    type: Boolean,
    default: true,
  },
  useRoute: {
    type: Boolean,
    default: true,
  },
  mapClickHandler: {
    type: Function,
    default: null,
  },
});
const mapContainer = useTemplateRef("mapContainer");
const store = useStore();
let mapHandler = null;
let isShowPreloader = true;
let isInited = false;

const selectAddress = (address, showPreloader = true) => {
  try {
    isShowPreloader = showPreloader;
    if (!address) {
      store.dispatch("resetLocation");
      mapHandler.removeCurrentRoute();
      mapHandler.removePlacemark();
      calculate(null, null);
      return;
    }
    mapHandler.selectAddress(address);
  } catch (error) {
    let message;
    if (error instanceof ResultError) {
      message = error.message;
    } else {
      message = "Ошибка при поиске координат";
    }
    console.error("Ошибка при геокодировании адреса:", error);
    showErrorNotification(message);
  }
};

const locationSelect = (address, coords) => {
  store.dispatch("resetLocation");
  store.commit("setAddress", address);
  store.commit("setCoordinates", coords);
};

const calculate = debounce(async (distance, duration, price = 0) => {
  const priceExtraService = store.getters.getPriceExtraService;
  if (priceExtraService) {
    try {
      let curPrice = price;
      if (distance && duration) {
        curPrice = await calculateDeliveryDistance(distance, duration);
      }
      if (priceExtraService.price !== curPrice) {
        store.commit("setExtraServiceValue", {
          serviceId: priceExtraService.id,
          value: curPrice,
        });
        store.dispatch("refresh", isShowPreloader);
      }
    } catch (error) {
      if (error instanceof ResultError) {
        showErrorNotification(error.message);
      } else {
        showErrorNotification();
      }
      store.dispatch("resetLocation");
      mapHandler.removeCurrentRoute();
    }
  }
  isShowPreloader = true;
}, 500);

const mapInit = async (newData = null) => {
  mapHandler = new DeliveryDistanceMapHandler(
    mapContainer,
    newData ?? props.data,
    getImagePublicPath("/yamap/point.svg"),
    locationSelect,
    props.useCalculate ? calculate : null
  );
  if (!props.useRoute) {
    mapHandler.disableRouteMaker();
  }
  mapHandler.setMapClickHandler(props.mapClickHandler);
  await mapHandler.initMap();
};

onMounted(async () => {
  await mapInit();
  isInited = true;
});

onUnmounted(() => {
  mapHandler?.destroyMap();
});

defineExpose({
  selectAddress: selectAddress,
});

watch(props.data, async (newData) => {
  await wait(() => isInited);
  if (mapHandler) {
    mapHandler.destroyMap();
    mapHandler = null;
  }
  await mapInit(newData);
});
</script>

<template>
  <div>
    <div ref="mapContainer" class="yandex-maps"></div>
  </div>
</template>

<style scoped>
.select-pvz-btn {
  cursor: pointer;
  padding: 5px 10px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
}
</style>
