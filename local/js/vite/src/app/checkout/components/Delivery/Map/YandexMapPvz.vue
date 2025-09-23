<script setup>
import { onMounted, onUnmounted, computed, useTemplateRef, watch } from 'vue';
import { wait } from '@/common/js/helpers';
import YandexMapPvzHandler from '@/lib/Location/YandexMapPvzHandler';

const props = defineProps({
    pvzList: [Array, Object],
    center: [Object, String]
});
const emits = defineEmits(['selectPvz']);
const mapContainer = useTemplateRef('mapContainer');
const normalizedPvzList = computed(() => {
    if (Array.isArray(props.pvzList)) {
        return props.pvzList;
    } else if (typeof props.pvzList === 'object' && props.pvzList !== null) {
        return Object.values(props.pvzList);
    }
    return [];
});
let mapHandler = null;
let isInited = false;

const buildRouteToPvz = (pvzCoords) => mapHandler?.buildRouteToUserCoords(pvzCoords);
const selectPvz = (pvz) => emits('selectPvz', pvz);

onMounted(async () => {
    try {
        mapHandler = new YandexMapPvzHandler(mapContainer, props.center, selectPvz);
        console.log(111)
        await mapHandler.initMap(normalizedPvzList.value);
        isInited = true;
    } catch (error) {
        console.error('Ошибка при инициализации карты:', error);
    }
});

onUnmounted(() => {
    mapHandler?.destroyMap();
});

watch(normalizedPvzList, async (newPvzList) => {
    try {
        await wait(() => isInited)
        if (mapHandler) {
            mapHandler.destroyMap();
            mapHandler = null;
        }
        mapHandler = new YandexMapPvzHandler(mapContainer, props.center, selectPvz);
        mapHandler.initMap(newPvzList);
    } catch (error) {
        console.error('Ошибка при обновлении карты:', error);
    }
});

defineExpose({
    'buildRouteToPvz': buildRouteToPvz
})
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
