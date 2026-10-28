<script setup lang="ts">
import type { ContentBlockProperties } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockProperties }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--properties"></div>
</template>
