<script setup>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';
import { getLocation } from '@/api/location';
import { debounce } from '@/common/js/helpers';
import Input from '../Input.vue';
import LocationList from './LocationList.vue';

const store = useStore();
const city = computed({
    get: () => isInputFocused.value ? store.getters.getDelivery.city : store.getters.getDisplayAddress,
    set: (val) => store.commit('setCity', val)
});
const locationItems = ref([]);
const isInputFocused = ref(false);
const isNextPageAvailable = ref(true);
const pageSize = 20;
let page = 0;
let oldCityName = '';

const citySearch = debounce(async () => {
    if (!city.value || city.value.length < 2 || !isNextPageAvailable.value) return;

    const result = await getLocation(city.value, { page, pageSize });
    if (result.items.length < pageSize) {
        isNextPageAvailable.value = false;
    }
    page++;
    locationItems.value.push(...result.items);
}, 500);

const locationSelect = (item) => {
    oldCityName = false;
    store.dispatch('resetLocation');
    city.value = item.display;
    store.commit('setLocation', item.code);
    store.dispatch('refresh');
    isInputFocused.value = false;
};

const focusHandler = () => {
    isInputFocused.value = true;
    oldCityName = city.value;
}

const blurHandler = () => {
    setTimeout(() => {
        isInputFocused.value = false;
        if (oldCityName !== false) {
            city.value = oldCityName;
            oldCityName = '';
        }
    }, 200);
};

const search = () => {
    page = 0;
    isNextPageAvailable.value = true;
    locationItems.value = [];
    citySearch();
}
</script>


<template>
    <div class="location-container">
        <div class="checkout-info__delivery-content">
            <div class="checkout-info__delivery-content-street">
                <Input :placeholder="'Населенный пункт'" v-model="city" @input="search" @focus="focusHandler"
                    @blur="blurHandler" type="text" autocomplete="off" />
            </div>
        </div>
        <LocationList :items="locationItems" :show="isInputFocused" :useScroll="true" @select="locationSelect"
            @showMore="citySearch" />
    </div>
</template>

<style scoped>
.location-list {
    max-height: 300px;
    position: absolute;
    box-sizing: border-box;
    width: 100%;
    z-index: 99;
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 0 0 4px 4px;
    padding: 10px;
    background: #fff;
    display: flex;
    flex-direction: column;
    max-width: 800px;
    width: 100%;
}

.location-item {
    padding: 10px;
    cursor: pointer;
    transition: background .1s ease-in-out;
}

.location-item:hover {
    background: #f0f0f0;
}
</style>