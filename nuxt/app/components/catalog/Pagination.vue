<!--
  Компонент пагинации
  Отображает кнопку "Показать еще" и номера страниц
  Текущая страница выделена цветом
-->
<script setup lang="ts">
const { pagination, hasMore, currentPage, pageCount, goToPage, showMore } = usePagination()
</script>

<template>
    <div v-if="pagination && pageCount > 1" class="mt-8 flex flex-col items-center gap-4">
        <UButton v-if="hasMore" color="primary" variant="outline" size="lg" @click="showMore">
            Показать еще
        </UButton>

        <div class="flex items-center gap-2">
            <UButton v-for="page in pageCount" :key="page" :color="page === currentPage ? 'primary' : 'neutral'"
                :variant="page === currentPage ? 'solid' : 'outline'" size="sm" @click="goToPage(page)">
                {{ page }}
            </UButton>
        </div>
    </div>
</template>