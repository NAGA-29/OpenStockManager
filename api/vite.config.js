import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/sidebar.css',
                'resources/css/table.css',
                'resources/css/slideshow.css',
                'resources/css/page_loading.css',
                'resources/css/barcode-print.css',
                'resources/css/edit-user-dialog.css',
                'resources/js/app.js',
                'resources/js/search.js',
                'resources/js/user/edit-user-modal.js',
                'resources/js/components/cart.ts',
                'resources/js/ui/loading/loading.ts',
                'resources/js/ui/sidebar/sidebar-toggle.ts',
                'resources/js/components/slideshow.ts',
                'resources/js/components/gallery.ts',
                'resources/js/ui/button/synchronize-button.ts',
                'resources/js/components/sortable-categories.ts',
                'resources/js/components/barcode-print.ts',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
