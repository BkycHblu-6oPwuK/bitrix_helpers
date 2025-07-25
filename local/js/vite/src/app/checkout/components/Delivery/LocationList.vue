<script setup>
import { watch, nextTick, useTemplateRef } from 'vue';

const props = defineProps({
    items: Array,
    show: Boolean,
    useScroll: {
        type: Boolean,
        default: false
    },
});
const emits = defineEmits(['select', 'showMore']);

if (props.useScroll) {
    const containerRef = useTemplateRef('containerRef');
    const handleScroll = () => {
        const el = containerRef.value;
        if (!el) return;
        if (el.scrollHeight - el.scrollTop - el.clientHeight < 50) {
            emits('showMore');
        }
    };

    watch(() => props.show, async (show) => {
        await nextTick();
        const el = containerRef.value;
        if (!el) return;
        el[show ? 'addEventListener' : 'removeEventListener']('scroll', handleScroll);
    });
}
</script>

<template>
    <div v-if="show && items.length" ref="containerRef" class="location-list">
        <div v-for="(item, key) in items" :key="key" @click="emits('select', item)" class="location-item">
            {{ item.display + (item.pathFormatted ? ', ' + item.pathFormatted : '') }}
        </div>
    </div>
</template>

<style scoped>
.location-list {
    max-height: 300px;
    position: absolute;
    box-sizing: border-box;
    width: 100%;
    z-index: 99;
    overflow-y: auto;
    border: 1px solid #ccc;
    border-radius: 0 0 4px 4px;
    padding: 10px;
    background: #fff;
    display: flex;
    flex-direction: column;
    max-width: 800px;
}

.location-item {
    padding: 10px;
    cursor: pointer;
    transition: background 0.1s ease-in-out;
}

.location-item:hover {
    background: #f0f0f0;
}
</style>
