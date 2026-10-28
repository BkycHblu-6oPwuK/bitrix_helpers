<script setup lang="ts">
import type { ContentBlockTable } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockTable }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--table"></div>
</template>
