<script setup lang="ts">
import type { ContentBlockLists } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockLists }>()
</script>

<template>
    <div class="content-block content-block--lists" v-html="block.content.value" />
</template>
