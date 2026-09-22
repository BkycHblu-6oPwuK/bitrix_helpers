<!--
  Корневой компонент Nuxt приложения
  Оборачивает все страницы в layout и рендерит текущую страницу
  NuxtLayout использует layouts/default.vue по умолчанию
-->
<script setup lang="ts">
const { initClient, initServer } = useBootstrap();

await callOnce('bootstrap-server-init', () => initServer());

onMounted(() => {
    initClient();
});
</script>
<template>
    <NuxtLayout>
        <NuxtPage />
    </NuxtLayout>
</template>
