<script setup lang="ts">
import { ref } from 'vue'
import { useColorMode } from '#imports'
import Menu from '~/components/menu/Menu.vue'
import AuthModal from '~/components/auth/AuthModal.vue'

const colorMode = useColorMode()
const query = ref('')
const isAuthModalOpen = ref(false)

const toggleTheme = () => {
  colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
}

const onSearch = () => {
  console.log('Search:', query.value)
}

const openAuthModal = () => {
  isAuthModalOpen.value = true
}
</script>

<template>
  <header class="w-full border-b bg-white dark:bg-gray-900">
    <div class="text-sm">
      <UContainer class="flex justify-between items-center py-2">
        <div class="flex items-center gap-3">
          <UButton icon="i-heroicons-swatch" variant="ghost" size="sm" @click="toggleTheme">
            <ClientOnly>
              {{ colorMode.value === 'dark' ? 'Светлая тема' : 'Тёмная тема' }}
            </ClientOnly>
          </UButton>

          <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
            <UIcon name="i-heroicons-map-pin" class="w-4 h-4" />
            <span>Омск</span>
          </div>
        </div>

        <div class="flex items-center gap-6">
          <div>
            <Menu menu-type="top_menu"></Menu>
          </div>
          <span class="font-medium text-gray-800 dark:text-gray-200">
            8-800-77-07-999
          </span>
        </div>
      </UContainer>
    </div>

    <div class="py-3">
      <UContainer class="flex flex-wrap items-center justify-between gap-4">
        <NuxtLink to="/" class="flex items-center gap-2">
          <div class="bg-gradient-to-r from-orange-500 to-amber-400 text-white font-bold text-lg px-4 py-2 rounded-md">
            logo
          </div>
        </NuxtLink>

        <Menu menu-type="catalog_menu" />

        <div class="flex-1 max-w-xl">
          <UInput v-model="query" placeholder="Поиск по сайту" icon="i-heroicons-magnifying-glass" size="lg"
            class="w-full" @keyup.enter="onSearch" />
        </div>

        <div class="flex items-center gap-3">
          <UButton variant="ghost" icon="i-heroicons-chart-bar" label="Сравнение" size="sm" />
          <UButton variant="ghost" icon="i-heroicons-heart" label="Избранное" size="sm" />
          <UButton variant="ghost" icon="i-heroicons-shopping-cart" label="Корзина" size="sm" />
          <UButton variant="ghost" icon="i-heroicons-user" label="Профиль" size="sm" @click="openAuthModal" />
        </div>
      </UContainer>
    </div>

    <!-- Модальное окно авторизации -->
    <AuthModal v-model="isAuthModalOpen" />
  </header>
</template>
