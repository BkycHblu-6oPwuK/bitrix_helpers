<script setup>
import { useStore } from 'vuex';
import { computed } from 'vue';
import ReviewsStars from '@/common/components/ReviewsStars.vue';

const store = useStore();
const product = computed(() => store.getters.getProduct);
const reviews = computed(() => store.getters.getReviews);
const name = computed(() => {
    return product.value.name ? product.value.name : product.value.original_name;
})
const emits = defineEmits(['scrollToReviews']);
</script>

<template>
    <div class="product-card-details__name-block">
        <span class="product-card-details__name">{{ name }}</span>
        <div class="product-card-details__head">
            <div class="product-card-details__head-num" v-if="product.model || product.article"><span v-if="product.model">Модель {{ product.model }}</span><span v-if="product.article">Артикул {{ product.article }}</span></div>
            <div class="product-card-details__head-stars">
                <ReviewsStars :eval="reviews.eval_info.avg" :width="18" :height="18"></ReviewsStars>
            </div>
            <a class="product-card-details__head-reviews" @click="emits('scrollToReviews')">{{ reviews.eval_info.count }}</a>
        </div>
    </div>
</template>