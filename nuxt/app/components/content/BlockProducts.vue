<script setup lang="ts">
import type { ContentBlockProducts } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockProducts }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--products"></div>
</template>
