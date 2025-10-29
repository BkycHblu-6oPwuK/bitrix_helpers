<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex'
import NotificationItem from './NotificationItem.vue';
const store = useStore();
const notifications = computed(() => store.getters['personal/getPersonal'].notifications);
const onNotificationUpdate = (notification, channel) => store.dispatch('personal/addPreferenceNotification', {notification, channel})

</script>

<template>
    <div class="profile-personal__notification" v-if="notifications && notifications.length">
        <h3 class="profile__subtitle">Настройка уведомлений</h3>
        <div class="profile-personal__notification-block">
            <template v-for="notification in notifications" :key="notification.type">
                <NotificationItem :notification="notification" @onNotificationUpdate="onNotificationUpdate"></NotificationItem>
            </template>
        </div>
    </div>
</template>