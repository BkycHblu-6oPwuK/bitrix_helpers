<script setup lang="ts">
import type { MenuBlock } from '~/types/menu'

const props = defineProps<{ data: MenuBlock }>()
</script>

<template>
  <nav
    class="w-full bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700
           text-gray-800 dark:text-gray-200 transition-colors duration-300"
  >
    <div class="container mx-auto px-4 flex items-center justify-start gap-6 py-2">
      <div class="relative group"
      >
        <NuxtLink
          :to="data.LINK || '#'"
          class="font-medium hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
          {{ data.NAME }}
        </NuxtLink>

        <div
          v-if="data.CHILDREN?.length"
          class="absolute left-0 top-full mt-2 hidden group-hover:block
                 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                 rounded-lg shadow-lg min-w-[200px] py-2 z-50"
        >
          <NuxtLink
            v-for="child in data.CHILDREN"
            :key="child.ID"
            :to="child.LINK || '#'"
            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300
                   hover:bg-gray-100 dark:hover:bg-gray-700
                   hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
          >
            {{ child.NAME }}
          </NuxtLink>
        </div>
      </div>
    </div>
  </nav>
</template>
