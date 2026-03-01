<script setup>
import ProfileInfo from './components/ProfileInfo.vue';
import { useStore } from 'vuex';

const store = useStore();
store.dispatch('personal/initialize');

</script>

<template>
    <div class="profile">
        <ProfileInfo></ProfileInfo>
        <RouterView />
    </div>
</template>