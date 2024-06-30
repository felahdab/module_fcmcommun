import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url); // get the resolved path to the file
const __dirname = path.dirname(__filename); // get the name of the directory

export default defineConfig({
    build: {
        outDir: '../../public/build-fcmcommun',
        emptyOutDir: true,
        manifest: true,
    },
    resolve: {
        alias: {
            "@mingle": path.resolve("/app/vendor/ijpatricio/mingle/resources/js"),
        },
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-fcmcommun',
            input: [
                __dirname + '/resources/assets/js/TestMingle/index.js'
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});

export const paths = [
    'Modules/FcmCommun/resources/assets/js/TestMingle/index.js',
//    'Modules/FcmCommun/resources/assets/sass/app.scss',
//    'Modules/FcmCommun/resources/assets/js/app.js',
];