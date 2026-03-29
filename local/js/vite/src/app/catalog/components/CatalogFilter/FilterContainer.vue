<script setup>
import { useStore } from 'vuex';
import { computed } from 'vue';
import FilterItemContainer from './FilterItemContainer.vue';
const store = useStore();
const items = computed(() => store.getters['catalogFilter/getItems']);
</script>

<template>
    <FilterItemContainer v-for="item in items" :item="item" :key="item.ID"></FilterItemContainer>
</template>