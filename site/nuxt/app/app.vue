<!--
  Корневой компонент Nuxt приложения
  Оборачивает все страницы в layout и рендерит текущую страницу
  NuxtLayout использует layouts/default.vue по умолчанию
-->
<script setup lang="ts">
const authStore = useAuthStore()

onMounted(() => {
  // Инициализация fuser токена при монтировании приложения
  useBootstrap()
  
  // Инициализация состояния авторизации из localStorage
  authStore.initFromStorage()
});
</script>
<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
