<!--
  Меню каталога с поповером
  Открывается при наведении на кнопку "Каталог"
  Клик по кнопке ведёт на страницу каталога
-->
<script setup lang="ts">
const { menu } = useMenu()

function toCatalog() {
    navigateTo('/catalog/')
}
</script>

<template>
    <UPopover :popper="{ placement: 'bottom-start', offset: 6 }" mode="hover">
        <template #default>
            <UButton color="warning" variant="solid" icon="i-heroicons-bars-3" trailing
                class="cursor-pointer !rounded-lg text-white font-semibold" @click="toCatalog">
                Каталог
            </UButton>
        </template>

        <template #content>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
               rounded-lg shadow-lg p-4 min-w-[420px]
               text-gray-800 dark:text-gray-200 transition-colors">
                <div v-if="menu.catalog?.length" class="grid grid-cols-2 gap-6">
                    <div v-for="item in menu.catalog" :key="item.id" class="space-y-2">
                        <NuxtLink :to="item.link || '#'" :prefetch="false" class="block font-medium text-gray-800 dark:text-gray-100
                     hover:text-primary-600 dark:hover:text-primary-400
                     transition-colors">
                            {{ item.name }}
                        </NuxtLink>

                        <ul v-if="item.children?.length" class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <li v-for="child in item.children" :key="child.id">
                                <NuxtLink :to="child.link || '#'" :prefetch="false" class="block pl-2 border-l border-gray-100 dark:border-gray-700
                         hover:border-primary-400 dark:hover:border-primary-500
                         hover:text-primary-600 dark:hover:text-primary-400
                         transition-colors">
                                    {{ child.name }}
                                </NuxtLink>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </template>
    </UPopover>
</template>
