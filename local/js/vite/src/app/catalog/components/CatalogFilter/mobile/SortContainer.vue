<script setup>
import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { useStore } from 'vuex';
import { closeByEscAndOverlay, openModal } from '@/common/js/helpers.js';
import SortingItem from '../SortingItem.vue';
const props = defineProps({
    sortBtn: {
        type: Object,
        required: true,
    },
});
const mobileSortContainer = useTemplateRef('mobileSortContainer');
const store = useStore();
const sorting = computed(() => store.getters['catalogFilter/getSorting']);
const selectedSortId = ref(sorting.value.currentSortId);
const sortingItemClickHandler = (fieldId) => {
    selectedSortId.value = fieldId;
}
const sortingApply = () => store.dispatch('catalogFilter/sorting', selectedSortId.value);
onMounted(() => {
    props.sortBtn && props.sortBtn.addEventListener('click', () => {
        openModal(mobileSortContainer.value);
        closeByEscAndOverlay(mobileSortContainer.value, 'filter-modal')
    })
});
</script>


<template>
    <div ref="mobileSortContainer" class="m-filter__price-list-container filter-modal">
        <div class="m-filter__price-list drag-menu">
            <div class="m-filter__price-list-title">
                <span>Сортировка</span>
            </div>
            <div class="m-filter__price-list-items">
                <SortingItem v-for="item in sorting.availableSorting" :item="item" :currentSortId="selectedSortId" :key="item.fieldId" @clickHandler="sortingItemClickHandler"></SortingItem>
            </div>
            <div class="reviews__form-mobile-grab"></div>
        </div>
        <div class="m-filter__price-list-accept-btn-container">
            <button class="m-filter__price-list-accept-btn" @click="sortingApply">Применить</button>
        </div>
    </div>
</template>