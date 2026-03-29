<script setup>
import { ref, Transition } from "vue";

const isAccept = ref(
  document.cookie.split("; ").some((row) => row.startsWith("cookieConsent="))
);
const accept = () => {
  document.cookie = "cookieConsent=true; path=/; max-age=" + 60 * 60 * 24 * 365;
  isAccept.value = true;
};
</script>

<template>
  <Transition name="fade" appear>
    <div class="cookie-notice" v-if="!isAccept">
      <p>
        Мы собираем cookie. Используя сайт, вы соглашаетесь с
        <a href="/policy/" target="_blank">Политикой конфиденциальности</a>
      </p>
      <button class="cookie-btn" @click="accept">Хорошо</button>
    </div>
  </Transition>
</template>

<style scoped>
.cookie-notice {
  position: fixed;
  bottom: 20px;
  left: 20px;
  right: 20px;
  max-width: 669px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0px 0px 32px 0px #0000000f;
  border: 1px solid #f5f5f5;
  padding: 15px 20px;
  font-size: 14px;
  color: #333;
  z-index: 99999999999;
}

.cookie-notice p {
  margin: 0;
  flex: 1;
}

.cookie-notice a {
  color: #0056b3;
  text-decoration: none;
}

.cookie-notice a:hover {
  text-decoration: underline;
}

.cookie-btn {
  background: #4187cc;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.3s;
}

.cookie-btn:hover {
  background: #3b7bbb;
}

@media (max-width: 767px) {
  .cookie-notice {
    flex-direction: column;
    align-items: unset;
    gap: 7px;
    bottom: 0;
    left: 0;
    right: 0;
  }
}
</style>
