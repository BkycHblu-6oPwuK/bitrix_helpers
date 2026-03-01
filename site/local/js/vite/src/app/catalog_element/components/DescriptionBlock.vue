<script setup>
import { useStore } from 'vuex';
import { computed, ref, useTemplateRef } from 'vue';
import storeAbout from '@/store/about';

const store = useStore();
const product = computed(() => store.getters.getProduct);
const properties = computed(() => store.getters.getProperties);
const isMobile = computed(() => storeAbout.getters.isMobile);
const svg = useTemplateRef('svg');
const descrDetailBlock = useTemplateRef('descrDetailBlock');
const descrSpecContainer = useTemplateRef('descrSpecContainer');
const descriptionIsOpen = ref(false);
const openDescription = () => {
    if(!isMobile) return;
    if (descriptionIsOpen.value) {
        descrDetailBlock.value.style.height = `0`;
        descrSpecContainer.value.style.height = '0';
        descrSpecContainer.value.style.marginTop = '0';
        descrDetailBlock.value.style.marginTop = '0';
        svg.value.setAttribute('d', 'M7.99984 3.16699V13.8337M13.3332 8.50033L2.6665 8.50032');
    } else {
        descrDetailBlock.value.style.height = `${descrDetailBlock.value.scrollHeight}px`;
        descrSpecContainer.value.style.height = `${descrSpecContainer.value.scrollHeight}px`;
        descrSpecContainer.value.style.marginTop = '20px';
        descrDetailBlock.value.style.marginTop = '10px';
        svg.value.setAttribute('d', 'M3.3335 8.5H12.6668');
    }
    descriptionIsOpen.value = !descriptionIsOpen.value;
}
</script>

<template>
    <div class="product-card-details__description-container" v-if="product.detailText.length > 0">
        <div class="product-card-details__description-title" @click="openDescription" :class="{
            'opened': descriptionIsOpen
        }">
            <span>Описание</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
                <path ref="svg" d="M7.99984 3.16699V13.8337M13.3332 8.50033L2.6665 8.50032" stroke="#0F1523"
                    stroke-width="1.5" stroke-linecap="square" />
            </svg>
        </div>
        <p ref="descrDetailBlock" v-html="product.detailText"></p>
    </div>
    <div v-if="properties.length > 0" ref="descrSpecContainer" class="product-card-details__specs-container">
        <span class="product-card-details__specs-title">Характеристики</span>
        <div class="product-card-details__specs-block">
            <div class="product-card-details__specs-item" v-for="prop in properties" :key="prop.code">
                <span class="product-card-details__specs-item-title">{{ prop.name }}</span>
                <span class="product-card-details__specs-item-value">{{ prop.value }}</span>
            </div>
        </div>
    </div>
</template>