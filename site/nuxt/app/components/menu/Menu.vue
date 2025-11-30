<!--
  Динамический рендерер меню
  Подгружает нужный компонент меню на основе menuType
  Поддерживаемые типы: top_menu, bottom_menu, catalog_menu
-->
<script setup lang="ts">
import type { MenuType, MenuData } from '~/types/menu'
import TopMenu from './blocks/TopMenu.vue'
import FooterMenu from './blocks/FooterMenu.vue'
import UnknownMenu from './blocks/UnknownMenu.vue'

// Пропсы: тип меню для отображения
const props = defineProps<{ menuType: MenuType }>()

// Получаем данные меню из composable
const { topMenu, bottomMenu } = useMenu()

// Маппинг типов меню на данные
const menuDataMap: Record<MenuType, MenuData | null> = {
  top_menu: topMenu,        // Верхнее меню
  catalog_menu: null,       // Меню каталога (загружается отдельно)
  bottom_menu: bottomMenu,  // Нижнее меню (футер)
}

// Маппинг типов меню на компоненты Vue
const componentsMap: Record<string, Component> = {
  top_menu: TopMenu,
  bottom_menu: FooterMenu,
}

/**
 * Резолвер компонента меню по типу
 * Возвращает UnknownMenu если тип неизвестен
 */
const resolveComponent = (type: MenuType) => {
  return componentsMap[type] || UnknownMenu
}

// Данные текущего меню
const menu = menuDataMap[props.menuType]
</script>

<template>
  <component v-if="menu?.menu" :is="resolveComponent(props.menuType)" :data="menu.menu" />
</template>