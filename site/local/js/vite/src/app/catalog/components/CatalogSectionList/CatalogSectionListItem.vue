<script setup>

const { item } = defineProps({
    item: {
        type: Object,
        required: true
    },
});
</script>

<template>
    <a class="swiper-slide sections-slider__item" :href="item.url" :id="item.id">
        <div class="sections-slider__item-image">
            <img :src="item.picture" :alt="item.name">
        </div>
        <div class="sections-slider__item-title">
            {{ item.name }}
        </div>
    </a>
</template>