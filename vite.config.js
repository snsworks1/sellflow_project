import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                 'resources/js/app.js',
                 'resources/js/register.js'
                
                ],
            buildDirectory: 'build', // 📌 `.vite` 폴더에 빌드
            refresh: true,
        }),
    ],
    build: {
        manifest: true,
        outDir: 'public/build/', // 📌 `.vite` 폴더로 출력
        emptyOutDir: true,
    },
});
