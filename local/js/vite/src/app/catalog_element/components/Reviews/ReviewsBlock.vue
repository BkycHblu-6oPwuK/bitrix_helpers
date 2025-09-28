<script setup>
import { computed, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import ReviewsForm from './ReviewsForm.vue';
import { plural } from '@/common/js/helpers';
import { useStore } from 'vuex';
import ReviewsStars from '@/common/components/ReviewsStars.vue';
import Swiper from 'swiper';

const store = useStore();
const reviews = computed(() => store.getters.getReviews);
const reviewsForm = useTemplateRef('reviewsForm');
const pluralText = computed(() => reviews.value.eval_info.countNumber + ' ' + plural(reviews.value.eval_info.countNumber, ['отзыва', 'отзывов', 'отзывов']))

const closeKeydownHandler = (e) => {
    e.key === 'Escape' && closeForm();
}
const closeFromDocument = (e) => {
    e.target === reviewsForm.value.reviewsFormOverlay && closeForm();
}
const openForm = () => {
    if (reviewsForm.value.reviewsFormOverlay) {
        document.addEventListener('keydown', closeKeydownHandler);
        document.addEventListener('click', closeFromDocument)
    }

}
const closeForm = () => {
    if (reviewsForm.value.reviewsFormOverlay) {
        document.removeEventListener('keydown', closeKeydownHandler);
        document.removeEventListener('click', closeFromDocument)
    }
}

const swiperRef = useTemplateRef('swiperRef');
const swiper = ref(null);

const toggleSwiper = () => {
    if (swiper.value) {
        swiper.value.destroy();
        swiper.value = null;
    }
    if (!swiperRef.value) return;
    swiper.value = new Swiper(swiperRef.value, {
        spaceBetween: 20,
        slidesPerView: 'auto',
        slidesPerGroup: 1,
        freeMode: true,
    });
}

onMounted(async () => {
    await nextTick();
    toggleSwiper();
})

watch(() => store.getters.getProductId, async () => {
    await nextTick();
    toggleSwiper();
})
</script>

<template>
    <section class="section-slider">
        <div class="reviews">
            <div class="reviews__title-block">
                <h2 class="reviews__title">Отзывы о товаре</h2>
                <!-- <a href="#" class="reviews__title-link">Показать все</a> -->
            </div>
            <div class="reviews__block">
                <div class="reviews__rating-mobile">
                    <span class="reviews__rating-mobile-num">{{ reviews.eval_info.avg }}</span>
                    <span class="reviews__rating-mobile-text">На основе {{ pluralText }}</span>
                    <button class="reviews__rating-mobile-btn" @click="openForm">Оставить отзыв</button>
                </div>
                <div class="reviews__container" ref="swiperRef">
                    <div class="swiper-wrapper">
                        <div class="reviews__rating swiper-slide">
                            <span class="reviews__rating-num">{{ reviews.eval_info.avg }}</span>
                            <span class="reviews__rating-text">На основе {{ pluralText }}</span>
                            <button class="reviews__rating-btn" @click="openForm">Оставить отзыв</button>
                        </div>
                        <div class="reviews__item swiper-slide" v-for="review in reviews.items" :key="review.id">
                            <div class="reviews__item-head">
                                <div class="reviews__item-head-author">
                                    <span class="reviews__item-head-author-name">{{ review.user_name }}</span>
                                    <span class="reviews__item-head-author-date">{{ review.date }} г.</span>
                                </div>
                                <div class="reviews__item-head-stars">
                                    <ReviewsStars :eval="review.eval"></ReviewsStars>
                                </div>
                            </div>
                            <p class="reviews__item-text">{{ review.review }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <ReviewsForm @close="closeForm" ref="reviewsForm"></ReviewsForm>
        </div>
    </section>
</template>