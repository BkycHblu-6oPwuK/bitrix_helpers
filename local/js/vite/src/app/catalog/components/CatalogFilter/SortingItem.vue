<script setup>
import { computed } from 'vue';
import storeAbout from '@/store/about';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    currentSortId: String
})
const emits = defineEmits(['clickHandler']);
const isMobile = computed(() => storeAbout.getters.isMobile);
</script>

<template>
    <span @click="emits('clickHandler', props.item.fieldId)" :class="{
        'filter__price-list-item': !isMobile,
        'm-filter__price-list-item_active': isMobile && props.currentSortId === props.item.fieldId,
        'm-filter__price-list-item': isMobile,
    }">{{ props.item.name }}</span>
</template>