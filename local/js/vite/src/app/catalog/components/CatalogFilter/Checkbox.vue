<script setup>
const props = defineProps({
    itemValue: {
        type: Object,
        required: true,
    },
});
const emits = defineEmits(['changeCallback']);
const value = defineModel('value');
</script>

<template>
    <input :id="props.itemValue.CONTROL_ID" type="checkbox" :value="props.itemValue.HTML_VALUE" :checked="props.itemValue.CHECKED"
        class="checkbox-input" v-model="value" @change="emits('changeCallback')">
    <label :for="props.itemValue.CONTROL_ID">
        {{ props.itemValue.VALUE }}
    </label>
</template>