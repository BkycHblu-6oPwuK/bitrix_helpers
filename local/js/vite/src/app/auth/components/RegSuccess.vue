<script setup>
import { useTemplateRef } from 'vue';

const emits = defineEmits(['close']);
const popupForm = useTemplateRef('popupForm');

defineExpose({
    popupForm
});
</script>

<template>
    <div class="reg-popup__form popup-form reg-popup__success hidden" ref="popupForm">
        <div class="reg-popup__success-info">
            <div class="reg-popup__success-info-icon">
                <svg width="140" height="140" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="86.5" fill="white" stroke="#111827" stroke-width="2" />
                    <path d="M80.5986 104.557L92.5778 116.537L122.526 86.5886" stroke="#0F1523" stroke-width="2"
                        stroke-linecap="square" />
                </svg>
            </div>
            <span class="reg-popup__success-info-message">Вы успешно зарегистрированы!</span>
            <span class="reg-popup__success-info-text">Теперь вы можете оформлять заказы и записываться на
                примерку.</span>
        </div>
        <div class="login-popup__form-close" @click="emits('close')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18.75 5.25L5.25 18.75" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M18.75 18.75L5.25 5.25" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>
</template>