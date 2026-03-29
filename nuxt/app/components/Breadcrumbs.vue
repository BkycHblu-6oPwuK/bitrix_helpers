<!-- Хлебные крошки, для корректного SSR рендеринга нельзя вызывать в layout -->
<script setup lang="ts">
import type { BreadcrumbItem } from '~/types/breadcrumbs';
import type { SectionDTO } from '~/types/iblock';

const props = defineProps<{
    sections: SectionDTO[],
    element?: BreadcrumbItem,
    items?: BreadcrumbItem[]
}>();
const items = computed<BreadcrumbItem[]>(() => {
    if (props.items) {
        return props.items;
    }

    const crumbs: BreadcrumbItem[] = [
        { title: 'Главная', to: '/' }
    ];

    props.sections.forEach(section => {
        crumbs.push({ title: section.name, to: section.url });
    });

    if (props.element) {
        crumbs.push(props.element);
    }

    return crumbs;
});
</script>

<template>
    <nav v-if="items.length" aria-label="Breadcrumb">
        <ol class="breadcrumbs">
            <li v-for="(item, i) in items" :key="i">
                <NuxtLink v-if="item.to && i !== items.length - 1" :to="item.to">
                    {{ item.title }}
                </NuxtLink>
                <span v-else>{{ item.title }}</span>
            </li>
        </ol>
    </nav>
</template>
