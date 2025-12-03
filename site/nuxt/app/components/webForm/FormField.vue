<script setup lang="ts">
import { computed } from 'vue'
import { UInput, UTextarea, USelect, URadioGroup } from '#components'
import type { FormNewFieldDTO } from '~/types/webForm';

const props = defineProps<{ field: FormNewFieldDTO; error?: string }>()
const model = defineModel<any>()

const options = computed(() =>
  props.field.options?.map(o => ({
    label: o.label,
    value: String(o.value),
    disabled: o.active === false
  })) ?? []
)

const component = computed(() => {
  const type = props.field.type
  if (['textarea'].includes(type)) return UTextarea
  if (['select', 'dropdown', 'multiselect'].includes(type)) return USelect
  if (['checkbox', 'radio'].includes(type)) return null
  return UInput
})

const color = computed(() => (props.error ? 'red' : 'gray'))
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="!['checkbox', 'radio'].includes(field.type)" :for="field.name"
      class="text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ field.label }}
      <span v-if="field.required" class="text-red-500">*</span>
    </label>

    <component v-if="component" :is="component" v-model="model" :id="field.name" v-bind="field.attributes"
      :type="['password', 'email', 'url', 'date', 'file', 'image'].includes(field.type) ? field.type : undefined"
      :placeholder="field.attributes?.placeholder || field.label"
      :options="['select', 'dropdown', 'multiselect'].includes(field.type) ? options : undefined"
      :multiple="field.isMultiple" :error="!!error" :color="color" class="w-full" />

    <div v-else-if="field.type === 'checkbox'" class="flex flex-col gap-2">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ field.label }}
        <span v-if="field.required" class="text-red-500">*</span>
      </span>

      <div class="flex flex-col gap-1">
        <UCheckboxGroup v-model="model" :items="options" class="flex flex-col gap-1"></UCheckboxGroup>
      </div>
    </div>

    <URadioGroup v-else-if="field.type === 'radio'" v-model="model" :options="options" :legend="field.label"
      legend-class="text-sm font-medium text-gray-700 dark:text-gray-300" class="flex flex-col gap-1" />

    <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
  </div>
</template>
