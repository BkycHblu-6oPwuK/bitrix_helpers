<script setup lang="ts">
import type { ContentBlockText } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockText }>()
</script>

<template>
    <div class="content-block content-block--text" v-html="block.content.value" />
</template>
