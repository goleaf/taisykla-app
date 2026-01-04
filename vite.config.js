import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { ViteImageOptimizer } from 'vite-plugin-image-optimizer';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        ViteImageOptimizer({
            test: /\.(jpe?g|png|gif|tiff|webp|svg|avif)$/i,
        }),
    ],
    build: {
        minify: 'esbuild',
        cssMinify: true,
    },
});
