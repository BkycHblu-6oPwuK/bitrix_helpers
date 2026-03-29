<!--
  Меню футера с колонками
  Отображает группы ссылок в нижней части сайта
  Каждая колонка - это группа с заголовком и списком children
-->
<script setup lang="ts">
import type { MenuItem } from '~/types/menu'

// Пропсы: массив колонок меню футера
defineProps<{ data: MenuItem[] }>()
</script>

<template>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
    <div v-for="column in data" :key="column.id" class="flex flex-col gap-2">
      <h3
        class="text-base font-semibold text-gray-900 dark:text-gray-100"
      >
        {{ column.name }}
      </h3>
      <ul v-if="column.children?.length" class="flex flex-col gap-1">
        <li
          v-for="item in column.children"
          :key="item.id"
        >
          <a
            :href="item.link || '#'"
            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition"
          >
            {{ item.name }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>