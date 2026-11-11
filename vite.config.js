import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  root: 'public',
  base: process.env.NODE_ENV === 'production' ? './' : '/',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(__dirname, 'public/index.html')
    }
  },
  server: {
    port: 3000,
    open: '/',
    cors: true,
    proxy: {
      '/api': {
        target: process.env.APP_URL,
        rewrite: (path) => path.replace(/^\/api/, ''),
        changeOrigin: true,
        secure: false
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'public/js'),
      '@components': resolve(__dirname, 'public/js/components'),
      '@composables': resolve(__dirname, 'public/js/composables'),
      '@css': resolve(__dirname, 'public/css'),
      'vue': 'vue/dist/vue.esm-bundler.js'
    }
  }
})
