<script setup>
import { ref, computed, watch, useTemplateRef } from 'vue';
import { useStore } from 'vuex';
import Checkbox from './Checkbox.vue';
import storeAbout from '@/store/about/index.js';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    filterItemContainer: Object,
    mobileItemsBlockIsOpen: Boolean,
    countSelected: Number
});
const emits = defineEmits(['setCountSelected'])

const store = useStore();
const isMobile = computed(() => storeAbout.getters.isMobile);
const item = useTemplateRef('item');
const filterMenuMobile = useTemplateRef('filterMenuMobile');
const currentSelectedFilters = ref({});

const updateCurrentSelectedFilters = () => {
    const selectedFiltersSet = new Set(Object.keys(store.getters['catalogFilter/getSelectedFilters']));

    for (let keyValue in props.item.VALUES) {
        if (selectedFiltersSet.has(props.item.VALUES[keyValue].CONTROL_ID)) {
            currentSelectedFilters.value[props.item.VALUES[keyValue].CONTROL_ID] = true;
        } else {
            delete currentSelectedFilters.value[props.item.VALUES[keyValue].CONTROL_ID];
        }
    }
};

updateCurrentSelectedFilters();

watch(() => store.getters['catalogFilter/getSelectedFilters'], () => {
    updateCurrentSelectedFilters();
    changeCallback();
});

const updateSelectedFilters = () => {
    for (let key in currentSelectedFilters.value) {
        if (currentSelectedFilters.value[key]) {
            store.commit('catalogFilter/pushSelectedFilters', { controlId: key, value: 'Y' })
        } else {
            store.commit('catalogFilter/removeSelectedFilters', key)
        }
    }
}

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

const changeCallback = () => {
    updateSelectedFilters();
    emits('setCountSelected', Object.entries(currentSelectedFilters.value).filter(([key, value]) => value === true).length);
};
changeCallback();

</script>

<template>
    <div v-if="!isMobile" ref="item" class="filter__item-list hover-filter-item">
        <div class="filter__item-list-item" v-for="itemValue in props.item.VALUES" :key="itemValue.CONTROL_ID">
            <Checkbox :itemValue="itemValue" v-model:value="currentSelectedFilters[itemValue.CONTROL_ID]"
                @changeCallback="changeCallback"></Checkbox>
        </div>
    </div>
    <div v-else ref="filterMenuMobile" class="m-filter__item-items-block">
        <div class="m-filter__item-item" v-for="itemValue in props.item.VALUES" :key="itemValue.CONTROL_ID">
            <Checkbox :itemValue="itemValue" v-model:value="currentSelectedFilters[itemValue.CONTROL_ID]"
                @changeCallback="changeCallback"></Checkbox>
        </div>
    </div>
</template>
