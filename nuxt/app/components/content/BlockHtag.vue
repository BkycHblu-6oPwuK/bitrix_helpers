<script setup lang="ts">
import type { ContentBlockHtag } from '~/types/iblock/content-blocks'

defineProps<{ block: ContentBlockHtag }>()
</script>

<template>
    <div v-if="false" class="content-block content-block--htag">
        <a v-if="block.content.anchor" :name="block.content.anchor" />
        <component :is="block.type" class="content-block__heading">
            {{ block.content.value }}
        </component>
    </div>
</template>
