<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';
import CardItem from './components/CardItem.vue';
import Total from './components/Total.vue';
import Form from './components/Form.vue';

const store = useStore();
const items = computed(() => store.getters.getItems);
</script>

<template>
    <div class="dressing-block">
        <h2 class="dressing-block__title">Примерочная</h2>
        <div class="dressing-block__container">
            <div class="dressing-block__items">
                <CardItem v-for="item in items" :item="item" :key="item.id"></CardItem>
            </div>
            <div class="dressing-block__result popup-form">
                <Total />
                <Form />
            </div>
        </div>
    </div>
</template>