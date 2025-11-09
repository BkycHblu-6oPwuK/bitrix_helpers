<script setup lang="ts">
import FormView from '~/components/webForm/FormView.vue';
import type { FormContent } from '~/types/content'

const props = defineProps<{
  data: FormContent['result']
}>()
</script>

<template>
  <FormView :form="data"></FormView>
</template>
