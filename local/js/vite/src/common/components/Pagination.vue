<script setup>
const props = defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});
const emits = defineEmits(['showMore', 'changePage'])
</script>

<template>
    <div>
        <button class="catalog-type__btn-more" @click="emits('showMore')" v-if="props.pagination.currentPage < props.pagination.pageCount">Показать еще</button>
        <div class="catalog-type__pagination">
            <template v-if="props.pagination.currentPage > 1">
                <span @click="emits('changePage', --props.pagination.currentPage)" class="catalog-type__pagination-prev">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M19 10L1 10" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M6 5L1 10L6 15" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <span>Назад</span>
                </span>
                <span @click="emits('changePage', --props.pagination.currentPage)" class="catalog-type__pagination-prev-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="#9CA3AF" stroke-width="2" stroke-linecap="square" />
                    </svg>
                </span>
            </template>
            <template v-else>
                <span class="catalog-type__pagination-prev disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M19 10L1 10" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round" />
                        <path d="M6 5L1 10L6 15" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round" />
                    </svg>
                    <span>Назад</span>
                </span>
                <span class="catalog-type__pagination-prev-mobile disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="#9CA3AF" stroke-width="2" stroke-linecap="square" />
                    </svg>
                </span>
            </template>
            <div class="catalog-type__pagination-pages">
                <span v-for="page in props.pagination.pages" :key="page.pageNumber" @click="emits('changePage', page.pageNumber)"
                    :class="['catalog-type__page', { 'active-page': page.isSelected }]">
                    {{ page.pageNumber }}
                </span>
            </div>
            <template v-if="props.pagination.currentPage < props.pagination.pageCount">
                <span @click="emits('changePage', ++props.pagination.currentPage)" class="catalog-type__pagination-next">
                    <span>Дальше</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M1 10L19 10" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M14 5L19 10L14 15" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </span>
                <span @click="emits('changePage', ++props.pagination.currentPage)" class="catalog-type__pagination-next-mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="#9CA3AF" stroke-width="2" stroke-linecap="square" />
                    </svg>
                </span>
            </template>
            <template v-else>
                <span class="catalog-type__pagination-next disabled">
                    <span>Дальше</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M1 10L19 10" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round" />
                        <path d="M14 5L19 10L14 15" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                              stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="catalog-type__pagination-next-mobile disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="#9CA3AF" stroke-width="2" stroke-linecap="square" />
                    </svg>
                </span>
            </template>
        </div>
    </div>
</template>