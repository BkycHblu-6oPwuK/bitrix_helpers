<script setup>
const props = defineProps({
    eval: Number,
    width: {
        type: Number,
        default: 24
    },
    height: {
        type: Number,
        default: 24
    }
})
</script>

<template>
    <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24"
        fill="none" :class="{
            'product-card-details__head-star_active': i <= Math.floor(props.eval)
        }">
        <path
            d="M12 0L14.6942 8.2918H23.4127L16.3593 13.4164L19.0534 21.7082L12 16.5836L4.94658 21.7082L7.64074 13.4164L0.587322 8.2918H9.30583L12 0Z"
            fill="#D1D5DB" />
    </svg>
</template>