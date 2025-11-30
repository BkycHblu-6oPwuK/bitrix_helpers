<script setup lang="ts">
import type { CatalogFilterDTO } from '~/types/iblock/catalog'

const props = defineProps<{
  filter: CatalogFilterDTO
}>()

const emit = defineEmits<{
  applyFilter: []
  clearFilter: []
  updateSorting: [sortId: string]
}>()

const { selectedFilters, currentSortId, hasActiveFilters, toggleFilter, clearFilters, setSorting } = useCatalogFilter(props.filter)

const handleApplyFilter = () => {
  emit('applyFilter')
}

const handleClearFilter = () => {
  clearFilters()
  emit('clearFilter')
}

const handleSortChange = (sortId: string) => {
  setSorting(sortId)
  emit('updateSorting', sortId)
}

const currentSortingName = computed(() => {
  const currentSort = props.filter.sorting.availableSorting.find(s => s.code === currentSortId.value)
  return currentSort?.name || props.filter.sorting.title
})

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

    <UPopover
      v-for="filterItem in filter.items"
      :key="filterItem.id"
      mode="hover"
      :popper="{ placement: 'bottom-start', offsetDistance: 8 }"
    >
      <UButton
        color="neutral"
        variant="outline"
        trailing-icon="i-heroicons-chevron-down"
        class="font-normal"
      >
        {{ filterItem.name }}
        <UBadge
          v-if="getSelectedCount(filterItem) > 0"
          :label="getSelectedCount(filterItem).toString()"
          color="primary"
          size="xs"
          class="ml-2"
        />
      </UButton>

      <template #content>
        <div v-if="filterItem.displayType === filter.types.checkbox" class="p-3 min-w-[250px] max-h-[400px] overflow-y-auto">
          <div class="space-y-2">
            <label
              v-for="value in filterItem.values"
              :key="value.controlId"
              :class="[
                'flex items-center gap-2 cursor-pointer p-2 rounded-md transition-colors hover:bg-gray-50 dark:hover:bg-gray-800',
                { 'opacity-50 cursor-not-allowed': value.disabled }
              ]"
            >
              <input
                type="checkbox"
                :checked="!!selectedFilters[value.controlId]"
                :disabled="value.disabled"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                @change="toggleFilter(value.controlId, value.htmlValue); handleApplyFilter()"
              />
              <span class="text-sm" v-html="value.htmlValue"></span>
            </label>
          </div>
        </div>
      </template>
    </UPopover>

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
