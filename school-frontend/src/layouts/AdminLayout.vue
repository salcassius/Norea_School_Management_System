<template>
  <div class="flex h-screen overflow-hidden">
    <AppSidebar
      :isCollapsed="isCollapsed"
      :isMobileOpen="isMobileOpen"
      @close="isMobileOpen = false"
    />

    <!-- Backdrop overlay: only visible on mobile when drawer is open -->
    <Transition name="fade">
      <div
        v-if="isMobileOpen"
        @click="isMobileOpen = false"
        class="fixed inset-0 bg-black/40 z-30 md:hidden"
      ></div>
    </Transition>

    <div class="flex-1 flex flex-col overflow-hidden w-full">
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

// isCollapsed = desktop mini/wide toggle
// isMobileOpen = phone drawer open/closed
const isCollapsed = ref(false)
const isMobileOpen = ref(false)

// លើទូរស័ព្ទ (< 768px) → បើក/បិទ Drawer
// លើ Desktop (>= 768px) → បង្រួម/ពង្រីក Sidebar
const toggleSidebar = () => {
  if (window.innerWidth < 768) {
    isMobileOpen.value = !isMobileOpen.value
  } else {
    isCollapsed.value = !isCollapsed.value
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>