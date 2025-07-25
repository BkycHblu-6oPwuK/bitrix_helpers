<script setup>
import { onMounted, computed, useTemplateRef } from 'vue';
import { closeByEscAndOverlay, openModal } from '@/common/js/helpers.js';
import FilterItemContainer from './FilterItemContainer.vue';
import { useStore } from 'vuex';

const props = defineProps({
    filterBtn: {
        type: Object,
        required: true,
    },
});
const store = useStore();
const items = computed(() => store.getters['catalogFilter/getItems']);
const mobileFilterContainer = useTemplateRef('mobileFilterContainer');
onMounted(() => {
    props.filterBtn && props.filterBtn.addEventListener('click', () => {
        openModal(mobileFilterContainer.value);
        closeByEscAndOverlay(mobileFilterContainer.value, 'filter-modal')
    })
});
const filterApply = () => {
    for(let key in items.value){
        store.dispatch('catalogFilter/updateFilter', items.value[key].VALUES);
    }
    store.dispatch('catalogFilter/applyFilter');
}
const clearFilter = () => store.dispatch('catalogFilter/clearFilter');
</script>

<template>
    <div ref="mobileFilterContainer" class="m-filter__product-container filter-modal">
        <div class="m-filter__product-list drag-menu">
            <div class="m-filter__product-title">
                <span>Фильтры</span>
                <span class="m-filter__product-clear-btn" @click="clearFilter">Очистить</span>
            </div>
            <div class="m-filter__product-list-items">
                <div class="m-filter__item-items" v-for="item in items" :key="item.ID">
                    <FilterItemContainer :item="item"></FilterItemContainer>
                </div>
            </div>
            <div class="reviews__form-mobile-grab"></div>
        </div>
        <div class="m-filter__product-list-accept-btn-container">
            <button class="m-filter__product-list-accept-btn" @click="filterApply">Применить</button>
        </div>
    </div>
</template>