import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  runtimeConfig: {
    apiBaseServer: process.env.NUXT_API_BASE_SERVER,
    public: {
      apiBaseClient: process.env.NUXT_PUBLIC_API_BASE_CLIENT,
    },
  },

  routeRules: {
    '/': { swr: 300 },
    '/catalog': { swr: 300 },
    '/catalog/**': { swr: 300 },
    '/product/**': { swr: 300 },
    '/articles': { swr: 300 },
    '/articles/**': { swr: 300 },
    '/reviews': { swr: 300 },
  },

  ui: {
    fonts: false
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