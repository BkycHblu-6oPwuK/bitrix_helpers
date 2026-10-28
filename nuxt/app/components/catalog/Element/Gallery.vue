<!-- Галерея: главное изображение и миниатюры -->
<script setup lang="ts">
const { item, images } = useCatalogProduct()

const activeIndex = ref(0)

// Другой товар / другой набор фото — возвращаемся к первому
watch(images, () => { activeIndex.value = 0 })
</script>

<template>
    <div class="product-gallery">
        <div class="main-image aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg mb-4 overflow-hidden">
            <img v-if="images?.[activeIndex]" :src="images[activeIndex]" :alt="item.name"
                class="w-full h-full object-contain" fetchpriority="high" />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div v-if="images && images.length > 1" class="thumbnails grid grid-cols-4 gap-2">
            <button v-for="(image, index) in images" :key="image" type="button" :aria-label="`Фото ${index + 1}`"
                :aria-current="activeIndex === index" :class="[
                    'aspect-square rounded-md overflow-hidden border-2 transition-colors',
                    activeIndex === index
                        ? 'border-primary-600'
                        : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'
                ]" @click="activeIndex = index">
                <img :src="image" :alt="`${item.name} - фото ${index + 1}`" class="w-full h-full object-cover"
                    loading="lazy" />
            </button>
        </div>
    </div>
</template>