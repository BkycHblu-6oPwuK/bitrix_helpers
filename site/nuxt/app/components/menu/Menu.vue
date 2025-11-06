<script setup lang="ts">
import type { MenuData, MenuType } from '~/types/menu'

const props = defineProps<{ menuType: MenuType }>()

const menu = await useApi<MenuData>('get-menu', {
  query: { menuType: props.menuType },
})

const componentsMap = {
  catalog_menu: defineAsyncComponent(() => import('./blocks/CatalogMenu.vue')),
  bottom_menu: defineAsyncComponent(() => import('./blocks/FooterMenu.vue')),
}

const resolveComponent = (type: MenuType) => {
  return componentsMap[type as keyof typeof componentsMap]
    || defineAsyncComponent(() => import('./blocks/UnknownMenu.vue'))
}
</script>

<template>
  <div class="menu-wrapper">
    <component
      v-for="(block, i) in menu.data.value?.data?.menu"
      :is="resolveComponent(props.menuType)"
      :key="i"
      :data="block"
    />
  </div>
</template>
