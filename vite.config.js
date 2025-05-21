import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // Set the base URL for production assets
    base: process.env.APP_URL ? `${process.env.APP_URL}/build/` : '/build/',
    // Add CORS configuration
    server: {
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        // Generate a manifest.json file for the build
        manifest: true,
        // Output assets to the public/build directory
        outDir: 'public/build',
        // Remove cssCodeSplit as it conflicts with having CSS in input files
    },
});
