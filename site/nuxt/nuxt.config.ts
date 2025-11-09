import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  runtimeConfig: {
    apiBaseServer: process.env.NUXT_API_BASE_SERVER,
    public: {
      apiBaseClient: process.env.NUXT_PUBLIC_API_BASE_CLIENT,
    },
  },

  devtools: {
    enabled: true
  },

  css: ['./app/assets/css/main.css'],

  devServer: {
    host: '0.0.0.0',
    port: 5173,
  },

  vite: {
    plugins: [
      tailwindcss(),
    ],
  },

  modules: ['@pinia/nuxt', 'nuxt-toast', '@nuxt/ui'],
})