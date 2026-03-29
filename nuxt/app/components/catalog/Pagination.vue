<!--
  Компонент пагинации
  Отображает кнопку "Показать еще" и номера страниц
  Текущая страница выделена цветом
-->
<script setup lang="ts">
// События для родительского компонента
const emit = defineEmits<{
  showMore: [] // Дозагрузка следующей страницы
  changePage: [page: number] // Переход на конкретную страницу
}>()

// Обработчик кнопки "Показать еще"
const handleShowMore = () => {
  emit('showMore')
}

// Обработчик клика по номеру страницы
const handleChangePage = (page: number) => {
  emit('changePage', page)
}

// Получаем данные пагинации из store
const { pagination, hasMore, currentPage, pageCount } = usePagination()
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