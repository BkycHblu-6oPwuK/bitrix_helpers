<!-- Переключатель торговых предложений (размер, цвет, объём…) -->
<script setup lang="ts">
const { optionGroups, selectOfferValue } = useCatalogProduct()
</script>

<template>
    <div v-if="optionGroups.length" class="product-offers space-y-4">
        <div v-for="group in optionGroups" :key="group.code" class="offer-property">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                {{ group.name }}
            </h3>

            <div class="flex flex-wrap gap-2" role="group" :aria-label="group.name">
                <!-- Недоступные варианты не прячем (иначе пропадают и выбранные) — приглушаем -->
                <button v-for="opt in group.options" :key="opt.value" type="button" :aria-pressed="opt.selected" :class="[
                    'px-4 py-2 border rounded-lg transition-colors text-sm font-medium',
                    opt.selected
                        ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-300'
                        : 'border-gray-300 dark:border-gray-600 hover:border-gray-400',
                    !opt.available && !opt.selected && 'opacity-50'
                ]" @click="selectOfferValue(group.code, opt.value)">
                    <span v-if="opt.pictureSrc" class="flex items-center gap-2">
                        <img :src="opt.pictureSrc" :alt="opt.name" class="w-6 h-6 rounded object-cover" />
                        <span>{{ opt.value }}</span>
                    </span>
                    <span v-else>{{ opt.value }}</span>
                </button>
            </div>
        </div>
    </div>
</template>