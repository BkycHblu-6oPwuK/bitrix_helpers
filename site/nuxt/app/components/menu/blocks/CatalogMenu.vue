<script setup lang="ts">
import type { MenuBlock } from '~/types/menu'
const props = defineProps<{ data: MenuBlock }>()
</script>

<template>
  <UPopover :popper="{ placement: 'bottom-start', offset: 6 }" mode="hover">
    <UButton
      color="warning"
      variant="solid"
      icon="i-heroicons-bars-3"
      trailing
      class="!rounded-lg text-white font-semibold"
    >
      {{ data.NAME }}
    </UButton>

    <template #content>
      <div
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
               rounded-lg shadow-lg p-4 grid grid-cols-2 gap-6 min-w-[420px]
               text-gray-800 dark:text-gray-200 transition-colors"
      >
        <div
          v-for="(item, i) in data.CHILDREN || []"
          :key="i"
          class="space-y-2"
        >
          <!-- Основной раздел -->
          <NuxtLink
            :to="item.LINK || '#'"
            class="block font-medium text-gray-800 dark:text-gray-100
                   hover:text-primary-600 dark:hover:text-primary-400
                   transition-colors"
          >
            {{ item.NAME }}
          </NuxtLink>

          <!-- Подразделы -->
          <ul
            v-if="item.CHILDREN?.length"
            class="space-y-1 text-sm text-gray-600 dark:text-gray-400"
          >
            <li
              v-for="child in item.CHILDREN"
              :key="child.ID"
            >
              <NuxtLink
                :to="child.LINK || '#'"
                class="block pl-2 border-l border-gray-100 dark:border-gray-700
                       hover:border-primary-400 dark:hover:border-primary-500
                       hover:text-primary-600 dark:hover:text-primary-400
                       transition-colors"
              >
                {{ child.NAME }}
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </UPopover>
</template>
