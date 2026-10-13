<!-- Хлебные крошки, для корректного SSR рендеринга нельзя вызывать в layout -->
<script setup lang="ts">
import type { BreadcrumbItem } from '~/types/breadcrumbs';
import type { SectionDTO } from '~/types/iblock';

const props = defineProps<{
    sections?: SectionDTO[],
    element?: BreadcrumbItem,
    items?: BreadcrumbItem[]
}>();
const items = computed<BreadcrumbItem[]>(() => {
    const result: BreadcrumbItem[] = [{ title: 'Главная', to: '/' }]

    if (props.items) {
        props.items.forEach(item => result.push(item))
        return result
    }

    if (props.sections) {
        props.sections.forEach(section => {
            result.push({ title: section.name, to: section.url })
        })
    }

    if (props.element) result.push(props.element)

    if (result.length === 1) {
        return []
    }

    return result
});
</script>

<template>
    <nav v-if="items.length" aria-label="Breadcrumb">
        <ol class="breadcrumbs">
            <li v-for="(item, i) in items" :key="i">
                <NuxtLink v-if="item.to && i !== items.length - 1" :to="item.to" :prefetch="false">
                    {{ item.title }}
                </NuxtLink>
                <span v-else>{{ item.title }}</span>
            </li>
        </ol>
    </nav>
</template>
