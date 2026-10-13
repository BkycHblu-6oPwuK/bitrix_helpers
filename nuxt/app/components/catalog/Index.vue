<script setup lang="ts">
import type { CatalogDTO } from '~/types/iblock/catalog.ts'

const props = withDefaults(defineProps<{
    catalog: CatalogDTO
    apiUrl: string
    syncUrl?: boolean
}>(), { syncUrl: false })

useSectionInit(props.catalog, { apiUrl: props.apiUrl, syncUrl: props.syncUrl })

const { isLoading } = useSection()
const { sectionList, hasSections } = useSectionList<CatalogDTO['sectionList']>()
</script>

<template>
    <div class="catalog-page">
        <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
            <div class="text-lg">Загрузка...</div>
        </div>

        <div v-else class="grid gap-8">
            <aside class="lg:col-span-1">
                <div class="sticky top-4">
                    <CatalogFilter />
                </div>
            </aside>

            <main class="lg:col-span-3">
                <CatalogSections v-if="hasSections" :section-list="sectionList" class="mb-8" />
                <CatalogSection />
            </main>
        </div>
    </div>
</template>