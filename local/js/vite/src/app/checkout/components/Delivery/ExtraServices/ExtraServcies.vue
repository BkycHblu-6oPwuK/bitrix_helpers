<script setup>
import { computed } from 'vue';
import ServiceItem from './ServiceItem.vue';
import { useStore } from 'vuex';

const store = useStore();
const extraServices = computed(() => store.getters.getExtraServices)
</script>

<template>
    <div class="checkout-extra-service" v-for="service in extraServices" :key="service.id">
        <ServiceItem :item="service"></ServiceItem>
    </div>
</template>