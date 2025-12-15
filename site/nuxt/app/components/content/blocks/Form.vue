<!--
  Блок с web-формой Bitrix
  Обертка для FormView, который рендерит форму с полями
  Используется для отображения форм на страницах сайта
-->
<script setup lang="ts">
import FormView from '~/components/web-form/FormView.vue';
import type { FormContent } from '~/types/iblock/content'

// Пропсы: данные формы с сервера
const props = defineProps<{
  data: FormContent['result']
}>()
</script>

<template>
  <FormView :form="data"></FormView>
</template>
