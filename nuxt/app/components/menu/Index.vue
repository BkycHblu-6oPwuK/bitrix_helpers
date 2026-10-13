<!--
  Динамический рендерер меню
  Подгружает нужный компонент меню на основе menuType
  Поддерживаемые типы: top_menu, bottom_menu, catalog_menu
-->
<script setup lang="ts">
import { MenuBlocksFooterMenu, MenuBlocksTopMenu, MenuBlocksUnknownMenu } from '#components';
import type { MenuType, MenuData } from '~/types/menu'

// Пропсы: тип меню для отображения
const props = defineProps<{ menuType: MenuType }>()

// Получаем данные меню из composable
const { getTopMenu, getBottomMenu } = useMenu()

// Маппинг типов меню на данные
const menuDataMap: Record<MenuType, (() => MenuData) | null> = {
    top_menu: getTopMenu,        // Верхнее меню
    bottom_menu: getBottomMenu,  // Нижнее меню (футер)
}

// Маппинг типов меню на компоненты Vue
const componentsMap: Record<string, Component> = {
    top_menu: MenuBlocksTopMenu,
    bottom_menu: MenuBlocksFooterMenu,
}

/**
 * Резолвер компонента меню по типу
 * Возвращает UnknownMenu если тип неизвестен
 */
const resolveComponent = (type: MenuType) => {
    return componentsMap[type] || MenuBlocksUnknownMenu
}

// Данные текущего меню
const menu = menuDataMap[props.menuType]
</script>

<template>
    <component v-if="menu?.()" :is="resolveComponent(props.menuType)" :data="menu?.().menu" />
</template>