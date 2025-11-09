<script setup lang="ts">
import type { MenuData, MenuType } from '~/types/menu'
import TopMenu from './blocks/TopMenu.vue'
import CatalogMenu from './blocks/CatalogMenu.vue'
import FooterMenu from './blocks/FooterMenu.vue'
import UnknownMenu from './blocks/UnknownMenu.vue'

const props = defineProps<{ menuType: MenuType }>()

const { data } = await useApi<MenuData>('get-menu', {
  query: { menuType: props.menuType },
})

const componentsMap: Record<MenuType, Component> = {
  top_menu: TopMenu,
  catalog_menu: CatalogMenu,
  bottom_menu: FooterMenu,
}

const resolveComponent = (type: MenuType) => {
  return componentsMap[type] || UnknownMenu
}
</script>

<template>
  <div>
    <component v-for="(block, i) in data?.data?.menu" :is="resolveComponent(props.menuType)" :key="i" :data="block" />
  </div>
</template>
