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
          src="../../assets/logo.png" 
          alt="Logo"
          class="h-18 object-contain transition-all duration-500 group-hover:scale-110"
        />
      </div>

      <h2 
        v-if="!isCollapsed"
        class="text-xl font-semibold tracking-wide mt-2 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400"
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
  { name: 'ផ្ទាំងគ្រប់គ្រង', path: '/admin/dashboard', icon: LayoutDashboard },
  { name: 'អ្នកប្រើប្រាស់', path: '/admin/user', icon: UserCog },
  { name: 'គ្រូបង្រៀន', path: '/admin/teacher', icon: GraduationCap },
  { name: 'សិស្សានុសិស្ស', path: '/admin/student', icon: Users },
  { name: 'មុខវិជ្ជា', path: '/admin/subject', icon: BookOpen },
  { name: 'វត្តមាន', path: '/admin/attendance', icon: ClipboardCheck },
  { name: 'ថ្នាក់រៀន', path: '/admin/class', icon: School },
  { name: 'ការប្រឡង', path: '/admin/exam', icon: FileCheck },
  { name: 'កាលវិភាគ', path: '/admin/schedule', icon: CalendarDays },
  { name: 'ឆ្នាំសិក្សា', path: '/admin/year', icon: CalendarRange },
  { name: 'របាយការណ៍', path: '/admin/report', icon: BarChart3 },
  { name: 'ការកំណត់', path: '/admin/setting', icon: Settings },
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
  <aside :class="[
    'font-[Battambang] h-full bg-gradient-to-b from-slate-900 via-slate-950 to-black text-white transition-all duration-300 flex flex-col z-20 shadow-2xl border-r border-slate-800',
    isCollapsed ? 'w-20' : 'w-64'
  ]">
    <div class="h-35 flex flex-col items-center justify-center border-b border-slate-800 relative">
      <img src="../../assets/logo.png" alt="Logo" class="h-18 object-contain" />
      <h2 v-if="!isCollapsed"
        class="text-xl font-semibold mt-2 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">
        អនុវិទ្យាល័យនរា
      </h2>
    </div>

    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto custom-scrollbar">
      <div v-for="item in menuItems" :key="item.name">
        
        <router-link v-if="!item.children" :to="item.path"
          class="relative flex items-center h-11 rounded-xl text-slate-400 hover:text-white transition-all group px-3"
          active-class="bg-indigo-600/20 text-white">
          <component :is="item.icon" class="w-5 h-5 shrink-0" />
          <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">{{ item.name }}</span>
        </router-link>

        <div v-else>
          <button @click="toggleDropdown(item.name)"
            class="w-full flex items-center h-11 rounded-xl text-slate-400 hover:text-white px-3 transition-all">
            <component :is="item.icon" class="w-5 h-5 shrink-0" />
            <span v-if="!isCollapsed" class="ml-3 flex-1 text-left text-sm font-medium">{{ item.name }}</span>
            <ChevronDown v-if="!isCollapsed" :class="['w-4 h-4 transition-transform duration-200', expandedMenu === item.name ? 'rotate-180' : '']" />
          </button>

          <div v-if="expandedMenu === item.name && !isCollapsed" class="pl-8 mt-1 space-y-1">
            <router-link v-for="child in item.children" :key="child.path" :to="child.path"
              class="block py-2 text-sm text-slate-500 hover:text-indigo-400 transition-colors"
              active-class="text-indigo-400 font-semibold">
              {{ child.name }}
            </router-link>
          </div>
        </div>
      </div>

      <button @click="handleLogout"
        class="flex items-center w-full h-11 rounded-xl text-rose-400 hover:bg-rose-900/30 px-3 mt-4">
        <LogOut class="w-5 h-5 shrink-0" />
        <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">ចាកចេញ</span>
      </button>
    </nav>
  </aside>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import {
  LayoutDashboard, UserCog, GraduationCap, Users, ClipboardCheck,
  School,BookOpen, FileCheck, CalendarDays, CalendarRange, BarChart3,
  LogOut, ChevronDown
} from 'lucide-vue-next'

const route = useRoute()
defineProps({ isCollapsed: Boolean })

const expandedMenu = ref(null)

// ឧទាហរណ៍នៃរចនាសម្ព័ន្ធ menuItems ដែលមាន Sub-menu
const menuItems = [
  { name: 'ផ្ទាំងគ្រប់គ្រង', path: '/admin/dashboard', icon: LayoutDashboard },
  { name: 'អ្នកប្រើប្រាស់', path: '/admin/user', icon: UserCog },
  { name: 'គ្រូបង្រៀន', path: '/admin/teacher', icon: GraduationCap },
  { name: 'សិស្សានុសិស្ស', path: '/admin/student', icon: Users },
  { name: 'វត្តមាន', path: '/admin/attendance', icon: ClipboardCheck },
  { name: 'មុខវិជ្ជា', path: '/admin/subject', icon: BookOpen },
  { name: 'ថ្នាក់រៀន', path: '/admin/class', icon: School },
  { name: 'ការប្រឡង​ &ពិន្ទុ', path: '/admin/exam', icon: FileCheck },
  { name: 'កាលវិភាគ', path: '/admin/schedule', icon: CalendarDays },
  { name: 'ឆ្នាំសិក្សា', path: '/admin/year', icon: CalendarRange },
  { name: 'របាយការណ៍', path: '/admin/report', icon: BarChart3 },
]

const toggleDropdown = (name) => {
  expandedMenu.value = expandedMenu.value === name ? null : name
}

const handleLogout = async () => {
  if (!confirm('តើអ្នកពិតជាចង់ចាកចេញមែនទេ?')) return;
  localStorage.clear();
  window.location.href = '/login';
}

onMounted(() => {
  // បើក menu ស្វ័យប្រវត្តិប្រសិនបើយើងនៅក្នុង path នោះស្រាប់
  const activeItem = menuItems.find(item => item.children && route.path.includes(item.path))
  if (activeItem) expandedMenu.value = activeItem.name
})
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