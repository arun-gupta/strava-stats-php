import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'public/build',
    manifest: true,
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true
      }
    },
    rollupOptions: {
      input: resolve(__dirname, 'resources/js/app.js'),
      output: {
        manualChunks: {
          'chart': ['chart.js', 'chartjs-plugin-datalabels']
        }
      }
    },
    chunkSizeWarningLimit: 600
  },
  server: {
    proxy: {
      '/api': 'http://localhost:8080'
    }
  }
});
