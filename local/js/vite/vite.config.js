import { defineConfig } from 'vite'

import vue from '@vitejs/plugin-vue';
import VueMacros from "unplugin-vue-macros/vite";

import dotenv from 'dotenv';
import path from 'path';

const envPath = path.resolve(__dirname, '../../../local/php_interface/include/.env'); // путь до .env относительно текущей директории p.s. переменно MODE значение production в js файле установится автоматически после билда
const result = dotenv.config({ path: envPath });

if (result.error) {
  throw result.error; // в контейнере node, в консоли, можно посмотреть сообщение об ошибке
}

const env = process.env;
const base = env.MODE === 'production' ?  `/${env.VITE_BASE_PATH}/${env.VITE_CLIENT_PATH}` : `/${env.VITE_BASE_PATH}`;
export default defineConfig({
    plugins: [
        VueMacros({
            plugins: {
                vue: vue(),
            },
        }),
    ],
    //define: {
    //    'process.env': env // можно передать переменные в клиентский код
    //},
    base: base,
    build: {
        outDir: env.VITE_CLIENT_PATH,
        assetsDir: '.',
        copyPublicDir: false,
        manifest: true,
        rollupOptions: {
            input: {
                bundle: 'src/common/js/bundle.js',
                catalog: 'src/app/catalog/index.js',
                catalog_element: 'src/app/catalog_element/index.js',
                cart: 'src/app/cart/index.js',
                checkout: 'src/app/checkout/index.js',
                dressing: 'src/app/dressing/index.js',
                profile: 'src/app/profile/index.js',
                articles: 'src/app/articles/index.js',
            },
        },
    },
    resolve: {
        alias: {
            '@': '/src',
        },
    },
    server: {
        host: '0.0.0.0',
        port: env.VITE_PORT,
        open: false,
        cors: {
            origin: '*'
        },
        hmr: {
            host: 'localhost',
        },
    }
});
