<template>
  <transition name="fade-preloader">
    <div v-if="isLoading" class="preloader">
      <div class="logo_pic">
        <img :src="'/include/logo.png'" alt="Логотип" />
      </div>
      <div class="preloader_items">
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { usePreloader } from "@/lib/Composables/usePreloader.js";
const { isLoading } = usePreloader();
</script>

<style scoped>
.preloader .logo_pic {
  width: 80px;
  height: 80px;
}

.preloader {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  gap: 24px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.preloader_items {
  display: flex;
  align-items: center;
  gap: 6px;
}

@keyframes preloaderAnimation {
  0% { background: rgba(42, 91, 139, 1); }
  50% { background: rgba(65, 135, 204, 1); }
  100% { background: rgba(42, 91, 139, 1); }
}

.preloader_items div {
  width: 12px;
  height: 12px;
  background: rgba(65, 135, 204, 1);
  border-radius: 9999px;
  animation: preloaderAnimation 1.2s infinite ease-in-out;
}

.preloader_items div:nth-child(1) { animation-delay: 0s; }
.preloader_items div:nth-child(2) { animation-delay: 0.2s; }
.preloader_items div:nth-child(3) { animation-delay: 0.4s; }

.fade-preloader-enter-active,
.fade-preloader-leave-active {
  transition: opacity 0.35s ease;
}
.fade-preloader-enter-from,
.fade-preloader-leave-to {
  opacity: 0;
}
</style>
