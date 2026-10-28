<script setup lang="ts">
import type { ContentBlockEmbed } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockEmbed }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--embed"></div>
</template>