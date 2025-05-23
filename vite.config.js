import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Explicitly set the build path
            publicDirectory: 'public',
            buildDirectory: 'build',
            // Force manifest to be in the build directory
            manifest: true
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
        // Ensure manifest is generated
        manifest: true,
        // Force the output directory
        outDir: 'public/build',
        // Make sure assets are in the correct path
        assetsDir: 'assets',
        // Clean the output directory before building
        emptyOutDir: true,
        rollupOptions: {
            output: {
                // Ensure proper asset naming
                assetFileNames: 'assets/[name]-[hash][extname]',
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },
    },
    // Expose Pusher environment variables
    define: {
        'import.meta.env.VITE_PUSHER_APP_KEY': JSON.stringify(process.env.PUSHER_APP_KEY || ''),
        'import.meta.env.VITE_PUSHER_APP_CLUSTER': JSON.stringify(process.env.PUSHER_APP_CLUSTER || 'eu'),
        'import.meta.env.VITE_PUSHER_APP_HOST': JSON.stringify(process.env.PUSHER_HOST || 'api.pusher.com'),
        'import.meta.env.VITE_PUSHER_APP_PORT': JSON.stringify(process.env.PUSHER_PORT || '443'),
        'import.meta.env.VITE_PUSHER_APP_SCHEME': JSON.stringify(process.env.PUSHER_SCHEME || 'https'),
    },
});
