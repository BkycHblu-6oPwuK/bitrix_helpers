<script setup lang="ts">
const { menu } = useMenu()
</script>

<template>
    <nav class="w-full bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700
           text-gray-800 dark:text-gray-200 transition-colors duration-300">
        <div class="container mx-auto px-4 flex items-center justify-start gap-6 py-2">
            <NuxtLink v-for="item in menu.top" :key="item.id" :to="item.link || '#'" :prefetch="false"
                class="font-medium hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                {{ item.name }}
            </NuxtLink>
        </div>
    </nav>
</template>
