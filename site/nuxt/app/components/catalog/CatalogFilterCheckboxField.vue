<!--
  Поле с чекбоксами для фильтра
  Отображает список значений фильтра с чекбоксами
  Используется внутри поповера CatalogFilterItem
-->
<script setup lang="ts">
import type { FilterValueItemDTO } from '~/types/iblock/section.ts'

// Пропсы от родителя
const props = defineProps<{
  values: FilterValueItemDTO[] // Массив значений фильтра
  selectedFilters: Record<string, string> // Текущие выбранные фильтры
}>()

const emit = defineEmits<{
  toggle: [controlId: string, value: string] // Событие переключения чекбокса
}>()

// Обработчик клика по чекбоксу
const handleToggle = (controlId: string, value: string) => {
  console.log('toggle', controlId, value)
  emit('toggle', controlId, value)
}
</script>

<template>
  <div class="p-3 min-w-[250px] max-h-[400px] overflow-y-auto">
    <div class="space-y-2">
      <label
        v-for="value in values"
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
          @change="handleToggle(value.controlId, value.htmlValue)"
        />
        <span class="text-sm" v-html="value.value"></span>
      </label>
    </div>
  </div>
</template>
