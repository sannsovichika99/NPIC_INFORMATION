import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: {
            origin: '*',
        },
        proxy: {
            '/api': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            },
        },
        hmr: {
            protocol: 'ws',
            host: process.env.VITE_HMR_HOST || 'localhost',
            port: 5173,
        },
        watch: {
            usePolling: true,
            useFsEvents: true,
            interval: 1000,
        }
    },

});
