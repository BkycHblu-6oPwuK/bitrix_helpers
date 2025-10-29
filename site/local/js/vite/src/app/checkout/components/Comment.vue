<script setup>
import { computed } from 'vue';
import { useStore } from 'vuex';

const store = useStore();
const model = computed({
  get: () => store.getters.getComment,
  set: (val) => {
    store.commit('setComment', val);
  }
});
</script>


<template>
  <div class="input-wrapper">
    <textarea class="checkout-form-input" v-model="model" placeholder="Комментарий к доставке"></textarea>
  </div>
</template>

<style scoped>
.checkout-form-input {
  padding: 8px 10px;
  height: 120px;
  margin: 5px 0;
}

@media (min-width: 768px) {
  .checkout-form-input {
    padding: 10px 15px;
    height: 90px;
    margin: unset;
  }
}
</style>