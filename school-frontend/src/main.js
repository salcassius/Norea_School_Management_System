import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'

createApp(App).use(router).mount('#app')

const savedTheme = localStorage.getItem('theme')

if (savedTheme === 'dark') {
  document.documentElement.classList.add('dark')
}