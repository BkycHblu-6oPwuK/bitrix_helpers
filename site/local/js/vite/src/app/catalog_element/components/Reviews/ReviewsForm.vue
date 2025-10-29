<script setup>
import { computed, reactive, useTemplateRef } from 'vue';
import { useStore } from 'vuex';
const emits = defineEmits(['close']);
const reviewsFormOverlay = useTemplateRef('reviewsFormOverlay');
defineExpose({ reviewsFormOverlay })

const store = useStore();
const reviews = computed(() => store.getters.getReviews);
const form = reactive({
    user_name: '',
    review: '',
    eval: 0
})
const validate = () => {
    let error = false;
    if (!reviews.value.user_authorize && !form.user_name) {
        error = true;
    }
    if (form.eval < 1) {
        error = true;
    }
    return error;
}
const make = () => {
    if(validate()) return;
    store.dispatch('addReview', form);
}
</script>
<template>
    <teleport to='body'>
        <div class="reviews__form-overlay" ref="reviewsFormOverlay">
            <form class="reviews__form drag-menu" @submit.prevent="make" v-if="!reviews.exits_review">
                <span class="reviews__form-title">Ваш отзыв о товаре</span>
                <div class="reviews__form-mark-block">
                    <span class="reviews__form-mark-question">Как вы оцениваете продукцию?</span>
                    <div class="reviews__form-marks">
                        <div class="reviews__form-mark" v-for="number in 5" :class="{
                            'reviews__form-mark_active' : number === form.eval
                        }" @click="form.eval = number">{{ number }}</div>
                    </div>
                    <div class="reviews__form-input-block" v-if="!reviews.user_authorize">
                        <label for="review-name">Ваше имя</label>
                        <div class="reviews__form-input">
                            <input id="review-name" v-model="form.user_name" type="text" placeholder="Ваше имя">
                            <div class="reviews__form-input-error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                    fill="none">
                                    <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                                    <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                                </svg>
                            </div>
                            <div class="reviews__form-input-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                    fill="none">
                                    <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653"
                                        stroke-width="2" stroke-linecap="square" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="reviews__form-input-text">
                        <label for="reviews__form-textarea">Ваш отзыв</label>
                        <textarea name="" id="reviews__form-textarea" cols="30" rows="10"
                            placeholder="Ваш отзыв" v-model="form.review"></textarea>
                    </div>
                    <div class="reviews__form-submit-btn-container">
                        <button type="submit" class="reviews__form-submit-btn">Оставить отзыв</button>
                    </div>
                    <span class="reviews__form-policy">Нажимая на кнопку «Оставить отзыв», я согласен на обработку
                        <a href="#">персональных данных</a></span>
                </div>
                <div class="reviews__form-close-btn" @click="emits('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18.75 5.25L5.25 18.75" stroke="#D1D5DB" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M18.75 18.75L5.25 5.25" stroke="#D1D5DB" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="reviews__form-mobile-grab"></div>
            </form>

            <div v-else class="reg-popup__form popup-form reviews__form-success">
                <div class="reg-popup__success-info">
                    <div class="reg-popup__success-info-icon">
                        <svg width="140" height="140" viewBox="0 0 200 200" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <circle cx="100" cy="100" r="86.5" fill="white" stroke="#111827" stroke-width="2" />
                            <path d="M80.5986 104.557L92.5778 116.537L122.526 86.5886" stroke="#0F1523" stroke-width="2"
                                stroke-linecap="square" />
                        </svg>
                    </div>
                    <span class="reg-popup__success-info-message">Отзыв принят!</span>
                    <span class="reg-popup__success-info-text">Спасибо за ваш отзыв. <br>Ваше мнение очень важно для
                        нас!</span>
                </div>
                <div class="login-popup__form-close" @click="emits('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18.75 5.25L5.25 18.75" stroke="#D1D5DB" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M18.75 18.75L5.25 5.25" stroke="#D1D5DB" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
        </div>
    </teleport>
</template>