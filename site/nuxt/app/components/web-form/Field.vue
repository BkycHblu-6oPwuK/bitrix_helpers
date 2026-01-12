<script setup lang="ts">
import { computed } from 'vue'
import {
  UInput,
  UTextarea,
  USelect,
  URadioGroup,
  UCheckboxGroup
} from '#components'
import type { FormNewFieldDTO } from '~/types/web-form'

const props = defineProps<{
  field: FormNewFieldDTO
  error?: string
}>()

const model = defineModel<any>()

const options = computed(() =>
  props.field.options?.map(o => ({
    label: o.label,
    value: String(o.value),
    disabled: o.active === false
  })) ?? []
)

const isSelect = computed(() =>
  ['select', 'dropdown', 'multiselect'].includes(props.field.type)
)

const isCheckbox = computed(() => props.field.type === 'checkbox')
const isRadio = computed(() => props.field.type === 'radio')
const isFile = computed(() =>
  ['file', 'image'].includes(props.field.type)
)

const component = computed(() => {
  if (props.field.type === 'textarea') return UTextarea
  if (isSelect.value) return USelect
  if (isCheckbox.value || isRadio.value) return null
  return UInput
})

const inputType = computed(() => {
  if (isFile.value) return 'file'
  if (['password', 'email', 'url', 'date'].includes(props.field.type)) {
    return props.field.type
  }
  return undefined
})

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const files = input.files

  model.value = props.field.isMultiple
    ? Array.from(files ?? [])
    : files?.[0] ?? null
}

const color = computed(() => (props.error ? 'error' : 'secondary'))
</script>

<template>
  <div class="flex flex-col gap-1">
    <label
      v-if="!isCheckbox && !isRadio"
      :for="field.name"
      class="text-sm font-medium text-gray-700 dark:text-gray-300"
    >
      {{ field.label }}
      <span v-if="field.required" class="text-red-500">*</span>
    </label>

    <UInput
      v-if="isFile"
      type="file"
      :id="field.name"
      :multiple="field.isMultiple"
      v-bind="field.attributes"
      :error="!!error"
      :color="color"
      class="w-full"
      @change="onFileChange"
    />

    <component
      v-else-if="component"
      :is="component"
      v-model="model"
      :id="field.name"
      v-bind="field.attributes"
      :type="inputType"
      :placeholder="field.attributes?.placeholder || field.label"
      :items="isSelect ? options : undefined"
      :multiple="field.isMultiple"
      :error="!!error"
      :color="color"
      class="w-full"
    />

    <div v-else-if="isCheckbox" class="flex flex-col gap-2">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ field.label }}
        <span v-if="field.required" class="text-red-500">*</span>
      </span>

      <UCheckboxGroup
        v-model="model"
        :items="options"
        class="flex flex-col gap-1"
      />
    </div>

    <URadioGroup
      v-else-if="isRadio"
      v-model="model"
      :items="options"
      :legend="field.label"
      legend-class="text-sm font-medium text-gray-700 dark:text-gray-300"
      class="flex flex-col gap-1"
    />

    <p v-if="error" class="text-xs text-red-500 mt-1">
      {{ error }}
    </p>
  </div>
</template>