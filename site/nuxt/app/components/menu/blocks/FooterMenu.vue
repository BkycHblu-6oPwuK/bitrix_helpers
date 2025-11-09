<script setup lang="ts">
import type { MenuBlock } from '~/types/menu'

const props = defineProps<{
  data: MenuBlock
}>()
console.log(props.data)
</script>

<template>
  <div class="flex flex-col gap-2">
    <h3
      v-if="data.NAME"
      class="text-base font-semibold text-gray-900 dark:text-gray-100"
    >
      {{ data.NAME }}
    </h3>
    <ul v-if="data.CHILDREN?.length" class="flex flex-col gap-1">
      <li
        v-for="(item, i) in data.CHILDREN"
        :key="i"
      >
        <a
          :href="item.LINK || '#'"
          class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition"
        >
          {{ item.NAME }}
        </a>
      </li>
    </ul>
  </div>
</template>
