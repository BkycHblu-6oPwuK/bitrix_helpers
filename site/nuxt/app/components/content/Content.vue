<!--
  Динамический рендерер блоков контента страницы
  Загружает данные страницы с сервера по pathName
  Динамически подгружает нужные компоненты блоков на основе их типа
  Позволяет создавать гибкую структуру страниц из разных блоков
-->
<script setup lang="ts">
import type { PageData } from '~/types/iblock/content';

// Импорты всех доступных типов блоков
import MainBanner from './blocks/MainBanner.vue'
import SliderArticles from './blocks/SliderArticles.vue'
import Form from './blocks/Form.vue'
import Slider from './blocks/Slider.vue'
import UnknownBlock from './blocks/UnknownBlock.vue'

// Пропсы: путь к странице для загрузки контента
const props = defineProps<{
  pathName: string
}>()

// Загрузка данных страницы с сервера
const page = await useApi<PageData>('get-content', {
  query: {
    pathName: props.pathName
  },
})

// Маппинг типов блоков на компоненты Vue
const componentsMap = {
  main_banner: MainBanner,       // Главный баннер
  slider_articles: SliderArticles, // Слайдер со статьями
  form: Form,                    // Форма (web-форма Bitrix)
  slider: Slider,                // Обычный слайдер
  unknown: UnknownBlock,         // Заглушка для неизвестных блоков
}

/**
 * Резолвер компонента по типу блока
 * Если тип неизвестен - возвращает UnknownBlock
 */
const resolveComponent = (type: string) => {
  return componentsMap[type as keyof typeof componentsMap] || UnknownBlock
}
</script>

<template>
  <div class="page-content">
    <component
      v-for="(block, i) in page.data.value?.data?.page"
      :is="resolveComponent(block.type)"
      :key="i"
      :data="block.result"
    />
  </div>
</template>
