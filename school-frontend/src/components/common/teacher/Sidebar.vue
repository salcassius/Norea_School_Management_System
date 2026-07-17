<template>
  <aside :class="[
    'font-[Battambang] h-full bg-gradient-to-b from-slate-900 via-slate-950 to-black text-white flex flex-col shadow-2xl border-r border-slate-800',
    'fixed inset-y-0 left-0 z-40 w-64 transition-transform duration-300',
    'md:static md:z-20 md:transition-[width] md:duration-300',
    isMobileOpen ? 'translate-x-0' : '-translate-x-full',
    'md:translate-x-0',
    isCollapsed ? 'md:w-20' : 'md:w-64'
  ]">
    <!-- Logo & Title -->
    <div class="h-35 flex flex-col items-center justify-center border-b border-slate-800 relative">
      <!-- ប៊ូតុងបិទ Drawer (មានតែលើទូរស័ព្ទ) -->
      <button
        @click="emit('close')"
        class="md:hidden absolute top-3 right-3 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
      >
        <X class="w-5 h-5" />
      </button>

      <img src="../../../assets/logo.png" alt="Logo" class="h-18 object-contain" />
      <h2 v-if="!isCollapsed"
        class="text-[18px] font-semibold mt-2 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400 text-center px-2">
        អនុវិទ្យាល័យនរា
      </h2>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto custom-scrollbar">
      <div v-for="item in menuItems" :key="item.name">

        <router-link v-if="!item.children" :to="item.path"
          @click="emit('close')"
          class="relative flex items-center h-11 rounded-xl text-slate-400 hover:text-white transition-all group px-3"
          active-class="bg-indigo-600/20 text-white">
          <component :is="item.icon" class="w-5 h-5 shrink-0" />
          <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">{{ item.name }}</span>
        </router-link>

        <div v-else>
          <button @click="toggleDropdown(item.name)"
            class="w-full flex items-center h-11 rounded-xl text-slate-400 hover:text-white px-3 transition-all cursor-pointer">
            <component :is="item.icon" class="w-5 h-5 shrink-0" />
            <span v-if="!isCollapsed" class="ml-3 flex-1 text-left text-sm font-medium">{{ item.name }}</span>
            <ChevronDown v-if="!isCollapsed" :class="['w-4 h-4 transition-transform duration-200', expandedMenu === item.name ? 'rotate-180' : '']" />
          </button>

          <div v-if="expandedMenu === item.name && !isCollapsed" class="pl-8 mt-1 space-y-1">
            <router-link v-for="child in item.children" :key="child.path" :to="child.path"
              @click="emit('close')"
              class="block py-2 text-sm text-slate-500 hover:text-indigo-400 transition-colors"
              active-class="text-indigo-400 font-semibold">
              {{ child.name }}
            </router-link>
          </div>
        </div>
      </div>

      <!-- ប៊ូតុងចាកចេញ -->
      <button @click="isLogoutModalOpen = true"
        class="flex items-center w-full h-11 rounded-xl text-rose-400 hover:bg-rose-900/30 px-3 mt-4 transition-all cursor-pointer group">
        <LogOut class="w-5 h-5 shrink-0 transition-transform group-hover:-translate-x-1" />
        <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">ចាកចេញ</span>
      </button>
    </nav>
  </aside>

  <!-- ========================================================================================= -->
  <!-- ផ្ទាំងបញ្ជាក់ការចាកចេញ (Custom Logout Modal) -->
  <!-- ========================================================================================= -->
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="isLogoutModalOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/20 transition-all duration-300"
        @click.self="closeLogoutModal">

        <Transition name="modal-scale">
          <div v-if="isLogoutModalOpen"
            class="bg-white rounded-3xl border border-slate-200/80 w-full max-w-sm p-6 sm:p-8 text-center font-[Battambang] shadow-[0_20px_50px_rgba(0,0,0,0.25)] relative overflow-hidden">

            <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-36 h-36 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative w-16 h-16 rounded-full bg-red-100 border border-red-200 flex items-center justify-center mx-auto mb-5 shadow-inner">
              <LogOut class="w-7 h-7 text-rose-500 drop-shadow-[0_0_8px_rgba(244,63,94,0.5)]" />
            </div>

            <p class="text-[18px] text-slate-800 mb-2 font-[battambang]">ចាកចេញពីប្រព័ន្ធ</p>
            <p class="text-sm text-slate-600 leading-relaxed mb-7 font-medium">
              តើអ្នកពិតជាចង់ចាកចេញពីគណនីរបស់អ្នកឥឡូវនេះមែនទេ?
            </p>

            <div class="flex gap-3">
              <button @click="closeLogoutModal" :disabled="isLoggingOut"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50 cursor-pointer">
                បោះបង់
              </button>
              <button @click="confirmLogout" :disabled="isLoggingOut"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white bg-rose-500 hover:bg-rose-600 flex items-center justify-center gap-2 transition-all active:scale-95 shadow-md shadow-rose-500/20 disabled:opacity-60 cursor-pointer">
                <Loader2 v-if="isLoggingOut" class="w-4 h-4 animate-spin" />
                <LogOut v-else class="w-4 h-4" />
                <span>{{ isLoggingOut ? 'កំពុងចាកចេញ...' : 'ចាកចេញ' }}</span>
              </button>
            </div>

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { X, LayoutDashboard, Users, ClipboardCheck, FileCheck, CalendarDays, BarChart3, LogOut, Loader2, ChevronDown } from 'lucide-vue-next'
import { logoutUser } from '../../../services/authService'

// isCollapsed = desktop mini/wide toggle
// isMobileOpen = phone drawer open/closed
defineProps({
  isCollapsed: Boolean,
  isMobileOpen: Boolean
})

const emit = defineEmits(['close'])

const expandedMenu = ref(null)

// State សម្រាប់ Modal ចាកចេញ
const isLogoutModalOpen = ref(false)
const isLoggingOut = ref(false)

const toggleDropdown = (name) => {
  expandedMenu.value = expandedMenu.value === name ? null : name
}

const menuItems = [
  { name: 'ផ្ទាំងគ្រប់គ្រង', path: '/teacher/dashboard', icon: LayoutDashboard },
  { name: 'សិស្សានុសិស្ស', path: '/teacher/student', icon: Users },
  { name: 'វត្តមានសិស្ស', path: '/teacher/teacherattendance', icon: ClipboardCheck },
  { name: 'ការប្រឡង & ពិន្ទុ', path: '/teacher/inputscores', icon: FileCheck },
  { name: 'កាលវិភាគបង្រៀន', path: '/teacher/scheduleteacher', icon: CalendarDays },
  { name: 'របាយការណ៍លទ្ធផល', path: '/teacher/report', icon: BarChart3 },
]

// បិទ Modal ចាកចេញ
const closeLogoutModal = () => {
  if (isLoggingOut.value) return
  isLogoutModalOpen.value = false
}

// បញ្ជាក់ការចាកចេញ
const confirmLogout = async () => {
  try {
    isLoggingOut.value = true
    await new Promise(resolve => setTimeout(resolve, 500))
    await logoutUser()
    localStorage.clear()
    window.location.href = '/login'
  } catch (error) {
    console.error("Logout failed", error)
    localStorage.clear()
    window.location.href = '/login'
  } finally {
    isLoggingOut.value = false
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 10px;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-scale-enter-active,
.modal-scale-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-scale-enter-from,
.modal-scale-leave-to {
  transform: scale(0.95);
  opacity: 0;
}
</style>