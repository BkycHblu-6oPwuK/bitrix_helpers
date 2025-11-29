<script setup lang="ts">
import type { MenuItem } from '~/types/menu'

defineProps<{ data: MenuItem[] }>()
</script>

<template>
  <nav
    class="w-full bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700
           text-gray-800 dark:text-gray-200 transition-colors duration-300"
  >
    <div class="container mx-auto px-4 flex items-center justify-start gap-6 py-2">
      <NuxtLink
        v-for="item in data"
        :key="item.id"
        :to="item.link || '#'"
        class="font-medium hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
      >
        {{ item.name }}
      </NuxtLink>
    </div>
  </nav>
</template>