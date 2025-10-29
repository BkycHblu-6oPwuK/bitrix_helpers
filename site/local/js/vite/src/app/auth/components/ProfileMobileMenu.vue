<script setup>
import { computed } from 'vue';
import store from '@/store/profile';
import { Mask } from 'maska';
import { phoneMask } from '@/common/js/variables';
store.dispatch('personal/initialize');
const personal = computed(() => store.getters['personal/getPersonal']);
const mask = new Mask({ mask: phoneMask })
const phone = computed(() => mask.masked(personal.value.phone))
</script>

<template>
    <div class="m-header__menu-profile">
        <div v-if="personal.photo" class="m-header__menu-profile-avatar">
            <img :src="personal.photo">
        </div>
        <div class="m-header__menu-profile-info">
            <span class="m-header__menu-profile-info-name">{{ personal.name }}</span>
            <span class="m-header__menu-profile-info-phone">{{ phone }}</span>
        </div>
    </div>
</template>