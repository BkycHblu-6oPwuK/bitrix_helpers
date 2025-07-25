<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: String,
    value: [String, Number],
    checked: Boolean,
})
const model = defineModel()
const isControlledExternally = computed(() => model.value === undefined && props.checked !== undefined)
const isSelected = computed(() => isControlledExternally.value ? props.checked : model.value === props.value)

const select = () => {
    if (!isControlledExternally.value && model) {
        model.value = props.value
    }
}
</script>

<template>
    <label class="checkout-radio-card" :class="{ selected: isSelected }" @click="select">
        <input type="radio" :name="name" :value="value" :checked="isSelected" @change="select" />
        <slot />
    </label>
</template>
