<script setup>
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { formattedDate } from '../helpers';

const model = defineModel();
</script>

<template>
    <label class="checkout-radio-card selected">
        <VueDatePicker v-model="model" locale="ru" :teleport="true" cancel-text="Закрыть" select-text="Выбрать"
            :enable-time-picker="false" :format="date => formattedDate(date)"
            :min-date="new Date(new Date().setDate(new Date().getDate() + 1))">
            <template #trigger>
                <slot />
            </template>
        </VueDatePicker>
    </label>
</template>

<style scoped>
.dp__main {
    font-family: inherit;
}
</style>
