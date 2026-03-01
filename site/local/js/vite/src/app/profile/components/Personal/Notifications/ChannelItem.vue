<script setup>
import { computed, useId } from 'vue';

const props = defineProps({
    channel: Object
})
const id = useId();
const emits = defineEmits(['onChannelUpdate'])
const isEnable = computed({
    get: () => props.channel.isEnable,
    set: (value) => {
        if(props.channel.isEnable !== value){
            emits('onChannelUpdate', { ...props.channel, isEnable: value })
        }
    }
})

</script>

<template>
    <div class="profile-personal__notification-item">
        <input :id="'channel-' + id" v-model="isEnable" type="checkbox" class="checkbox-input">
        <label :for="'channel-' + id">{{ channel.name }}</label>
    </div>
</template>