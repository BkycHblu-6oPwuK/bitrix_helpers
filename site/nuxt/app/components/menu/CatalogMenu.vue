<script setup lang="ts">
import { watch, ref } from 'vue'
import type { MenuData } from '~/types/menu'

const isOpen = ref(false)

const { data, pending, error, execute } = await useApi<MenuData>('get-menu', {
  query: { menuType: 'catalog' },
  immediate: false,
})

// следим за открытием попапа
watch(isOpen, async (value) => {
  if (value) {
    console.log('CatalogMenu opened')
    // загружаем только если ещё не загружено и сейчас не грузится
    if (!data.value && !pending.value) {
      execute()
    }
  }
})
</script>

<template>
  <UPopover
    v-model:open="isOpen"
    :popper="{ placement: 'bottom-start', offset: 6 }"
    mode="hover"
  >
    <template #default>
      <UButton
        color="warning"
        variant="solid"
        icon="i-heroicons-bars-3"
        trailing
        class="!rounded-lg text-white font-semibold"
      >
        Каталог
      </UButton>
    </template>

    <template #content>
      <div
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
               rounded-lg shadow-lg p-4 min-w-[420px]
               text-gray-800 dark:text-gray-200 transition-colors"
      >
        <div v-if="pending" class="flex items-center justify-center p-4">
          Загрузка...
        </div>
        <div v-else-if="error" class="flex items-center justify-center p-4 text-red-500">
          Ошибка загрузки
        </div>
        <div
          v-else-if="data?.data?.menu"
          class="grid grid-cols-2 gap-6"
        >
          <div
            v-for="item in data.data.menu"
            :key="item.id"
            class="space-y-2"
          >
            <NuxtLink
              :to="item.link || '#'"
              class="block font-medium text-gray-800 dark:text-gray-100
                     hover:text-primary-600 dark:hover:text-primary-400
                     transition-colors"
            >
              {{ item.name }}
            </NuxtLink>

            <ul
              v-if="item.children?.length"
              class="space-y-1 text-sm text-gray-600 dark:text-gray-400"
            >
              <li
                v-for="child in item.children"
                :key="child.id"
              >
                <NuxtLink
                  :to="child.link || '#'"
                  class="block pl-2 border-l border-gray-100 dark:border-gray-700
                         hover:border-primary-400 dark:hover:border-primary-500
                         hover:text-primary-600 dark:hover:text-primary-400
                         transition-colors"
                >
                  {{ child.name }}
                </NuxtLink>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </template>
  </UPopover>
</template>
