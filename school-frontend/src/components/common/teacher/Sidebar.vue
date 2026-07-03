<!-- <template>
  <aside
    :class="[
    'font-[Battambang] h-full bg-gradient-to-b from-slate-900 via-slate-950 to-black text-white transition-all duration-300 flex flex-col z-20 shadow-2xl border-r border-slate-800',
    isCollapsed ? 'w-20' : 'w-64'
  ]"
  >
    <div class="h-35 flex flex-col items-center justify-center border-b border-slate-800 relative">
      
      <div class="relative group">
        <img 
          src="../../../assets/logo.png" 
          alt="Logo"
          class="h-18 object-contain transition-all duration-500 group-hover:scale-110"
        />
      </div>

      <h2 
        v-if="!isCollapsed"
        class="text-sm font-semibold tracking-wide mt-2 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400"
      >
        អនុវិទ្យាល័យនរា
      </h2>
    </div>


    <nav class="flex-1 py-6 px-3 space-y-2 overflow-y-auto custom-scrollbar">

      <router-link
        v-for="item in menuItems"
        :key="item.path"
        :to="item.path"
        class="relative flex items-center h-11 rounded-xl text-slate-400 hover:text-white transition-all group px-3"
        active-class="bg-indigo-600/20 text-white"
      >

        <span
          class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-indigo-500 scale-y-0 group-[.router-link-active]:scale-y-100 transition-transform duration-300"
        ></span>


        <component 
          :is="item.icon" 
          class="w-5 h-5 min-w-[20px] shrink-0 transition-all duration-300 group-hover:scale-110"
        />

        <span 
          v-if="!isCollapsed"
          class="ml-3 text-sm font-medium whitespace-nowrap"
        >
          {{ item.name }}
        </span>

        <div
          v-if="isCollapsed"
          class="absolute left-20 bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg border border-slate-700 whitespace-nowrap"
        >
          {{ item.name }}
        </div>
      </router-link>

    </nav>

    <div class="p-4 border-t border-slate-800 text-xs text-slate-500 text-center">
      <span v-if="!isCollapsed">School Management System</span>
      <span v-else>SMS</span>
    </div>

  </aside>
</template>

<script setup>
import { 
  LayoutDashboard, UserCog, GraduationCap, Users, BookOpen, 
  ClipboardCheck, School, FileCheck, CalendarDays, 
  CalendarRange, BarChart3, Settings 
} from 'lucide-vue-next'

defineProps({ isCollapsed: Boolean })

const menuItems = [
  { name: 'ផ្ទាំងគ្រប់គ្រង', path: '/teacher/dashboard', icon: LayoutDashboard },
  { name: 'សិស្សានុសិស្ស', path: '/teacher/student', icon: Users },
  { name: 'វត្តមានសិស្ស', path: '/teacher/attendance', icon: ClipboardCheck },
  { name: 'ការប្រឡង & ពិន្ទុ', path: '/teacher/exam', icon: FileCheck },
  { name: 'កាលវិភាគបង្រៀន', path: '/teacher/schedule', icon: CalendarDays },
  { name: 'របាយការណ៍លទ្ធផល', path: '/teacher/report', icon: BarChart3 },
]
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 10px;
}
</style> -->





<template>
  <aside
    :class="[
      'font-[Battambang] h-full bg-gradient-to-b from-slate-900 via-slate-950 to-black text-white transition-all duration-300 flex flex-col z-20 shadow-2xl border-r border-slate-800',
      isCollapsed ? 'w-20' : 'w-64'
    ]"
  >
    <div class="h-35 flex flex-col items-center justify-center border-b border-slate-800 relative">
      <img src="../../../assets/logo.png" alt="Logo" class="h-18 object-contain transition-all duration-500 hover:scale-110" />
      <h2 v-if="!isCollapsed" class="text-sm font-semibold mt-2 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">
        អនុវិទ្យាល័យនរា
      </h2>
    </div>

    <nav class="flex-1 py-6 px-3 space-y-2 overflow-y-auto custom-scrollbar">
      <template v-for="item in menuItems" :key="item.name">
        
        <div v-if="item.children" class="space-y-1">
          <button 
            @click="toggleSubMenu(item.name)"
            class="w-full flex items-center h-11 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all px-3"
          >
            <component :is="item.icon" class="w-5 h-5 shrink-0" />
            <span v-if="!isCollapsed" class="ml-3 text-sm font-medium flex-1 text-left">{{ item.name }}</span>
            <span v-if="!isCollapsed" class="text-xs opacity-50">
              {{ activeSubMenu === item.name ? '▼' : '▶' }}
            </span>
          </button>

          <div v-if="activeSubMenu === item.name && !isCollapsed" class="pl-4 mt-1 space-y-1">
            <router-link
              v-for="sub in item.children"
              :key="sub.path"
              :to="sub.path"
              class="flex items-center h-9 px-4 text-xs text-slate-500 hover:text-white rounded-lg hover:bg-indigo-600/10 transition-all"
              active-class="!text-indigo-400 bg-indigo-600/10 font-bold"
            >
              {{ sub.name }}
            </router-link>
          </div>
        </div>

        <router-link
          v-else
          :to="item.path"
          class="relative flex items-center h-11 rounded-xl text-slate-400 hover:text-white transition-all group px-3"
          active-class="bg-indigo-600/20 text-white"
        >
          <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-indigo-500 scale-y-0 group-[.router-link-active]:scale-y-100 transition-transform duration-300"></span>
          <component :is="item.icon" class="w-5 h-5 shrink-0" />
          <span v-if="!isCollapsed" class="ml-3 text-sm font-medium whitespace-nowrap">{{ item.name }}</span>
        </router-link>
      </template>

      <button @click="handleLogout" class="relative flex items-center w-full h-11 rounded-xl text-rose-400 hover:text-white hover:bg-rose-900/30 transition-all px-3 mt-4">
        <LogOut class="w-5 h-5 shrink-0" />
        <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">ចាកចេញ</span>
      </button>
    </nav>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { LayoutDashboard, Users, ClipboardCheck, FileCheck, CalendarDays, BarChart3, LogOut } from 'lucide-vue-next'
import { logoutUser } from '../../../services/authService'

defineProps({ isCollapsed: Boolean })

const activeSubMenu = ref(null)

const toggleSubMenu = (name) => {
  activeSubMenu.value = activeSubMenu.value === name ? null : name
}

const menuItems = [
  { name: 'ផ្ទាំងគ្រប់គ្រង', path: '/teacher/dashboard', icon: LayoutDashboard },
  { name: 'សិស្សានុសិស្ស', path: '/teacher/student', icon: Users },
  { name: 'វត្តមានសិស្ស', path: '/teacher/teacherattendance', icon: ClipboardCheck,},
  { name: 'ការប្រឡង & ពិន្ទុ', path: '/teacher/inputscores', icon: FileCheck },
  { name: 'កាលវិភាគបង្រៀន', path: '/teacher/scheduleteacher', icon: CalendarDays },
  { name: 'របាយការណ៍លទ្ធផល', path: '/teacher/report', icon: BarChart3 },
]

const handleLogout = async () => { try {
    await logoutUser()
  } catch (error) {
    console.error("Logout failed", error)
  } 
}
</script>