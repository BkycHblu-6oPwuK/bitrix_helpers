<script setup>
import { computed, useSlots } from 'vue';
import storeAbout from '@/store/about';
const props = defineProps({
    productId: {
        type: Number,
        required: true
    },
    classes: {
        type: Object,
        default: {
            default: 'product-card__image-favourites-icon',
            active: 'product-card__icon-favourites_active'
        }
    }
})
const isFavourite = computed(() => storeAbout.getters['favourite/getFavourite'].includes(Number(props.productId)),);
const toggle = () => storeAbout.dispatch('favourite/toggleFavourite', Number(props.productId));
const slots = useSlots();
</script>

<template>
    <div @click.prevent="toggle" :class="{
        [props.classes.default]: true,
        [props.classes.active]: isFavourite,
    }">
        <slot v-if="slots.default"></slot>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M14 7.00019C11.9007 4.55366 8.39273 3.79757 5.76243 6.03785C3.13213 8.27813 2.76182 12.0238 4.82741 14.6734C6.54481 16.8763 11.7423 21.5225 13.4457 23.0263C13.6363 23.1945 13.7316 23.2787 13.8427 23.3117C13.9397 23.3406 14.0459 23.3406 14.1429 23.3117C14.254 23.2787 14.3493 23.1945 14.5399 23.0263C16.2434 21.5225 21.4408 16.8763 23.1582 14.6734C25.2238 12.0238 24.8987 8.25457 22.2232 6.03785C19.5477 3.82114 16.0993 4.55366 14 7.00019Z"
                stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" />
        </svg>
    </div>
</template>
