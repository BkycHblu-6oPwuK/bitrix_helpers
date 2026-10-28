<script setup lang="ts">
import type { ContentBlockVideo } from '~/types/iblock/content-blocks'

const props = defineProps<{ block: ContentBlockVideo }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--video"></div>
</template>
