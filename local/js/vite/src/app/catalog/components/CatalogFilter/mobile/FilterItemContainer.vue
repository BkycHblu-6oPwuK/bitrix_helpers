<script setup>
import { useStore } from 'vuex';
import { computed, ref, useTemplateRef } from 'vue';
import CheckboxContainer from '../CheckboxContainer.vue';
import RangeContainer from '../RangeContainer.vue';
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});
const mobileItemsBlockIsOpen = ref(false);
const filterItemContainer = useTemplateRef('filterItemContainer');
const store = useStore();
const types = computed(() => store.getters['catalogFilter/getTypes']);
</script>

<template>
    <div ref="filterItemContainer" class="m-filter__item-items-title"
        @click="mobileItemsBlockIsOpen = !mobileItemsBlockIsOpen">
        <span>{{ props.item.NAME }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M6 9L12 15L18 9" stroke="#5C636E" stroke-width="2" stroke-linecap="square" />
        </svg>
    </div>
    <CheckboxContainer v-if="types.checkbox === item.DISPLAY_TYPE && filterItemContainer" :item="props.item"
        :filterItemContainer="filterItemContainer" :mobileItemsBlockIsOpen="mobileItemsBlockIsOpen"></CheckboxContainer>
    <RangeContainer v-if="types.range === item.DISPLAY_TYPE && filterItemContainer" :item="props.item"
        :filterItemContainer="filterItemContainer" :mobileItemsBlockIsOpen="mobileItemsBlockIsOpen"></RangeContainer>
</template>