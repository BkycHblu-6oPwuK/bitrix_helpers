<script setup>

const route = useRoute();

console.log(route.params.group, route.params.id)
</script>

<template>
  <p>{{ $route.params.group }} - {{ $route.params.id }}</p>
</template>