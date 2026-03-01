<script setup>
const props = defineProps({
    item: Object
})
</script>

<template>
    <a :href="item.url" class="product-card-small-photo swiper-slide">
        <img :src="props.item.imageSrc" :alt="props.item.name">
    </a>
</template>