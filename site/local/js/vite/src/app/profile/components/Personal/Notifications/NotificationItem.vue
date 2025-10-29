<script setup>
import ChannelItem from './ChannelItem.vue';

const props = defineProps({
    notification: Object
})
const emits = defineEmits(['onNotificationUpdate'])
const onChannelUpdate = (channel) => emits('onNotificationUpdate', props.notification, channel)
</script>

<template>
    <div class="profile-personal__notification-column">
        <span>{{ notification.name }}</span>
        <template v-for="(channel, index) in notification.channels">
            <ChannelItem :channel="channel" @onChannelUpdate="onChannelUpdate"></ChannelItem>
        </template>
    </div>
</template>