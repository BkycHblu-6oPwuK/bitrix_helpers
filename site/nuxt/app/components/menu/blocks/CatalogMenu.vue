<script setup lang="ts">
import { ref } from 'vue'
import { Popover, PopoverPanel } from '@headlessui/vue'
import type { MenuBlock } from '~/types/menu'

const props = defineProps<{ data: MenuBlock }>()

const isOpen = ref(false)
let closeTimeout: ReturnType<typeof setTimeout> | null = null

function open() {
  if (closeTimeout) clearTimeout(closeTimeout)
  isOpen.value = true
}

function close() {
  if (closeTimeout) clearTimeout(closeTimeout)
  closeTimeout = setTimeout(() => {
    isOpen.value = false
  }, 300)
}

function handleClick() {
  navigateTo(props.data.LINK || '#')
}
</script>

<template>
  <Popover class="relative">
    <!-- Кнопка каталога -->
    <div
      class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 cursor-pointer select-none"
      @mouseenter="open"
      @mouseleave="close"
      @click="handleClick"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
      </svg>
      {{ props.data.NAME }}
    </div>

    <!-- Попап меню -->
    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <PopoverPanel
        v-if="isOpen"
        static
        class="absolute left-0 mt-2 w-[600px] rounded-xl bg-white shadow-xl border border-gray-200 p-4 z-50"
        @mouseenter="open"
        @mouseleave="close"
      >
        <div class="grid grid-cols-2 gap-4">
          <div v-for="(item, i) in props.data.CHILDREN" :key="i">
            <NuxtLink
              :to="item.LINK || '#'"
              class="font-semibold text-gray-800 hover:text-indigo-600 transition-colors"
            >
              {{ item.NAME }}
            </NuxtLink>

            <ul
              v-if="item.CHILDREN?.length"
              class="ml-3 mt-1 space-y-1 border-l border-gray-100 pl-2 text-sm"
            >
              <li v-for="child in item.CHILDREN" :key="child.ID">
                <NuxtLink
                  :to="child.LINK || '#'"
                  class="text-gray-600 hover:text-indigo-500 transition-colors"
                >
                  {{ child.NAME }}
                </NuxtLink>
              </li>
            </ul>
          </div>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>
