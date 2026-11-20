import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/css/contact.css',
    'resources/js/contact.js',
    'resources/css/news.css',
    'resources/js/news.js',
    'resources/css/about.css',
    'resources/js/about.js',
    'resources/css/show.css',
    'resources/js/show.js',
    'resources/js/checkout.js',
    'resources/css/checkout.css',
    'resources/css/auth.css',
    'resources/js/auth.js',
    'resources/css/wishlist.css',
    'resources/js/wishlist.js',
    'resources/css/admin.css',
    'resources/js/admin.js',
    'resources/css/seller.css',
    'resources/js/seller.js',
    
],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});