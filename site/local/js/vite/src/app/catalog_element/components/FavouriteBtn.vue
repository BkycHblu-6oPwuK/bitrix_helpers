<script setup>
import { computed } from 'vue';
import storeAbout from '@/store/about';
import { useStore } from 'vuex';
const store = useStore();
const productId = computed(() => store.getters.getProductId);
const isFavourite = computed(() => storeAbout.getters['favourite/getFavourite'].includes(productId.value),);
const toggle = () => storeAbout.dispatch('favourite/toggleFavourite', productId.value);
</script>
<template>
    <button @click="toggle" class="product-card-details__btn-favourite" :class="{ 
        'product-card-details__btn-favourite_active': isFavourite
    }">
        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M12.25 6.50019C10.4506 4.40317 7.44377 3.7551 5.18923 5.67534C2.93468 7.59558 2.61727 10.8061 4.38778 13.0772C5.85984 14.9654 10.3148 18.9479 11.7749 20.2369C11.9382 20.3811 12.0199 20.4532 12.1152 20.4815C12.1983 20.5062 12.2893 20.5062 12.3725 20.4815C12.4678 20.4532 12.5494 20.3811 12.7128 20.2369C14.1729 18.9479 18.6278 14.9654 20.0999 13.0772C21.8704 10.8061 21.5917 7.57538 19.2984 5.67534C17.0051 3.7753 14.0494 4.40317 12.25 6.50019Z"
                stroke="#28313D" stroke-width="1.5" stroke-linecap="round" />
        </svg>
        <span>В избранное</span>
    </button>
</template>