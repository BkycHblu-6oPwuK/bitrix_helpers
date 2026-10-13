<!--
  Компонент панели фильтров с горизонтальным расположением
  Отображает дропдаун сортировки, поповеры с фильтрами и кнопку очистки
-->
<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog'

// Получаем доступ к store фильтров
const { filterData, selectedFilters, currentSortId, hasActiveFilters, hasFilters, hasSorting, toggleFilter, applyFilters, resetFilters, applySorting } = useSectionFilter<CatalogDTO['filter']>()

// Название текущей сортировки для отображения в кнопке
const currentSortingName = computed(() => {
    const currentSort = filterData?.value?.sorting.availableSorting.find(s => s.code === currentSortId.value)
    return currentSort?.name || filterData?.value?.sorting.title
})

// Подсчет выбранных значений в конкретном фильтре (для бейджа)
const getSelectedCount = (filterItem: any) => {
    if (filterItem.displayType === filterData?.value?.types.checkbox) {
        return filterItem.values.filter((v: any) => selectedFilters.value[v.controlId]).length
    }
    return 0
}
</script>

<template>
    <div v-if="hasFilters || hasSorting" class="catalog-filter flex items-center gap-3 flex-wrap">
        <UPopover v-if="hasSorting" mode="hover" :popper="{ placement: 'bottom-start', offsetDistance: 8 }">
            <UButton color="neutral" variant="outline" trailing-icon="i-heroicons-chevron-down" class="font-normal">
                {{ currentSortingName }}
            </UButton>

            <template #content>
                <div class="p-2 min-w-[200px]">
                    <button v-for="sortItem in filterData?.sorting?.availableSorting || []" :key="sortItem.id" :class="[
                        'w-full text-left px-3 py-2 rounded-md transition-colors text-sm',
                        currentSortId === sortItem.code
                            ? 'bg-primary-100 text-primary-900 dark:bg-primary-900 dark:text-primary-100 font-medium'
                            : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                    ]" @click="applySorting(sortItem.code)">
                        {{ sortItem.name }}
                    </button>
                </div>
            </template>
        </UPopover>

        <template v-if="hasFilters && filterData">
            <CatalogFilterItem v-for="filterItem in filterData.items" :key="filterItem.id" :filter-item="filterItem"
                :filter="filterData" :selected-filters="selectedFilters" :selected-count="getSelectedCount(filterItem)"
                @toggle-filter="toggleFilter" @apply-filter="applyFilters" />

            <UButton v-if="hasActiveFilters" color="neutral" variant="ghost" icon="i-heroicons-x-mark" size="sm"
                @click="resetFilters">
                Очистить
            </UButton>
        </template>
    </div>
</template>
