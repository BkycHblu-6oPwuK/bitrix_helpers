<script setup lang="ts">

const props = defineProps<{
  loading?: boolean
  disabled?: boolean
  label?: string
}>()

// Разрешаем внешним родителям переопределять действие кнопки
const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()

</script>

<template>
  <button
    type="submit"
    :disabled="props.loading"
    class="inline-flex cursor-pointer items-center justify-center gap-2 px-4 py-2.5 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 disabled:opacity-60 transition"
  >
    <span v-if="!props.loading">Отправить</span>
    <span v-else class="flex items-center gap-2">
      <svg class="w-4 h-4 animate-spin text-white" viewBox="0 0 24 24">
        <circle
          class="opacity-25"
          cx="12" cy="12" r="10"
          stroke="currentColor" stroke-width="4"
          fill="none"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8v4l3-3-3-3v4a10 10 0 00-10 10h4z"
        />
      </svg>
      Отправка...
    </span>
  </button>
</template>
