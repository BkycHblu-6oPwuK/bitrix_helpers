<script setup>
import { onMounted, onUnmounted, computed, useTemplateRef, watch } from "vue";
import { wait } from "@/common/js/helpers";
import YandexMapPvzHandler from "@/lib/Location/YandexMapPvzHandler";

const props = defineProps({
  pvzList: [Array, Object],
  center: [Object, String],
});
const emits = defineEmits(["selectPvz"]);
const mapContainer = useTemplateRef("mapContainer");
const normalizedPvzList = computed(() => {
  if (Array.isArray(props.pvzList)) {
    return props.pvzList;
  } else if (typeof props.pvzList === "object" && props.pvzList !== null) {
    return Object.values(props.pvzList);
  }
  return [];
});
let mapHandler = null;
let isInited = false;

const buildRouteToPvz = (pvzCoords) => mapHandler?.buildRouteToUserCoords(pvzCoords);
const selectPvz = (pvz) => emits("selectPvz", pvz);
const mapInit = () => {
  if (mapHandler?.map) {
    mapHandler.updatePlacemarks(normalizedPvzList.value)
  } else {
    mapHandler = new YandexMapPvzHandler(mapContainer, props.center, selectPvz);
    mapHandler
      .initMap(normalizedPvzList.value)
      .then(() => isInited = true)
      .catch((error) => {
        console.error("Ошибка при создании карты:", error);
      });
  }
};

onMounted(() => {
  mapInit();
});

onUnmounted(() => {
  mapHandler?.destroyMap();
});

watch(normalizedPvzList, (newVal) => {
  wait(() => isInited).then(() => mapInit())
});
watch(() => props.center, (newVal) => {
  mapHandler?.setMapCenter(newVal)
});

defineExpose({
  buildRouteToPvz: buildRouteToPvz,
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
