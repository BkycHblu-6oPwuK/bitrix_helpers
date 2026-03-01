<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { vMaska } from "maska/vue"
import { phoneMask } from '@/common/js/variables';

const store = useStore();
const errors = computed(() => store.getters.getErrors);
const form = computed(() => store.getters.getForm);
const createOrder = () => store.dispatch('createOrder');
</script>

<template>
    <label for="dressing-name">Имя</label>
    <div class="dressing-block__result-name-input-block form-input-block">
        <input id="dressing-name" type="text" v-model="form.name" placeholder="Екатерина" class="dressing-block__result-name-input">
        <div class="dressing-block__result-name-input-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
            </svg>
        </div>
        <div class="dressing-block__result-name-input-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                    stroke-linecap="square" />
            </svg>
        </div>
    </div>
    <span class="dressing-block__name-err-msg error-message" v-if="errors.name">Введите Ваше имя</span>
    <label for="dressing-phone">Номер телефона</label>
    <div class="dressing-block__result-phone-input-block form-input-block">
        <input id="dressing-phone" type="text" v-model="form.phone" v-maska="phoneMask" placeholder="+ 7 950 567 89 45"
            class="dressing-block__result-phone-input">
        <div class="dressing-block__result-phone-input-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
            </svg>
        </div>
        <div class="dressing-block__result-phone-input-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                    stroke-linecap="square" />
            </svg>
        </div>
    </div>
    <span class="dressing-block__phone-err-msg error-message" v-if="errors.phone">Введите Ваш номер телефона</span>
    <a><button @click="createOrder" type="submit" class="dressing-block__submit-btn">Записаться на примерку</button></a>
</template>