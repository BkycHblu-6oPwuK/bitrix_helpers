<!--
  Компонент панели фильтров с горизонтальным расположением
  Отображает дропдаун сортировки, поповеры с фильтрами и кнопку очистки
-->
<script setup lang="ts">
import type { FilterDTO } from '~/types/iblock/catalog.ts'
import CatalogFilterItem from './CatalogFilterItem.vue'

// Пропсы: данные фильтра (поля, сортировки, типы)
const props = defineProps<{
  filter: FilterDTO
}>()

// События для родительского компонента
const emit = defineEmits<{
  applyFilter: [] // Применить фильтры
  clearFilter: [] // Очистить фильтры
  updateSorting: [sortId: string] // Изменить сортировку
}>()

// Получаем доступ к store фильтров
const { selectedFilters, currentSortId, hasActiveFilters, toggleFilter, clearFilters, setSorting } = useSectionFilter()

// Прокидываем событие применения фильтров наверх
const handleApplyFilter = () => {
  emit('applyFilter')
}

// Очищаем фильтры в store и прокидываем событие
const handleClearFilter = () => {
  clearFilters()
  emit('clearFilter')
}

// Обработчик изменения сортировки
const handleSortChange = (sortId: string) => {
  setSorting(sortId)
  emit('updateSorting', sortId)
}

// Переключение значения фильтра
const handleToggleFilter = (controlId: string, value: string) => {
  toggleFilter(controlId, value)
}

// Название текущей сортировки для отображения в кнопке
const currentSortingName = computed(() => {
  const currentSort = props.filter.sorting.availableSorting.find(s => s.code === currentSortId.value)
  return currentSort?.name || props.filter.sorting.title
})

// Подсчет выбранных значений в конкретном фильтре (для бейджа)
const getSelectedCount = (filterItem: any) => {
  if (filterItem.displayType === props.filter.types.checkbox) {
    return filterItem.values.filter((v: any) => selectedFilters.value[v.controlId]).length
  }
  return 0
}
</script>

<template>
  <div class="catalog-filter flex items-center gap-3 flex-wrap">
    <UPopover mode="hover" :popper="{ placement: 'bottom-start', offsetDistance: 8 }">
      <UButton
        color="neutral"
        variant="outline"
        trailing-icon="i-heroicons-chevron-down"
        class="font-normal"
      >
        {{ currentSortingName }}
      </UButton>

      <template #content>
        <div class="p-2 min-w-[200px]">
          <button
            v-for="sortItem in filter.sorting.availableSorting"
            :key="sortItem.id"
            :class="[
              'w-full text-left px-3 py-2 rounded-md transition-colors text-sm',
              currentSortId === sortItem.code
                ? 'bg-primary-100 text-primary-900 dark:bg-primary-900 dark:text-primary-100 font-medium'
                : 'hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
            @click="handleSortChange(sortItem.code)"
          >
            {{ sortItem.name }}
          </button>
        </div>
      </template>
    </UPopover>

    <CatalogFilterItem
      v-for="filterItem in filter.items"
      :key="filterItem.id"
      :filter-item="filterItem"
      :filter="filter"
      :selected-filters="selectedFilters"
      :selected-count="getSelectedCount(filterItem)"
      @toggle-filter="handleToggleFilter"
      @apply-filter="handleApplyFilter"
    />

    <UButton
      v-if="hasActiveFilters"
      color="neutral"
      variant="ghost"
      icon="i-heroicons-x-mark"
      size="sm"
      @click="handleClearFilter"
    >
      Очистить
    </UButton>
  </div>
</template>
