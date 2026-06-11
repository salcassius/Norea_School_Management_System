<template>
  <div class="space-y-6 font-[Battambang] pb-10 bg-slate-50/50 min-h-screen p-4 md:p-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 font-[Khmer_OS_Muol_Light]">ផ្ទាំងគ្រប់គ្រងទូទៅ</h2>
        <p class="text-slate-500 text-sm mt-1">ព័ត៌មានសង្ខេបនៃសាលារៀន និងស្ថិតិសំខាន់ៗ</p>
      </div>

      <div class="flex items-center gap-3">
        <label class="text-sm font-medium text-slate-600 hidden sm:block">ឆ្នាំសិក្សា:</label>
        <select v-model="selectedYearId" @change="handleYearChange"
          class="bg-white border border-slate-200 text-slate-600 text-sm rounded-xl px-4 py-2.5 outline-none focus:ring-4 focus:ring-indigo-500/10 hover:border-indigo-300 shadow-sm transition-all cursor-pointer font-semibold">
          <option value="" disabled>ជ្រើសរើសឆ្នាំសិក្សា</option>
          <option v-for="year in academicYears" :key="year.id" :value="year.id">
            ឆ្នាំសិក្សា {{ year.year_name }} {{ year.is_active ? '(បច្ចុប្បន្ន)' : '' }}
          </option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="(stat, index) in statsCards" :key="index"
        class="p-5 rounded-2xl border border-slate-200 bg-white shadow-sm flex items-center gap-4 transition-all hover:border-indigo-300 hover:shadow-md active:scale-95 group cursor-pointer">
        
        <div :class="['p-3 rounded-xl transition-colors', stat.bgClass]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconClass]" />
        </div>
        
        <div class="overflow-hidden w-full">
          <p class="text-[14px] text-slate-500 font-medium truncate">{{ stat.title }}</p>
          <p class="text-xl font-bold text-slate-800 mt-0.5">{{ stat.value }}</p>
          <p v-if="stat.subtext" class="text-[12px] text-slate-400 mt-0.5 italic truncate">{{ stat.subtext }}</p>
          
          <div v-if="stat.roles" class="flex flex-wrap gap-1 mt-1.5">
            <span v-for="role in stat.roles" :key="role.name"
              :class="['px-1.5 py-0.5 text-[8px] rounded-md border font-bold uppercase tracking-wider', role.class]">
              {{ role.name }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:border-slate-300">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h4 class="font-bold text-slate-700">ទិន្នន័យសិស្សានុសិស្សតាមថ្នាក់</h4>
            <p class="text-[11px] text-slate-400">បែងចែកតាមភេទ និងប្រភេទថ្នាក់</p>
          </div>
          <button class="p-2 hover:bg-slate-50 rounded-lg text-slate-400 transition-colors">
            <MoreHorizontal class="w-5 h-5" />
          </button>
        </div>
        <div class="h-80">
          <Bar v-if="loaded" :data="barChartData" :options="barChartOptions" />
          <div v-else class="h-full flex items-center justify-center text-slate-400 text-sm italic">
            កំពុងទាញទិន្នន័យ...
          </div>
        </div>
      </div>
<!-- 
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col transition-all hover:border-slate-300">
        <div class="mb-6">
          <h4 class="font-bold text-slate-700">អវត្តមានគ្រូ</h4>
          <p class="text-[11px] text-slate-400">ស្ថិតិវត្តមានប្រចាំខែ</p>
        </div>
        <div class="flex-1 flex items-center justify-center relative min-h-[220px]">
          <div class="w-full max-w-[200px]">
            <Pie v-if="loaded" :data="pieChartData" :options="pieChartOptions" />
          </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-3">
          <div v-for="item in attendanceLegend" :key="item.label" class="flex items-center gap-2">
            <span :class="`w-2.5 h-2.5 rounded-full ${item.color}`"></span>
            <span class="text-[11px] text-slate-600">{{ item.label }}</span>
          </div>
        </div>
      </div> -->

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { MoreHorizontal, Users, UserCheck, GraduationCap, School } from 'lucide-vue-next'
import api from '../../services/authService'
import { Bar, Pie } from 'vue-chartjs'
import {
  Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale,
  LinearScale, ArcElement
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

const loaded = ref(false)
const academicYears = ref([])
const selectedYearId = ref('')

const statsCards = reactive([
  { 
    title: 'សិស្សសរុប', value: '0', subtext: 'ប្រុស 0 | ស្រី 0', 
    icon: GraduationCap, bgClass: 'bg-blue-50', iconClass: 'text-blue-600' 
  },
  { 
    title: 'គ្រូសរុប', value: '0', subtext: 'ប្រុស 0 | ស្រី 0', 
    icon: UserCheck, bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' 
  },
  { 
    title: 'អ្នកប្រើប្រាស់', value: '0', subtext: 'គណនីក្នុងប្រព័ន្ធ',
    icon: Users, bgClass: 'bg-indigo-50', iconClass: 'text-indigo-600', // 💡 កែពី bg-indigo-40
    roles: [
      { name: 'admin', class: 'bg-blue-50 text-blue-500 border-blue-100' },
      { name: 'teacher', class: 'bg-purple-50 text-purple-500 border-purple-100' },
      { name: 'student', class: 'bg-amber-50 text-amber-600 border-amber-100' }
    ]
  },
  { 
    title: 'ថ្នាក់សរុប', value: '0', subtext: 'ថ្នាក់សិក្សាទាំងអស់', 
    icon: School, bgClass: 'bg-orange-50', iconClass: 'text-orange-600' 
  },
])

const attendanceLegend = [
  { label: 'វត្តមាន', color: 'bg-blue-500' },
  { label: 'ច្បាប់', color: 'bg-emerald-400' },
  { label: 'យឺត', color: 'bg-amber-400' },
  { label: 'អវត្តមាន', color: 'bg-rose-500' }
]

const barChartData = reactive({ labels: [], datasets: [] })
const pieChartData = reactive({ labels: ['វត្តមាន', 'ច្បាប់', 'មកយឺត', 'អវត្តមាន'], datasets: [] })

const fetchYears = async () => {
  try {
    const response = await api.get('/years')
    academicYears.value = response.data?.data || response.data
    const active = academicYears.value.find(y => y.is_active)
    if (active) {
      selectedYearId.value = active.id
    } else if (academicYears.value.length > 0) {
      selectedYearId.value = academicYears.value[0].id
    }
  } catch (error) {
    console.error("Error loading years:", error)
  }
}

const fetchDashboardData = async () => {
  loaded.value = false
  try {
    const response = await api.get('/dashboard-stats?year_id=${selectedYearId.value}')
    const result = response.data
    const data = result.data || result // Support ទាំងមាន .data ឬអត់

    statsCards[0].value = String(data.total_students || 0)
    statsCards[0].subtext = `ប្រុស ${data.students_male || 0} | ស្រី ${data.students_female || 0}`
    statsCards[1].value = String(data.total_teachers || 0)
    statsCards[1].subtext = `ប្រុស ${data.teachers_male || 0} | ស្រី ${data.teachers_female || 0}`
    statsCards[2].value = String(data.total_users || 0)
    statsCards[3].value = String(data.total_classes || 0)

    barChartData.labels = data.chart_classes_labels || []
    barChartData.datasets = [
      { label: 'សិស្សប្រុស', backgroundColor: '#3b82f6', borderRadius: 6, data: data.chart_students_male || [] },
      { label: 'សិស្សស្រី', backgroundColor: '#34d399', borderRadius: 6, data: data.chart_students_female || [] }
    ]

    pieChartData.datasets = [{
      backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
      borderWidth: 0,
      data: data.attendance_stats || [0, 0, 0, 0]
    }]

    loaded.value = true
  } catch (error) {
    console.error("Error loading dashboard stats:", error)
  }
}

const handleYearChange = () => fetchDashboardData()

onMounted(async () => {
  await fetchYears()
  await fetchDashboardData()
})

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { font: { family: 'Battambang', size: 10 }, usePointStyle: true } }
  },
  scales: {
    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
    x: { grid: { display: false }, ticks: { font: { family: 'Battambang', size: 10 } } }
  }
}

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { bodyFont: { family: 'Battambang' } }
  }
}
</script>