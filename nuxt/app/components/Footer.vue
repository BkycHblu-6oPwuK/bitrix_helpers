<!--
  Компонент футера (подвала) сайта
  Отображает нижнее меню с группами ссылок
  Содержит копирайт и дополнительную информацию
-->
<template>
  <footer class="bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4 py-10">
      <!-- Нижнее меню с группами ссылок -->
      <div
        class="flex flex-wrap justify-start gap-8 sm:gap-12 md:gap-16"
      >
        <Menu menu-type="bottom_menu" />
      </div>
      
      <!-- Копирайт -->
      <div class="mt-10 text-center text-sm text-gray-500 dark:text-gray-400">
        © 2025 Магазин «BeerAlex». Все права защищены.
      </div>
    </div>
  </footer>
</template>
