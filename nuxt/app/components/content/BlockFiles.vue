<script setup lang="ts">
import type { ContentBlockFiles } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockFiles }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--files"></div>
</template>
