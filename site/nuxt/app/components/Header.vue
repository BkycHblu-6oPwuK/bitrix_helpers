<!--
  Компонент шапки сайта
  Содержит:
  - Верхнюю панель с переключателем темы, городом и меню
  - Основную панель с логотипом, меню каталога, поиском и иконками
  Поддерживает темную/светлую тему
-->
<script setup lang="ts">
import { ref } from 'vue'
import { useColorMode } from '#imports'
import Menu from '~/components/menu/Menu.vue'
import CatalogMenu from '~/components/menu/CatalogMenu.vue'
import AuthModal from '~/components/auth/AuthModal.vue'

// Управление цветовой темой (dark/light)
const colorMode = useColorMode()

// Stores
const userStore = useUserStore()
// Строка поиска
const query = ref('')

// Состояние модального окна авторизации
const isAuthModalOpen = ref(false)

/**
 * Переключение между светлой и темной темой
 */
const toggleTheme = () => {
  colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
}

/**
 * Обработчик поиска (TODO: реализовать логику)
 */
const onSearch = () => {
  console.log('Search:', query.value)
}

/**
 * Открытие модального окна авторизации
 */
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

        <CatalogMenu />

        <div class="flex-1 max-w-xl">
          <UInput v-model="query" placeholder="Поиск по сайту" icon="i-heroicons-magnifying-glass" size="lg"
            class="w-full" @keyup.enter="onSearch" />
        </div>

        <div class="flex items-center gap-3">
          <NuxtLink to="/favourite" class="flex items-center gap-2 px-2 py-1 rounded text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
            <UIcon name="i-heroicons-heart" class="w-5 h-5" />
            <span>Избранное</span>
          </NuxtLink>

          <NuxtLink to="/basket" class="flex items-center gap-2 px-2 py-1 rounded text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
            <UIcon name="i-heroicons-shopping-cart" class="w-5 h-5" />
            <span>Корзина</span>
          </NuxtLink>
          
          <UButton 
            v-if="!userStore.isAuthenticated"
            variant="ghost" 
            icon="i-heroicons-user" 
            label="Войти" 
            size="sm" 
            @click="openAuthModal" 
          />
          
          <UDropdownMenu v-else :items="[
            [{ label: 'Профиль', icon: 'i-heroicons-user', onSelect: () => navigateTo('/profile') }],
            [{ label: 'Заказы', icon: 'i-heroicons-shopping-bag', onSelect: () => navigateTo('/orders') }],
            [{ label: 'Выйти', icon: 'i-heroicons-arrow-right-on-rectangle', onSelect: () => userStore.logout() }]
          ]">
            <UButton variant="ghost" size="sm">
              <div class="flex items-center gap-2">
                <UIcon name="i-heroicons-user" class="w-5 h-5" />
                <span>{{ userStore.fullName || 'Профиль' }}</span>
              </div>
            </UButton>
          </UDropdownMenu>
        </div>
      </UContainer>
    </div>

    <AuthModal v-model="isAuthModalOpen" @close="isAuthModalOpen = false" />
  </header>
</template>
