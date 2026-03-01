<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';
import ServiceValues from './ServiceValues.vue';
import Checkbox from '../../Checkbox.vue';
import { debounce } from '@/common/js/helpers';

const store = useStore();
const props = defineProps({
    item: Object,
})
const priceShowString = computed(() => {
    switch (props.item.code) {
        case "PRICE_UNLOADING":
            return ['руб/тн', 'Требуется разгрузка'];
        default:
            return [null, null]
    }
})
const selected = computed({
    get: () => formatValue(props.item.value),
    set: (val) => {
        store.commit('setExtraServiceValue', {
            serviceId: props.item.id,
            value: formatValue(val)
        });
        refresh();
    },
});
const debounceTime = computed(() => {
    switch (props.item.type) {
        case 'STRING':
            return 500;
        default:
            return 0;
    }
});

const formatValue = (value) => {
    switch (value) {
        case true:
            return 'Y';
        case false:
            return 'N';
        case 'Y':
            return true;
        case 'N':
            return false
        default:
            if (props.item.type === 'ENUM') {
                return Number(value)
            }
            return value
    }
};

const refresh = debounce(() => {
    store.dispatch('refresh');
}, debounceTime.value)
</script>

<template>
    <template v-if="item.values && item.values.length">
        <ServiceValues :values="item.values" :priceString="priceShowString[0]" :showTitle="priceShowString[1]"
            v-model="selected" :item="item"></ServiceValues>
    </template>
    <template v-else-if="item.type === 'Y/N'">
        <div class="checkout-form-row">
            <Checkbox v-model="selected" :label="item.title"></Checkbox>
        </div>
    </template>
    <template v-else-if="item.type === 'STRING'">
        <div class="checkout-form-row">
            <div class="input-wrapper">
                <input v-model="selected" class="checkout-form-input" />
            </div>
            <label>{{ item.title }}</label>
        </div>
    </template>
</template>