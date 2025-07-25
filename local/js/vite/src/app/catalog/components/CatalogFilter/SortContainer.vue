<script setup>
import { computed, onMounted, useTemplateRef } from 'vue';
import { isHoveredWithOverlay } from '@/common/js/helpers.js';
import { useStore } from 'vuex';
import SortingItem from './SortingItem.vue';
const sortContainer = useTemplateRef('sortContainer');
const sortDesctopBtn = useTemplateRef('sortDesctopBtn');
const store = useStore();
const sorting = computed(() => store.getters['catalogFilter/getSorting']);
const sortingItemClickHandler = (fieldId) => {
    store.dispatch('catalogFilter/sorting', fieldId)
}
onMounted(() => {
    isHoveredWithOverlay(sortDesctopBtn.value, sortContainer.value);
});
</script>


<template>
    <div ref="sortDesctopBtn" class="filter__price hover-filter-price">
        <span class="filter__price-title">{{ sorting.title }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M3.3335 15H16.6668M3.3335 10H12.4995M3.3335 5H9.16683" stroke="#111827" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div ref="sortContainer" class="filter__price-list hover-filter-price">
            <SortingItem v-for="item in sorting.availableSorting" :item="item" :key="item.fieldId" @clickHandler="sortingItemClickHandler"></SortingItem>
        </div>
    </div>
</template>