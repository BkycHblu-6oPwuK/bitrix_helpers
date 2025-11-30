<script setup lang="ts">
import type { PaginationDTO } from '~/types/pagination';

const props = defineProps<{
    pagination?: PaginationDTO
}>()

const emit = defineEmits<{
  showMore: []
  changePage: [page: number]
}>()

const handleShowMore = () => {
  emit('showMore')
}

const handleChangePage = (page: number) => {
  emit('changePage', page)
}

const { pagination, hasMore, currentPage, pageCount } = usePagination(props.pagination)
</script>

<template>
    <div v-if="pagination && pageCount > 1" class="mt-8 flex flex-col items-center gap-4">
        <UButton v-if="hasMore" color="primary" variant="outline" size="lg" @click="handleShowMore">
            Показать еще
        </UButton>

        <div class="flex items-center gap-2">
            <UButton v-for="page in pageCount" :key="page" :color="page === currentPage ? 'primary' : 'neutral'"
                :variant="page === currentPage ? 'solid' : 'outline'" size="sm" @click="handleChangePage(page)">
                {{ page }}
            </UButton>
        </div>
    </div>
</template>