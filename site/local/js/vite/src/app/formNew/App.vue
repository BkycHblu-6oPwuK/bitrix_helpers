<script setup>
import { useStore } from "vuex";
import { computed } from "vue";

const store = useStore();
const title = computed(() => store.state.title);
const description = computed(() => store.state.description);
const fields = computed(() => store.state.fields);
const form = computed({
  get: () => store.state.form,
  set: (val) => {
    // не нужен set — v-model работает по ключам
  },
});
const formIdsMap = computed(() => store.state.formIdsMap);

function handleSubmit() {
  store.dispatch("sendForm");
}
</script>

<template>
  <div class="form-wrapper">
    <h2>{{ title }}</h2>
    <p>{{ description }}</p>

    <form @submit.prevent="handleSubmit">
      <div v-for="field in fields" :key="field.id" class="form-group">
        <label :for="formIdsMap[field.name]">{{ field.label }}</label>

        <input
          v-if="field.type !== 'textarea'"
          :type="field.type"
          :id="formIdsMap[field.name]"
          v-model="form[field.name]"
          v-bind="field.attributes"
          :required="field.required"
        />

        <textarea
          v-else
          :id="formIdsMap[field.name]"
          v-model="form[field.name]"
          v-bind="field.attributes"
          :required="field.required"
        ></textarea>

        <span v-if="field.error" class="error">{{ field.error }}</span>
      </div>

      <button type="submit">Отправить</button>
    </form>
  </div>
</template>

<style scoped>
.form-wrapper {
  max-width: 500px;
  margin: auto;
}
.form-group {
  margin-bottom: 1rem;
}
input,
textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid black;
  box-sizing: border-box;
}
.error {
  color: red;
  font-size: 0.9rem;
}
</style>
