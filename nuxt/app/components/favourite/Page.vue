<!--
  Провайдер состояния страницы избранного.
  Только создаёт состояние раздела; всё отображение — в FavouriteContent
  (там используются useSection / useSectionList, которым нужен provide от родителя).
-->
<script setup lang="ts">
import type { FavouritePageDTO } from '~/types/pages/favourite'

const props = defineProps<{
    favourite: FavouritePageDTO
    apiUrl: string
}>()

provideSectionPage(props.favourite, { apiUrl: props.apiUrl })
</script>

<template>
    <FavouriteContent />
</template>