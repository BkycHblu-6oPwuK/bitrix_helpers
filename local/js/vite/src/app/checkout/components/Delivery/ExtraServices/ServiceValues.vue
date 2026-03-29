<script setup>
import { ref, watch } from 'vue';
import RadioCard from '../../RadioCard.vue';
import Checkbox from '../../Checkbox.vue';
import { isExtaServiceSkipValue } from '@/store/checkout/helpers';

const props = defineProps({
    values: Object,
    item: Object,
    priceString: {
        type: String,
        default: 'Р'
    },
    showTitle: {
        type: String,
        default: 'Показать'
    }
})
const model = defineModel();
const show = ref(model.value && !isExtaServiceSkipValue(props.item) ? true : false);

const getPriceString = (price) => {
    if(!price) {
        return 'договорная';
    }
    return price + ' ' + props.priceString;
};

watch(show, (newVal) => {
    if (!newVal) {
        model.value = null;
        return;
    }
    for (let key in props.values) {
        const item = props.values[key];
        model.value = item.id
        break;
    }
});
</script>

<template>
    <div class="checkout-form-row">
        <Checkbox v-model="show" :label="showTitle"></Checkbox>
    </div>
    <template v-if="show">
        <div class="service-item-title">Цены на разгрузку</div>
        <div class="checkout-radio-group">
            <template v-for="item in values" :key="item.id">
                <RadioCard v-if="!isExtaServiceSkipValue(item)" :value="item.id" v-model="model">
                    <div class="service-item__value">
                        <div class="service-item__value-title">{{ item.title }}</div>
                        <div class="service-item__value-price">{{ getPriceString(item.price) }}</div>
                    </div>
                </RadioCard>
            </template>
        </div>
    </template>
</template>