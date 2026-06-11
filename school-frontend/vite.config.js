import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite' // <-- Add this line!

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    // vueDevTools(),
    tailwindcss(), // Now this will work!
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})