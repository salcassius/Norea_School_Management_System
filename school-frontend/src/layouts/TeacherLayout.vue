<template>
  <div class="flex h-screen bg-slate-50 font-khmer overflow-hidden">
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

    <div class="font-[Battambang] flex-1 flex flex-col min-w-0 overflow-hidden w-full">
      <!-- @toggle ហៅ Function toggleSidebar -->
      <AppNavbar @toggle="toggleSidebar" />

      <main class="flex-1 overflow-y-auto p-6">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
// ប្តូរពី Sidebar ទៅជា AppSidebar (teacher)
import AppSidebar from '../components/common/teacher/Sidebar.vue'
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