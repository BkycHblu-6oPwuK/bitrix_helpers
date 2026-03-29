<script setup>
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    showReview: {
        type: Boolean,
        default: false
    }
});
const addReview = () => {
    const url = new URL(props.item.url, window.location.origin);
    url.searchParams.append('setReviews', 'Y');
    window.location.href = url.href
}
</script>
<template>
    <a :href="item.url" class="product-card-ordered">
        <div class="product-card-ordered__image">
            <img :src="item.imageSrc" :alt="item.name">
        </div>
        <div class="product-card-ordered__info">
            <span class="product-card-ordered__price" v-html="`${item.price} ₽`"></span>
            <span class="product-card-ordered__name">{{ item.name }}</span>
            <span class="product-card-ordered__size" v-if="item.razmer">Размер: {{ item.razmer }}</span>
        </div>
        <button class="product-card-ordered__review-btn" v-if="showReview" @click.prevent="addReview">Написать отзыв</button>
    </a>
</template>