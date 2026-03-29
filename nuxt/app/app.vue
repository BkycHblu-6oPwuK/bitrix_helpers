<!--
  Корневой компонент Nuxt приложения
  Оборачивает все страницы в layout и рендерит текущую страницу
  NuxtLayout использует layouts/default.vue по умолчанию
-->
<script setup lang="ts">
onMounted(() => {
  // Инициализация fuser токена при монтировании приложения
  useBootstrap()
});
</script>
<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
