<template>
  <div class="flex h-screen bg-slate-50 font-khmer overflow-hidden">
    <AppSidebar :is-collapsed="isCollapsed" />

    <div class="font-[Battambang] flex-1 flex flex-col min-w-0 overflow-hidden">
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
import AppSidebar from '../components/common/student/AppSidebar.vue'
import AppNavbar from '../components/common/student/AppNavbar.vue' 

const isCollapsed = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>