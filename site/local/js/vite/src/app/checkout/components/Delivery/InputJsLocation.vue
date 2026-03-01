<script setup>
import { useStore } from 'vuex';
import Input from '../Input.vue';
import { computed, onMounted, ref, watch } from 'vue';
import LocationList from './LocationList.vue';
import { debounce } from '@/common/js/helpers';
import DaDataLocation from '@/lib/Location/DaDataLocation';
import YandexLocation from '@/lib/Location/YandexLocation';

const store = useStore();
const emits = defineEmits(['selectAddress']);
const locationItems = ref([]);
const isInputFocused = ref(false);
const address = computed({
    get: () => store.getters.getDelivery.address,
    set: (val) => store.commit('setAddress', val)
});
const coords = computed({
    get: () => store.getters.getDelivery.coordinates,
    set: (val) => store.commit('setCoordinates', val)
});
let daDataLocation = new DaDataLocation();
let yandexDisabled = false;
let oldAddress = '';

const locationSelect = (item) => {
    oldAddress = false;
    store.dispatch('resetLocation');
    address.value = item.value;
    if (item.postalCode) {
        store.commit('setPostCode', item.postalCode);
    }
    if (item.coords && item.coords.length) {
        coords.value = item.coords;
    }
    isInputFocused.value = false;
    store.dispatch('refresh').then(() => emits('selectAddress', item.coords && item.coords.length ? item.coords : item.value));
};

const focusHandler = () => {
    isInputFocused.value = true;
    oldAddress = address.value;
}

const blurHandler = () => {
    setTimeout(() => {
        isInputFocused.value = false;
        store.dispatch('refresh').then(() => emits('selectAddress'))
        return;
    }, 200);
};

const search = debounce(async () => {
    try {
        if (!yandexDisabled) {
            locationItems.value = await YandexLocation.getAddressByQuery(address.value);
        } else {
            locationItems.value = await daDataLocation.getAddressByQuery(address.value);
        }
    } catch (error) {
        if (!yandexDisabled) {
            yandexDisabled = true;
            try {
                locationItems.value = await daDataLocation.getAddressByQuery(address.value);
            } catch (e) {
                console.error('DaData API error:', e);
                locationItems.value = [];
            }
        } else {
            locationItems.value = [];
        }
    }
}, 500);

onMounted(() => {
    const addr = coords.value && coords.value.length ? coords.value : address.value;
    if (addr) {
        emits('selectAddress', addr, false);
    }
});

watch(address, (newVal) => {
    if (!newVal) {
        emits('selectAddress', null);
    }
})

</script>


<template>
    <div class="location-container">
        <div class="checkout-info__delivery-content">
            <div class="checkout-info__delivery-content-street">
                <Input v-model="address" @input="search" @focus="focusHandler" @blur="blurHandler"
                    :placeholder="'Город, улица, корпус, дом'" type="text" autocomplete="off" />
            </div>
        </div>
        <LocationList :items="locationItems" :show="isInputFocused" :useScroll="false" @select="locationSelect" />
    </div>
</template>