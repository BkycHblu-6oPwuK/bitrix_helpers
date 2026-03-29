<!--
  Карточка дочернего раздела (подкатегории)
  Отображает название раздела и ссылку на него
  Используется в компоненте Sections для навигации по подразделам
-->
<script setup lang="ts">
import type { SectionDTO } from '~/types/iblock';


// Пропсы: данные раздела (id, название, URL)
const props = defineProps<{
    item: SectionDTO
}>()
</script>

<template>
    <NuxtLink :to="item.url || '#'"
        class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-lg transition-shadow">
        <div class="text-lg font-semibold mb-2">{{ item.name }}</div>
    </NuxtLink>
</template>