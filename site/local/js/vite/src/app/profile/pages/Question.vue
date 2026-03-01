<script setup>
import { useStore } from 'vuex';
import QuestionItem from '../components/Questions/QuestionItem.vue';
import { computed } from 'vue';

const store = useStore();
store.dispatch('question/initialize');

const questions = computed(() => store.getters['question/getQuestions']);
</script>

<template>
    <div class="profile-qa">
        <h3 class="profile__subtitle">Вопросы и ответы</h3>
        <div class="profile-qa__container">
            <QuestionItem v-for="(question, key) in questions" :key="key" :item="question"></QuestionItem>
        </div>
        <!-- <div class="profile-qa-help">
            <span>Не нашли нужного ответа?</span>
            <a href="#">Помощь тех. поддержки</a>
        </div> -->
    </div>
</template>