<script setup>
import { ref, useAttrs, watch } from 'vue';

defineOptions({ inheritAttrs: false });
const props = defineProps({
    placeholder: String,
});
const emits = defineEmits(['focus', 'blur']);
const value = defineModel();
const attrs = useAttrs();
const hidePlaceholder = ref(false);

const focusHandler = () => {
    hidePlaceholder.value = !!value.value
    emits('focus')
}
const blurHandler = () => {
    hidePlaceholder.value = !!value.value
    emits('blur')
}

watch(value, (val) => {
    hidePlaceholder.value = !!val;
});
</script>


<template>
    <div class="input-wrapper">
        <input v-bind="attrs" v-model="value" class="checkout-form-input" @focus="focusHandler"
            @blur="blurHandler" />
        <span v-if="!hidePlaceholder && !value" class="custom-placeholder">
            {{ placeholder }}
            <span class="required-star">*</span>
        </span>
    </div>
</template>