<script setup lang="ts">
import type { ContentBlockIblockElements, ContentBlockIblockSections } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockIblockElements | ContentBlockIblockSections }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--iblock-list"></div>
</template>
