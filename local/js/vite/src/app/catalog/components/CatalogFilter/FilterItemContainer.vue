<script setup>
import { computed, ref, useTemplateRef } from 'vue';
import { useStore } from 'vuex';
import CheckboxContainer from './CheckboxContainer.vue';
import RangeContainer from './RangeContainer.vue';
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});
const store = useStore();
const types = computed(() => store.getters['catalogFilter/getTypes']);
const filterItemContainer = useTemplateRef('filterItemContainer');
const countSelected = ref(0);
const setCountSelected = (value) => countSelected.value = value;
</script>

<template>
    <div ref="filterItemContainer" class="filter__item hover-filter-item">
        <span class="filter__item-title">
            {{ item.NAME }}
        </span>
        <span v-if="countSelected" class="filter__item-num filter__num">{{countSelected}}</span>
        <div class="filter__item-arrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M5 7.5L10 12.5L15 7.5" stroke="#111827" stroke-width="1.5" stroke-linecap="square" />
            </svg>
        </div>
        <CheckboxContainer v-if="types.checkbox === item.DISPLAY_TYPE && filterItemContainer" :item="item" :filterItemContainer="filterItemContainer" @setCountSelected="setCountSelected"></CheckboxContainer>
        <RangeContainer v-if="types.range === item.DISPLAY_TYPE && filterItemContainer" :item="item" :filterItemContainer="filterItemContainer"></RangeContainer>
    </div>
</template>