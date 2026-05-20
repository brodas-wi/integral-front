import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    base: '/bancaintegral/public/build/',
    server: {
        host: '127.0.0.1',
        port: 5174,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/fonts/poppins.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
