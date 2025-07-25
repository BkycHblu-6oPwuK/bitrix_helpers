<script setup>
import Slider from '@vueform/slider'
import { computed } from 'vue'
import '@vueform/slider/themes/default.css'

const props = defineProps({
    name: {
        type: String,
        required: true
    },
    minNumberItem: {
        type: Object,
        required: true,
    },
    maxNumberItem: {
        type: Object,
        required: true,
    },
});

const minValue = defineModel('minValue');
const maxValue = defineModel('maxValue');

const minFixedValue = Number(props.minNumberItem.VALUE);
const maxFixedValue = Number(props.maxNumberItem.VALUE);

const priceRange = computed({
  get: () => [minValue.value, maxValue.value],
  set: ([min, max]) => {
    minValue.value = min;
    maxValue.value = max;
  },
});

const updatePrice = ([min, max]) => {
  priceRange.value = [min, max]
};
</script>

<template>
  <div class="filter__item-price-range">
    <div class="filter__item-price-inputs">
      <input :id="minNumberItem.CONTROL_ID" type="number" v-model="minValue" class="input">
      <input :id="maxNumberItem.CONTROL_ID" type="number" v-model="maxValue" class="input">
    </div>
    <Slider
        v-model="priceRange"
        :min="minFixedValue"
        :max="maxFixedValue"
        :step="100"
        :tooltips="false"
        range
        @slide="updatePrice"
    />
  </div>
</template>