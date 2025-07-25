<script setup>
import { computed, ref, watch } from 'vue'
import RadioCard from './RadioCard.vue'
import Subtitle from './Subtitle.vue'
import { getImagePublicPath } from '@/common/js/helpers'
import { useStore } from 'vuex'
import DateCard from './DateCard.vue'
import { formattedDate } from '../helpers'

const store = useStore();
const dates = ref([]);
const selectedCode = computed({
    get: () => store.getters.getCompletionDate,
    set: (val) => {
        val = formattedDate(val);
        store.commit('setDeliveryCompletionDate', val);
    }
});
const isDatePickerDate = computed(() => {
    if (!selectedCode.value) return false;
    for (let date of dates.value) {
        if (date === selectedCode.value) {
            return false;
        }
    }
    return true;
});
const daysOfWeek = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
const months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

for (let i = 1; i <= 3; i++) {
    const date = new Date();
    date.setDate(date.getDate() + i);
    const day = daysOfWeek[date.getDay()];
    const formattedDate = `${day}, ${date.getDate()} ${months[date.getMonth()]}`;
    dates.value.push(formattedDate);
}
const selectedTransportDelivery = computed(() => store.getters.selectedTransportDelivery);

watch(selectedTransportDelivery, (newVal) => {
    if (!newVal) {
        selectedCode.value = '';
    }
});
</script>

<template>
    <div v-if="!selectedTransportDelivery">
        <Subtitle>3. Когда вам будет удобнее забрать заказ?</Subtitle>
        <div class="checkout-radio-group take-date__radio-group">
            <template v-for="(item, index) in dates" :key="index">
                <RadioCard :name="'take-date'" :value="item" v-model="selectedCode">
                    <span>{{ item }}</span>
                </RadioCard>
            </template>
            <DateCard v-model="selectedCode">
                <div class="take-date__calendar">
                    <div><img :src="getImagePublicPath('/checkout/calendar-dates.svg')"></div>
                    <span>{{ isDatePickerDate ? selectedCode : 'Выбрать дату' }}</span>
                </div>
            </DateCard>
        </div>
    </div>
</template>

<style scoped>
.take-date__calendar {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    font-size: 14px;
}

@media (min-width: 768px) {
    .take-date__calendar {
        flex-direction: row;
        gap: 10px;
        font-size: 16px;
    }
}
</style>
