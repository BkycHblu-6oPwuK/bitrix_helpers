<script setup lang="ts">
import type { ContentBlockGallery } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockGallery }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--gallery"></div>
</template>
