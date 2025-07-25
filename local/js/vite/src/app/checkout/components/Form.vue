<script setup>
import { computed, watch } from 'vue';
import { useStore } from 'vuex';
import { vMaska } from "maska/vue";
import { phoneMask } from '@/common/js/variables';
import Input from './Input.vue';
import Subtitle from './Subtitle.vue';
import { showErrorNotification } from '@/app/notify';
import Checkbox from './Checkbox.vue';

const store = useStore();
const form = computed(() => store.getters.getForm);
const errors = computed(() => store.getters.getErrors);
const isLegal = computed(() => store.getters.isLegal);

watch(errors, (newValue) => {
    if(Object.entries(newValue).length > 0) {
        showErrorNotification("Не все обязательные поля заполнены")
    }
})
</script>

<template>
    <div class="checkout-info__contacts">
        <Subtitle>1. Укажите данные получателя заказа</Subtitle>
        <form class="checkout-form">
            <div class="checkout-form-block">
                <div class="checkout-form-row">
                    <Input :placeholder="'Имя'" v-model="form.fio" />
                    <Input type="email" :placeholder="'Email'" v-model="form.email" />
                </div>

                <div class="checkout-form-row">
                    <Input v-maska :data-maska="phoneMask" :placeholder="'Номер телефона'" v-model="form.phone" />
                </div>
            </div>
            <div class="checkout-form-block" v-if="isLegal">
                <div class="checkout-form-row">
                    <Input :placeholder="'ИНН'" v-model="form.legalInn"/>
                </div>

                <div class="checkout-form-row">
                    <Input :placeholder="'Название'" v-model="form.legalName"/>
                </div>

                <div class="checkout-form-row">
                    <Input :placeholder="'Юридический адрес'" v-model="form.legalAddress"/>
                </div>

                <div class="checkout-form-row form-checkbox-row">
                    <Checkbox v-model="form.legalAddressCheck" :label="'Юридический адрес совпадает с фактическим'" />
                </div>

                <div class="checkout-form-row" v-if="!form.legalAddressCheck">
                    <Input :placeholder="'Фактический адрес'" v-model="form.legalActualAddress" />
                </div>
            </div>
        </form>
    </div>
</template>