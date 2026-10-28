<script setup lang="ts">
import type { ContentBlock } from '~/types/iblock/content-blocks';
import {
    ContentBlockText,
    ContentBlockHtag,
    ContentBlockImage,
    ContentBlockGallery,
    ContentBlockVideo,
    ContentBlockTable,
    ContentBlockLists,
    ContentBlockButtonLink,
    ContentBlockFiles,
    ContentBlockAccordion,
    ContentBlockContainer,
    ContentBlockComplexImageText,
    ContentBlockComplexVideoText,
    ContentBlockContents,
    ContentBlockProperties,
    ContentBlockVideoGallery,
    ContentBlockYandexMap,
    ContentBlockIblockList,
    ContentBlockProducts,
    ContentBlockEmbed,
} from '#components';

const props = defineProps<{
    blocks: ContentBlock[]
}>()

const COMPONENTS: Partial<Record<ContentBlock['type'], Component>> = {
    text: ContentBlockText,
    textfield: ContentBlockText,
    selectfield: ContentBlockText,
    image: ContentBlockImage,
    gallery: ContentBlockGallery,
    video: ContentBlockVideo,
    table: ContentBlockTable,
    lists: ContentBlockLists,
    button_link: ContentBlockButtonLink,
    files: ContentBlockFiles,
    accordion: ContentBlockAccordion,
    container: ContentBlockContainer,
    complex_image_text: ContentBlockComplexImageText,
    complex_video_text: ContentBlockComplexVideoText,
    contents: ContentBlockContents,
    properties: ContentBlockProperties,
    video_gallery: ContentBlockVideoGallery,
    yandex_map: ContentBlockYandexMap,
    iblock_elements: ContentBlockIblockList,
    iblock_sections: ContentBlockIblockList,
    products: ContentBlockProducts,
    instagram: ContentBlockEmbed,
    twitter: ContentBlockEmbed,

    h1: ContentBlockHtag,
    h2: ContentBlockHtag,
    h3: ContentBlockHtag,
    h4: ContentBlockHtag,
    h5: ContentBlockHtag,
    h6: ContentBlockHtag,
} as const

function resolveComponent(type: ContentBlock['type']) {
    return COMPONENTS[type]
}
</script>

<template>
    <div class="content-blocks">
        <template v-for="(block, i) in blocks" :key="i">
            <component :is="resolveComponent(block.type)" v-if="resolveComponent(block.type)" :block="block"
                class="content-blocks__item" />
        </template>
    </div>
</template>
