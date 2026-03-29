<script setup>
import { useId } from 'vue';

const id = useId();
const model = defineModel()
const props = defineProps({
    label: {
        type: String,
        default: ""
    }
});
</script>

<template>
    <input type="checkbox" :id="id" class="styled-checkbox" v-model="model" />
    <label v-if="label" :for="id">{{ label }}</label>
</template>

<style scoped>
.styled-checkbox {
    appearance: none;
    width: 24px;
    height: 24px;
    border: 1px solid #aaa;
    border-radius: 4px;
    position: relative;
    cursor: pointer;
}

.styled-checkbox:checked::after {
    content: "";
    position: absolute;
    left: 9px;
    top: 3px;
    width: 6px;
    height: 12px;
    border: solid #000;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

label {
    font-size: 14px;
}

@media (min-width: 768px) {
    label {
        font-size: 16px;
    }
}
</style>