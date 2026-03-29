<!--
  Базовый layout сайта
  Содержит шапку, основной контент и футер
  Используется для всех страниц сайта
  Поддерживает темную тему с плавными переходами
-->
<template>
  <div
    class="flex flex-col min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300"
  >
    <!-- Шапка сайта с меню и поиском -->
    <Header />

    <!-- Основной контент страницы -->
    <main class="flex-1 container mx-auto px-4 py-8">
      <slot />
    </main>

    <!-- Подвал сайта с меню и копирайтом -->
    <Footer />
  </div>
</template>