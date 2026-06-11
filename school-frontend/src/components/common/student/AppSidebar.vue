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
        class="text-6sm font-semibold tracking-wide mt-2 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400"
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
  { name: 'វត្តមាន', path: '/student/attendance', icon: ClipboardCheck },
  { name: 'ការប្រឡង', path: '/student/exam', icon: FileCheck },
  { name: 'កាលវិភាគ', path: '/student/schedule', icon: CalendarDays },
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
        <span class="absolute left-0 top-2 bottom-2 w-1 rounded-r-full bg-indigo-500 scale-y-0 group-[.router-link-active]:scale-y-100 transition-transform duration-300"></span>
        <component :is="item.icon" class="w-5 h-5 min-w-[20px] shrink-0" />
        <span v-if="!isCollapsed" class="ml-3 text-sm font-medium whitespace-nowrap">{{ item.name }}</span>
        
        <div v-if="isCollapsed" class="absolute left-20 bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition-all shadow-lg border border-slate-700 whitespace-nowrap">
          {{ item.name }}
        </div>
      </router-link>

      <button
        @click="handleLogout"
        class="relative flex items-center w-full h-11 rounded-xl text-rose-400 hover:text-white hover:bg-rose-900/30 transition-all group px-3 mt-4"
      >
        <LogOut class="w-5 h-5 min-w-[20px] shrink-0" />
        <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">ចាកចេញ</span>
        
        <div v-if="isCollapsed" class="absolute left-20 bg-rose-600 text-white px-3 py-1.5 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition-all shadow-lg whitespace-nowrap">
          ចាកចេញ
        </div>
      </button>
    </nav>

    <div class="p-4 border-t border-slate-800 text-xs text-slate-500 text-center">
      <span v-if="!isCollapsed">School Management System</span>
      <span v-else>SMS</span>
    </div>
  </aside>
</template>

<script setup>
import { 
  ClipboardCheck, FileCheck, CalendarDays, LogOut 
} from 'lucide-vue-next'
import { logoutUser } from '../../../services/authService';

defineProps({ isCollapsed: Boolean })

const menuItems = [
  { name: 'វត្តមាន', path: '/student/attendance', icon: ClipboardCheck },
  { name: 'ការប្រឡង', path: '/student/exam', icon: FileCheck },
  { name: 'កាលវិភាគ', path: '/student/schedule', icon: CalendarDays },
]

const handleLogout = async () => {
  if (!confirm('តើអ្នកពិតជាចង់ចាកចេញមែនទេ?')) return;
  try {
    await logoutUser();
  } catch (error) {
    console.error("Logout Error:", error);
  } finally {
    localStorage.clear();
    sessionStorage.clear();
    window.location.href = '/login'; 
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
</style>