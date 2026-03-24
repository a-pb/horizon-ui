import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        outDir: 'dist',
        emptyOutDir: false,
        rollupOptions: {
            input: 'resources/js/app.js',
            output: {
                entryFileNames: 'app.js',
                assetFileNames: 'app.css',
                format: 'iife',
                inlineDynamicImports: true,
            },
        },
        cssCodeSplit: false,
        minify: 'esbuild',
    },
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
});
