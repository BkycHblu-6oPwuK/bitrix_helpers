<script setup>
import CatalogFilter from './components/CatalogFilter/CatalogFilter.vue';
import CatalogSection from './components/CatalogSection/CatalogSection.vue';
import CatalogSectionList from "./components/CatalogSectionList/CatalogSectionList.vue";
</script>

<template>
    <CatalogSectionList></CatalogSectionList>
    <CatalogFilter></CatalogFilter>
    <CatalogSection></CatalogSection>
</template>