<script setup lang="ts">
import type { ContentBlockImage } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockImage }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--image"></div>
</template>
