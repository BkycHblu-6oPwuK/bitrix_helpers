<script setup>
import { closePreloader } from '../../preloader';
import Form from '../components/Personal/Form.vue';
import Notifications from '../components/Personal/Notifications/Notifications.vue';
closePreloader();
</script>

<template>
    <div class="profile-personal">
        <Form></Form>
        <!-- <Delivery></Delivery> -->
        <Notifications></Notifications>
    </div>
</template>