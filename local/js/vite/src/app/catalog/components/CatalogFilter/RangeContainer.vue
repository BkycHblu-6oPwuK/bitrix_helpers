<script setup>
import {watch, computed, useTemplateRef } from 'vue';
import { useStore } from 'vuex';
import Range from './Range.vue';
import storeAbout from '@/store/about';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    filterItemContainer: Object,
    mobileItemsBlockIsOpen: Boolean,
});

const store = useStore();
const isMobile = computed(() => storeAbout.getters.isMobile);
const item = useTemplateRef('item');
const filterMenuMobile = useTemplateRef('filterMenuMobile');
const minNumberItem = computed(() => props.item.VALUES['MIN']);
const maxNumberItem = computed(() => props.item.VALUES['MAX']);
const minNumber = computed({
    get() {
        let selectedValue = store.getters['catalogFilter/getSelectedFilters'][minNumberItem.value.CONTROL_ID];
        if(selectedValue) {
            return Number(selectedValue);
        }
        return Number(minNumberItem.value.HTML_VALUE || minNumberItem.value.VALUE);
    },
    set(val) {
        store.commit('catalogFilter/pushSelectedFilters', {
            controlId: minNumberItem.value.CONTROL_ID,
            value: val,
        });
    },
});

const maxNumber = computed({
    get() {
        let selectedValue = store.getters['catalogFilter/getSelectedFilters'][maxNumberItem.value.CONTROL_ID];
        if(selectedValue) {
            return Number(selectedValue);
        }
        return Number(maxNumberItem.value.HTML_VALUE || maxNumberItem.value.VALUE);
    },
    set(val) {
        store.commit('catalogFilter/pushSelectedFilters', {
            controlId: maxNumberItem.value.CONTROL_ID,
            value: val,
        });
    },
});

if (isMobile.value) {
    watch(() => props.mobileItemsBlockIsOpen, (newValue) => {
        if (!filterMenuMobile.value) return;
        if (!newValue) {
            props.filterItemContainer.querySelector('svg').style.transform = 'rotate(0deg)';
        } else {
            props.filterItemContainer.querySelector('svg').style.transform = 'rotate(180deg)';
        }
    });
} else {
    const applyFilter = () => {
        store.dispatch('catalogFilter/updateFilter', props.item.VALUES);
        store.dispatch('catalogFilter/applyFilter');
    }
}
</script>

<template>
    <div v-if="!isMobile" ref="item" class="filter__item-list hover-filter-item">
        <Range :name="props.item.NAME" :minNumberItem="minNumberItem" :maxNumberItem="maxNumberItem"
            v-model:minValue="minNumber" v-model:maxValue="maxNumber">
        </Range>
    </div>
    <div v-else ref="filterMenuMobile" class="m-filter__item-items-block">
        <div class="m-filter__item-item price-range">
            <Range :name="props.item.NAME" :minNumberItem="minNumberItem" :maxNumberItem="maxNumberItem"
                v-model:minValue="minNumber" v-model:maxValue="maxNumber">
            </Range>
        </div>
    </div>
</template>
