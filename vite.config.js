import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

import path from 'path';
import { fileURLToPath } from 'url';

// const __filename = fileURLToPath(import.meta.url); // get the resolved path to the file
// const __dirname = path.dirname(__filename); // get the name of the directory

console.log( __dirname + '/resources/assets/js/LivretDeTransformation/index.js');

export default defineConfig({
    build: {
        //outDir: '../../public/assets/modules/fcmcommun',
        outDir: 'resources/assets/js/dist/',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            output: {
                assetFileNames: 'livret-de-transformation.js',
                //chunkFileNames: 'client/rsc/[chunks]/[name].[hash].js',
                entryFileNames: 'livret-de-transformation.js'
            }
        }
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
                './resources/assets/js/src/LivretDeTransformation/index.js'
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
    'Modules/FcmCommun/resources/assets/js/src/LivretDeTransformation/index.js',
//    'Modules/FcmCommun/resources/assets/sass/app.scss',
//    'Modules/FcmCommun/resources/assets/js/app.js',
];