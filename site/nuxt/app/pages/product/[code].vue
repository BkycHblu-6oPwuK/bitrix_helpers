<script setup lang="ts">

import Dump from '~/components/dev/Dump.vue';
const route = useRoute();
console.log(route.params.code)

const { data } = await useApi(`product/${route.params.code}`)
// if (data.value?.data?.seo) {
//     useSeoPage(data.value.data.seo)
// }

</script>

<template>
    <Dump :data="data" :label="'Product Page'" />
</template>