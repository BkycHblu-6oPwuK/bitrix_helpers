<script setup>

const route = useRoute();
const data = await useApi(`user/me`)

console.log(route.params.group, route.params.id)
</script>

<template>
    <p>{{ $route.params.group }} - {{ $route.params.id }}, {{ data }}</p>
</template>