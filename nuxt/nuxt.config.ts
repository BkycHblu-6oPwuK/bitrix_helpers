import tailwindcss from "@tailwindcss/vite";

const isDev = process.env.NODE_ENV === 'development'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  runtimeConfig: {
    apiBaseServer: process.env.NUXT_API_BASE_SERVER,
    debug: process.env.NUXT_DEBUG === 'true',
    public: {
      apiBaseClient: process.env.NUXT_PUBLIC_API_BASE_CLIENT,
    },
  },

  routeRules: {
    '/api/v1/get-main-page': { swr: isDev ? false : 300 },
    '/api/v1/get-menu': { swr: isDev ? false : 600 },
    '/api/v1/catalog': { swr: isDev ? false : 300 },
    '/api/v1/catalog/**': { swr: isDev ? false : 300 },
    '/api/v1/product/**': { swr: isDev ? false : 300 },
    '/api/v1/articles': { swr: isDev ? false : 300 },
    '/api/v1/articles/**': { swr: isDev ? false : 300 },
    '/api/v1/reviews': { swr: isDev ? false : 300 },
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