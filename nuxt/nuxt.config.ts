import tailwindcss from "@tailwindcss/vite";

const isDev = process.env.NODE_ENV === 'development';
const buildAtIso = process.env.NUXT_BUILD_AT || new Date().toISOString();

export default defineNuxtConfig({
    compatibilityDate: '2026-01-01',

    runtimeConfig: {
        apiBaseServer: process.env.NUXT_API_BASE_SERVER,
        buildAt: buildAtIso,
        debug: process.env.NUXT_DEBUG === 'true',
        public: {
            apiBaseClient: process.env.NUXT_PUBLIC_API_BASE_CLIENT,
        },
    },

    ui: {
        fonts: false
    },

    devtools: {
        enabled: isDev
    },

    sourcemap: {
        client: isDev,
        server: isDev
    },

    sitemap: {
        urls: [
            {
                loc: '/',
                priority: 1.0
            }
        ],
        cacheMaxAgeSeconds: isDev ? 0 : 600,
        // sources: [
        //     '/api/sitemap',
        // ],
        strictNuxtContentPaths: true,
        xslColumns: [
            { label: 'URL', width: '50%' },
            { label: 'Last Modified', select: 'sitemap:lastmod', width: '12.5%' },
            { label: 'Priority', select: 'sitemap:priority', width: '12.5%' },
            { label: 'Change Frequency', select: 'sitemap:changefreq', width: '25%' },
        ],
        defaults: {
            lastmod: buildAtIso,
            changefreq: 'daily',
            priority: 0.9,
        },
    },

    experimental: {
        defaults: {
            nuxtLink: {
                trailingSlash: 'append',
                prefetch: false
            }
        }
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

    modules: ['@pinia/nuxt', 'nuxt-toast', '@nuxt/ui', '@nuxtjs/sitemap'],

    features: {
        inlineStyles: true
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
        '/**': {
            headers: {
                'cache-control': 'public, max-age=60, stale-while-revalidate=300',
            },
        },
    },

})