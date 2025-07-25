<script setup>
import { useStore } from 'vuex';
import {computed, useTemplateRef} from 'vue';
import PaginationComponent from "@/common/components/Pagination.vue";
import ArticleItem from "@/app/articles/components/ArticleItem.vue";

const store = useStore();
const items = computed(() => store.getters['getItems']);
const pagination = computed(() => store.getters['getPagination']);
const articles_block = useTemplateRef('articles_block');
const showMore = () => store.dispatch('showMore');
const changePage = (page) => {
    store.dispatch('changePage', page);
    if (articles_block.value) {
        const offsetTop = articles_block.value.getBoundingClientRect().top + window.scrollY - 100;
        window.scrollTo({ top: offsetTop, behavior: 'smooth' });
    }
};
</script>

<template>
    <div ref="articles_block" class="articles-list">
        <ArticleItem v-for="item in items" :key="item.id" :item="item"></ArticleItem>
    </div>
    <PaginationComponent v-if="pagination.pageCount > 1" :pagination="pagination" @showMore="showMore" @changePage="changePage"></PaginationComponent>
</template>