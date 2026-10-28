<script setup lang="ts">
import type { ContentBlockContents } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockContents }>()
</script>

<template>
    <nav v-if="false" class="content-block content-block--contents"></nav>
</template>
