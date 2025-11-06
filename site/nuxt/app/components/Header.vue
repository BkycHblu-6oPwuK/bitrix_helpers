<template>
  <header class="bg-white border-b border-gray-200 shadow-sm">
    <div class="bg-gray-50 border-b border-gray-200">
      <div class="container mx-auto px-4 py-2 flex justify-end text-sm text-gray-600">
        <Menu :menu-type="'top_menu'" />
      </div>
    </div>

    <div class="container mx-auto flex items-center justify-between px-4 py-4">
      <div class="flex items-center gap-6">
        <NuxtLink
          to="/"
          class="flex items-center gap-2 text-2xl font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
        >
          <span>MyNuxtApp</span>
        </NuxtLink>

        <Menu :menu-type="'catalog_menu'" />
      </div>
      <div class="flex items-center gap-4">

      </div>
    </div>
  </header>
</template>
