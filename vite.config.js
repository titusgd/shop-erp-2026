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
                'resources/js/users.js',
                'resources/js/users-create.js',
                'resources/js/users-edit.js',
                'resources/js/vendors.js',
                'resources/js/vendors-create.js',
                'resources/js/vendors-edit.js',
                'resources/js/vendors-show.js',
                'resources/js/customers.js',
                'resources/js/customers-create.js',
                'resources/js/customers-edit.js',
                'resources/js/customers-show.js',
                'resources/js/products.js',
                'resources/js/products-create.js',
                'resources/js/products-edit.js',
                'resources/js/products-show.js',
                'resources/js/product-categories.js',
                'resources/js/product-categories-create.js',
                'resources/js/product-categories-edit.js',
                'resources/js/product-units.js',
                'resources/js/product-units-create.js',
                'resources/js/product-units-edit.js',
                'resources/js/warehouse-types.js',
                'resources/js/warehouse-types-create.js',
                'resources/js/warehouse-types-edit.js',
                'resources/js/warehouses.js',
                'resources/js/warehouses-create.js',
                'resources/js/warehouses-edit.js',
                'resources/js/warehouses-histories.js',
                'resources/js/cities.js',
                'resources/js/cities-create.js',
                'resources/js/cities-edit.js',
                'resources/js/districts.js',
                'resources/js/districts-create.js',
                'resources/js/districts-edit.js',
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
