<script setup lang="ts">
import type { ContentBlockAccordion } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockAccordion }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--accordion"></div>
</template>
