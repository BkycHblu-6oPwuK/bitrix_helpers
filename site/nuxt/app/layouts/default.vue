<template>
  <div
    class="flex flex-col min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300"
  >
    <Header />

    <main class="flex-1 container mx-auto px-4 py-8">
      <slot />
    </main>

    <Footer />
  </div>
</template>