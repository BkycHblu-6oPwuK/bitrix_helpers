<script setup>
import { useStore } from 'vuex';
import { computed, useTemplateRef } from 'vue';
import ProductCard from '@/common/components/ProductCard.vue';
import PaginationComponent from '@/common/components/Pagination.vue';

const store = useStore();
const items = computed(() => store.getters['catalogSection/getItems']);
const pagination = computed(() => store.getters['catalogSection/getPagination']);
const catalog_block = useTemplateRef('catalog_block');
const showMore = () => store.dispatch('catalogSection/showMore');
const changePage = (page) => {
    store.dispatch('catalogSection/changePage', page);
    if (catalog_block.value) {
        const offsetTop = catalog_block.value.getBoundingClientRect().top + window.scrollY - 100;
        window.scrollTo({ top: offsetTop, behavior: 'smooth' });
    }
};
</script>

<template>
    <div ref="catalog_block" class="catalog-type">
        <div class="catalog-type__container">
            <ProductCard v-for="item in items" :key="item.id" :item="item"></ProductCard>
        </div>

        <PaginationComponent v-if="pagination.pageCount > 1" :pagination="pagination" @showMore="showMore" @changePage="changePage"></PaginationComponent>
    </div>
</template>