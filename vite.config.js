import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'public/build',
    manifest: true,
    rollupOptions: {
      input: resolve(__dirname, 'resources/js/app.js')
    }
  },
  server: {
    proxy: {
      '/api': 'http://localhost:8080'
    }
  }
});
