import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css'],
            refresh: ['app/Modules/**', 'resources/views/**', 'routes/**'],
        }),
        vue({ template: { transformAssetUrls: { base: null, includeAbsolute: false } } }),
    ],
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
            '@modules': path.resolve('app/Modules'),
            'ziggy-js': path.resolve('vendor/tightenco/ziggy'),
        },
    },
});
