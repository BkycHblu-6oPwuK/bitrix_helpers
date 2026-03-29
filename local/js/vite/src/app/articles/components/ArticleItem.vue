<script setup>
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const wideCard = props.item.PROPERTIES.PROP_WIDE_CARD.VALUE
const imageUrl = !!wideCard ? props.item.PROPERTIES.PROP_IMAGE_LIST_WIDE.PATH : props.item.PROPERTIES.PROP_IMAGE_LIST.PATH
const originalDate = props.item.DATE_CREATE;
const modifiedDate = originalDate.split(' ')[0];
</script>

<template>
    <a :href="props.item.DETAIL_PAGE_URL" class="articles-list__item" :class="{ 'big': !!wideCard }">
        <div class="articles-list__item-image">
            <img :src="imageUrl" :alt="props.item.DETAIL_PICTURE.ALT">
        </div>
        <div class="articles-list__item-info">
            <time datetime="">{{ modifiedDate }}</time>
            <span class="articles-list__item-title">{{ props.item.NAME }}</span>
            <p>{{ props.item.PREVIEW_TEXT }}</p>
            <span class="articles-list__item-link">Подробнее</span>
        </div>
    </a>
</template>