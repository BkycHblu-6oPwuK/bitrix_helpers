<!--
  Содержимое страницы избранного: разделы и список товаров.
  «Показать ещё» и пагинация обрабатываются внутри CatalogSection через контекст раздела.
-->
<script setup lang="ts">
const { isLoading } = useSection()
</script>

<template>
    <div class="catalog-page relative" :aria-busy="isLoading">
        <!-- Контент не размонтируем на время загрузки (иначе при «Показать ещё» список пропадал бы),
             а затемняем и показываем индикатор поверх -->
        <div v-if="isLoading"
            class="absolute inset-0 z-10 flex items-start justify-center pt-24 bg-white/60 dark:bg-gray-900/60">
            <div class="text-lg">Загрузка...</div>
        </div>

        <div class="grid gap-8 transition-opacity" :class="{ 'opacity-50 pointer-events-none': isLoading }">
            <main class="lg:col-span-3">
                <CatalogSection />
            </main>
        </div>
    </div>
</template>