<script setup>
import { computed } from 'vue';
import { logOut } from '@/common/js/helpers';
import ProfileTabs from './ProfileTabs.vue';
import { useStore } from 'vuex';
import { Mask } from 'maska';
import { phoneMask } from '@/common/js/variables';

const store = useStore();
const personal = computed(() => store.getters['personal/getPersonal']);
const mask = new Mask({ mask: phoneMask })
const phone = computed(() => mask.masked(personal.value.phone))
</script>

<template>
    <div class="profile__info">
        <div class="profile__info-user">
            <div v-if="personal.photo" class="profile__info-user-avatar">
                <img :src="personal.photo">
            </div>
            <div class="profile__info-user-contacts">
                <span class="profile__info-user-name">{{ personal.name }}</span>
                <span class="profile__info-user-phone">{{ phone }}</span>
            </div>
        </div>
        <div class="profile__info-logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                <path d="M13.333 14.6668L17.4997 10.5002L13.333 6.3335" stroke="#6B7280" stroke-width="1.5"
                    stroke-linecap="square" />
                <path
                    d="M17.5 11.25C17.9142 11.25 18.25 10.9142 18.25 10.5C18.25 10.0858 17.9142 9.75 17.5 9.75V11.25ZM7.5 9.75H6.75V11.25H7.5V9.75ZM17.5 9.75H7.5V11.25H17.5V9.75Z"
                    fill="#6B7280" />
                <path
                    d="M7.5 18H4.16667C3.72464 18 3.30072 17.8244 2.98816 17.5118C2.67559 17.1993 2.5 16.7754 2.5 16.3333V4.66667C2.5 4.22464 2.67559 3.80072 2.98816 3.48816C3.30072 3.17559 3.72464 3 4.16667 3H7.5"
                    stroke="#6B7280" stroke-width="1.5" stroke-linecap="square" />
            </svg>
            <span @click="logOut">Выйти из аккаунта</span>
        </div>
        <ProfileTabs></ProfileTabs>
    </div>
</template>