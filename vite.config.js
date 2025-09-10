import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { visualizer } from 'rollup-plugin-visualizer';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
            manifest: {
                name: 'COPRRA',
                short_name: 'COPRRA',
                theme_color: '#3b82f6',
                start_url: '/',
                display: 'standalone',
                background_color: '#ffffff',
                icons: [
                    {
                        src: '/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            }
        }),
        visualizer({
            gzipSize: true,
            brotliSize: true,
            template: 'treemap'
        })
    ],
    build: {
        cssMinify: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['axios']
                }
            }
        },
        chunkSizeWarningLimit: 1000
    },
    optimizeDeps: {
        include: ['axios']
    }
});
