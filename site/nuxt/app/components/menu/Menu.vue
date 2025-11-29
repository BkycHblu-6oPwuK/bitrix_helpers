<script setup lang="ts">
import type { MenuType, MenuData } from '~/types/menu'
import TopMenu from './blocks/TopMenu.vue'
import FooterMenu from './blocks/FooterMenu.vue'
import UnknownMenu from './blocks/UnknownMenu.vue'

const props = defineProps<{ menuType: MenuType }>()

const { topMenu, bottomMenu } = useMenu()

const menuDataMap: Record<MenuType, MenuData | null> = {
  top_menu: topMenu,
  catalog_menu: null,
  bottom_menu: bottomMenu,
}

const componentsMap: Record<string, Component> = {
  top_menu: TopMenu,
  bottom_menu: FooterMenu,
}

const resolveComponent = (type: MenuType) => {
  return componentsMap[type] || UnknownMenu
}

const menu = menuDataMap[props.menuType]
</script>

<template>
  <component v-if="menu?.menu" :is="resolveComponent(props.menuType)" :data="menu.menu" />
</template>