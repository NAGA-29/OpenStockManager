import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: true, // docker-compose の `npm run dev -- --host` 相当（0.0.0.0 で公開）
    port: 5173,
    strictPort: true,
  },
  preview: {
    host: true,
    port: 5173,
  },
});
