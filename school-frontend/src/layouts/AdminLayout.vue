<template>
  <div class="flex h-screen overflow-hidden">
    <AppSidebar :isCollapsed="isCollapsed" />
    
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- បន្ថែម @toggle ដើម្បីឱ្យវាហៅ Function toggleSidebar -->
      <AppNavbar @toggle="toggleSidebar" />
      <main class="flex-1 overflow-y-auto">
        <router-view /> 
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AppSidebar from '../components/common/AppSidebar.vue'
import AppNavbar from '../components/common/AppNavbar.vue'

const isCollapsed = ref(false)

// Function នេះនឹងប្តូរតម្លៃ true/false
const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}
</script>