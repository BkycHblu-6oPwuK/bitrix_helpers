<script setup lang="ts">
const props = defineProps({
    productId: {
        type: Number,
        required: true,
    },

    absolute: {
        type: Boolean,
        default: false,
    },
});

const favourite = useFavouriteStore();
const isActive = computed(() => favourite.isFavourite(props.productId));

function toggle() {
    favourite.toggle(props.productId);
}
</script>

<template>
    <button @click.stop.prevent="toggle" :class="[
        'flex items-center cursor-pointer justify-center rounded-full w-9 h-9 transition border bg-white',
        'hover:bg-gray-100',
        isActive ? 'border-red-500' : 'border-gray-300',
        absolute ? 'absolute top-2 right-2 z-10' : ''
    ]" aria-label="Добавить в избранное">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" :class="isActive ? 'stroke-red-500' : 'stroke-gray-700'"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path :class="isActive ? 'fill-red-500' : 'fill-transparent'" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 
           5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78
           1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
        </svg>
    </button>
</template>
