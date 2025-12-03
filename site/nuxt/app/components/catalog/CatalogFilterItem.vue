<!--
  Один элемент фильтра с поповером
  Отображает кнопку с названием фильтра и бейджем с количеством выбранных значений
  При наведении открывает поповер с чекбоксами
-->
<script setup lang="ts">
import type { FilterDTO, FilterItemDTO } from '~/types/iblock/page';
import CatalogFilterCheckboxField from './CatalogFilterCheckboxField.vue'

// Пропсы от родительского компонента
const props = defineProps<{
  filterItem: FilterItemDTO // Данные конкретного фильтра (название, значения)
  filter: FilterDTO // Общие данные фильтра (для типов)
  selectedFilters: Record<string, string> // Текущие выбранные фильтры
  selectedCount: number // Количество выбранных значений
}>()

// Состояние открытия/закрытия поповера
const isOpen = ref(false)

const emit = defineEmits<{
  toggleFilter: [controlId: string, value: string] // Переключение фильтра
  applyFilter: [] // Применение фильтров
}>()

// Пробрасываем событие переключения
const handleToggle = (controlId: string, value: string) => {
  emit('toggleFilter', controlId, value)
}

// Когда поповер закрывается - применяем фильтры
watch(isOpen, (newVal) => {
  if (!newVal) {
    emit('applyFilter')
  }
})
</script>

<template>
  <UPopover
    v-model:open="isOpen"
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
        v-if="selectedCount > 0"
        :label="selectedCount.toString()"
        color="primary"
        size="xs"
        class="ml-2"
      />
    </UButton>

    <template #content>
      <CatalogFilterCheckboxField
        v-if="filterItem.displayType === filter.types.checkbox"
        :values="filterItem.values"
        :selected-filters="selectedFilters"
        @toggle="handleToggle"
      />
    </template>
  </UPopover>
</template>
