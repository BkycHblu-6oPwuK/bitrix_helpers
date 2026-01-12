<!--
  Главная страница сайта
  Отображает динамические блоки контента с сервера
  Добавлены тестовые ссылки для навигации
-->
<template>
  <div>
    <!-- Динамические блоки контента с сервера -->
    <Main></Main>
    
    <!-- Тестовый блок со ссылками -->
    <section class="text-center py-20">
      <NuxtLink to="/catalog"
        class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg shadow hover:bg-indigo-700 transition-all">
        Перейти в каталог
      </NuxtLink>
      <NuxtLink to="/user-admin/123"
        class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg shadow hover:bg-indigo-700 transition-all">
        user
      </NuxtLink>
    </section>
  </div>
</template>