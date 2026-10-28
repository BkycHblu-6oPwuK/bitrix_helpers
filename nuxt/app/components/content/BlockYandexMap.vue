<script setup lang="ts">
import type { ContentBlockYandexMap } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockYandexMap }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--yandex-map"></div>
</template>
