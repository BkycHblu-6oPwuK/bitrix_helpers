<script setup>
import { computed, inject } from 'vue';
import { useStore } from 'vuex';
import RadioCard from './RadioCard.vue';

const store = useStore();
const isMobile = inject('isMobile');
const personType = computed(() => store.getters.getPersonType);
const selectedCode = computed({
    get: () => {
        return personType.value.selected;
    },
    set: (val) => {
        store.commit('setSelectedPersonType', val);
        store.dispatch('refresh');
    }
});
</script>

<template>
    <div class="checkout-radio-group person-types__radio-group">
        <template v-for="(person, code) in personType.fields" :key="code">
            <RadioCard :value="code" v-model="selectedCode"><span>{{ isMobile ? person.name.mobile : person.name.default }}</span></RadioCard>
        </template>
    </div>
</template>