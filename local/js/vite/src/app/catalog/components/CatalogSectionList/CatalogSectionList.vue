<script setup>
import { useStore } from 'vuex';
import { computed } from 'vue';
import CatalogSectionListItem from "@/app/catalog/components/CatalogSectionList/CatalogSectionListItem.vue";

const store = useStore();
const items = computed(() => store.getters['catalogSectionList/getItems']);
</script>

<template>
    <section v-if="items.length" class="catalog-sections">
        <div class="sections-slider js-catalog-sections">
            <div class="swiper-wrapper">
                <CatalogSectionListItem v-for="item in items" :key="item.id" :item="item" />
            </div>
        </div>
    </section>
</template>