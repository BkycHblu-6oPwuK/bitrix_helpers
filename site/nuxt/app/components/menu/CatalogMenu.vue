<!--
  Меню каталога с ленивой загрузкой
  Открывается при наведении на кнопку "Каталог"
  Данные загружаются только при первом открытии
  Клик по кнопке ведет на страницу каталога
-->
<script setup lang="ts">
import { watch, ref, computed } from 'vue'
import type { MenuData } from '~/types/menu'

// Состояние открытия поповера
const isOpen = ref(false)

// Ссылка на API объект (lazy-loaded)
const api = ref<any>(null)

// Computed свойства для удобного доступа к данным API
const data = computed(() => api.value?.data)
const pending = computed(() => api.value?.pending)
const error = computed(() => api.value?.error)

/**
 * Инициализация API объекта (только при первом вызове)
 */
async function ensureApi() {
  if (!api.value) {
    api.value = await useApi<MenuData>('get-menu', {
      query: { menuType: 'catalog' },
    })
  }
}

/**
 * Загрузка данных меню, если еще не загружено
 */
async function fetchMenuIfNeeded() {
  await ensureApi()
  if (!data.value && !pending.value) {
    await api.value.execute()
  }
}

/**
 * Переход на страницу каталога по клику на кнопку
 */
function toCatalog() {
  navigateTo('/catalog')
}

// Загружаем данные при открытии поповера
watch(isOpen, async (value) => {
  if (value) {
    await fetchMenuIfNeeded()
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
        class="cursor-pointer !rounded-lg text-white font-semibold"
        @click="toCatalog"
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
