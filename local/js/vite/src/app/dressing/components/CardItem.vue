<script setup>
import { useStore } from 'vuex';
import ProductCardBasket from '@/common/components/ProductCardBasket.vue';

const store = useStore();
const props = defineProps({
    item: Object
});
const itemDelete = () => {
    store.dispatch('toggleDressing', props.item.offerId)
}
</script>

<template>
    <div class="dressing-block__item">
        <ProductCardBasket :item="props.item" @delete="itemDelete" />
    </div>
</template>