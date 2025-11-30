<script setup lang="ts">
import type { SectionDTO } from '~/types/iblock/catalog';
import SectionItem from './SectionItem.vue';

const props = defineProps<{
  sections: Array<SectionDTO>
}>()
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <SectionItem
            v-for="item in sections"
            :key="item.id"
            :item="item"
        />
    </div>
</template>