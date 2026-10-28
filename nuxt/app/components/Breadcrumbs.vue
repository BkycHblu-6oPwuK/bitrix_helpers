<!-- Хлебные крошки, для корректного SSR рендеринга нельзя вызывать в layout -->
<script setup lang="ts">
const crumbs = usePageCrumbs()
</script>

<template>
    <nav v-if="crumbs.length" aria-label="Breadcrumb">
        <ol class="breadcrumbs">
            <li v-for="(item, i) in crumbs" :key="i">
                <NuxtLink v-if="item.to && i !== crumbs.length - 1" :to="item.to" :prefetch="false">
                    {{ item.title }}
                </NuxtLink>
                <span v-else>{{ item.title }}</span>
            </li>
        </ol>
    </nav>
</template>
