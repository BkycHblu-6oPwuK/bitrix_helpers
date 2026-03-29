<!--
  Главная страница сайта
  Отображает динамические блоки контента с сервера
  Добавлены тестовые ссылки для навигации
-->

<script setup lang="ts">
import type { MainPageContent } from '~/types/iblock/content';
import type { PageData } from '~/types/page';

const page = await useApi<PageData<MainPageContent>>('get-main-page')
</script>

<template>
  <div>
    <!-- Динамические блоки контента с сервера -->
    <Main v-if="page.data.value?.data?.page" :data="page.data.value.data.page"></Main>

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