<script setup lang="ts">
import type { ContentBlockContainer } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockContainer }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--container">
        <ContentBlocks :blocks="block.content.blocks" />
    </div>
</template>
