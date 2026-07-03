<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-slate-800">សួស្តីលោកគ្រូ/អ្នកគ្រូ! 👋</h1>
      <p class="text-slate-500 mt-1">សូមស្វាគមន៍មកកាន់ប្រព័ន្ធគ្រប់គ្រងសាលារៀន</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 font-[battambang]">
      <div v-for="stat in stats" :key="stat.title" class="p-6 bg-white rounded-2xl border border-slate-300 shadow-sm flex items-center gap-4">
        <div :class="['p-3 rounded-xl', stat.bgColor]">
          <component :is="stat.icon" class="w-6 h-6" :class="stat.iconColor" />
        </div>
        <div>
          <p class="text-[14px] text-slate-600 font-bold uppercase">{{ stat.title }}</p>
          <p class="text-xl font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">កាលវិភាគថ្ងៃនេះ</h3>
        
        <div v-if="loading" class="text-center py-10 text-slate-400">កំពុងផ្ទុកទិន្នន័យ...</div>
        
        <div v-else-if="schedule.length > 0" class="space-y-4">
          <div v-for="sched in schedule" :key="sched.id" class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-xl transition-colors border border-transparent hover:border-slate-100">
            <div class="text-1xs font-bold text-indigo-600 bg-indigo-50 px-3 py-2 rounded-lg min-w-[80px] text-center">
              {{ sched.time }}
            </div>
            <div class="flex-1">
              <p class="text-1xs font-bold text-slate-800">{{ sched.subject }}</p>
              <p class="text-[14px] font-bold text-slate-800">{{ sched.class_name }}{{ sched.room }}</p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-10 text-slate-400">មិនមានកាលវិភាគសម្រាប់ថ្ងៃនេះទេ</div>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4">មុខងាររហ័ស</h3>
        <div class="space-y-3">
          <button @click="$router.push('/teacher/teacherattendance')" class="w-full text-left p-4 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50 transition-all text-sm font-medium text-slate-600 flex items-center gap-2">
            <span>➕</span> បញ្ចូលវត្តមានសិស្ស
          </button>
          <button @click="$router.push('/teacher/inputscores')" class="w-full text-left p-4 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50 transition-all text-sm font-medium text-slate-600 flex items-center gap-2">
            <span>📊</span> ដាក់ពិន្ទុការប្រឡង
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Users, ClipboardCheck, Clock } from 'lucide-vue-next'
import api from '../../services/authService' 

const loading = ref(true)
const schedule = ref([])

const stats = ref([
  { title: 'សិស្សក្នុងថ្នាក់', value: '0', icon: Users, bgColor: 'bg-blue-50', iconColor: 'text-blue-600' },
  { title: 'វត្តមានសិស្ស', value: '0%', icon: ClipboardCheck, bgColor: 'bg-emerald-50', iconColor: 'text-emerald-600' },
  { title: 'ម៉ោងបង្រៀន', value: '0h', icon: Clock, bgColor: 'bg-amber-50', iconColor: 'text-amber-600' },
])

const fetchDashboardData = async () => {
  try {
    loading.value = true
    const response = await api.get('/teacher/dashboard') 
    const data = response.data

    // ភ្ជាប់ទិន្នន័យពី API ទៅ UI
    stats.value[0].value = data.total_students
    stats.value[1].value = data.attendance_rate + '%'
    stats.value[2].value = data.total_hours + 'h'
    
    schedule.value = data.today_schedule
  } catch (error) {
    console.error("Error fetching dashboard:", error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>