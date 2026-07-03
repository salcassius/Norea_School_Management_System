<template>
  <div class="space-y-6 font-[Battambang] pb-10 bg-slate-50/50 min-h-screen p-4 md:p-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
      <div>
        <h2 class="text-2xl font-bold text-slate-800 font-[Khmer-[battambang]">ផ្ទាំងគ្រប់គ្រង</h2>
        <p class="text-slate-500 text-sm mt-1">ព័ត៌មានសង្ខេបនៃសាលារៀន និងស្ថិតិសំខាន់ៗ</p>
      </div>

      <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm w-full md:w-64">
        <label class="block text-[14px] font-bold text-slate-700 uppercase tracking-wider mb-1">ឆ្នាំសិក្សា</label>
        <select v-model="selectedYearId" @change="fetchDashboardData"
          class="w-full bg-slate-50 border-none text-slate-700 text-sm rounded-lg outline-none cursor-pointer font-bold">
          <option :value="0">គ្រប់ឆ្នាំសិក្សាទាំងអស់</option>

          <option v-for="year in academicYears" :key="year.id" :value="year.id">
            {{ year.year_name }} {{ year.is_active ? ' (បច្ចុប្បន្ន)' : '' }}
          </option>
        </select>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="(stat, index) in statsCards" :key="index" @click="activeStatIndex = index" :class="[
        'p-5 rounded-2xl border transition-all cursor-pointer shadow-sm flex items-center gap-4',
        activeStatIndex === index
          ? 'bg-white border-blue-500 ring-4 ring-blue-50'
          : 'bg-white border-slate-300 hover:border-blue-400'
      ]">
        <div :class="['p-3 rounded-xl', stat.bgClass]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconClass]" />
        </div>

        <div class="overflow-hidden">
          <p class="text-[14px] text-slate-700 font-medium">{{ stat.title }}</p>
          <p class="text-xl font-bold text-slate-800">{{ stat.value }}</p>

          <!-- Users card: role badges with counts -->
          <div v-if="stat.roles" class="flex gap-1.5 mt-1 flex-wrap">
            <span v-for="role in stat.roles" :key="role.name"
              :class="['px-2 py-0.5 rounded-md text-[11px] font-bold border', role.class]">
              {{ role.name }} {{ role.count }}
            </span>
          </div>
          <p v-else-if="stat.subtext" class="text-[12px] text-slate-600 truncate">{{ stat.subtext }}</p>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-6">

      <!-- Doughnut Chart: Student Gender -->
      <div class="lg:col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/20">
        <div class="flex justify-between items-center mb-6">
          <h4 class="font-bold text-slate-800 text-[16px]">ស្ថិតិសិស្ស</h4>
        </div>

        <div class="h-48 relative flex items-center justify-center">
          <Doughnut v-if="loaded" :data="pieChartData" :options="pieChartOptions" />
          <div class="absolute text-2xl font-black text-slate-800">{{ statsCards[0].value }}</div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6">
          <div class="bg-emerald-50/50 p-3 rounded-2xl text-center border border-emerald-100">
            <div class="text-lg font-black text-emerald-600">{{ pieChartData.datasets[0]?.data[0] || 0 }}</div>
            <div class="text-[11px] text-emerald-700">សិស្សប្រុស</div>
          </div>
          <div class="bg-amber-50/50 p-3 rounded-2xl text-center border border-amber-100">
            <div class="text-lg font-black text-amber-600">{{ pieChartData.datasets[0]?.data[1] || 0 }}</div>
            <div class="text-[11px] text-amber-700">សិស្សស្រី</div>
          </div>
        </div>
      </div>

      <!-- Bar Chart: Attendance by Class -->
      <div class="lg:col-span-3 bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/20">
        <div class="flex justify-between items-start mb-8">
          <div>
            <h4 class="font-bold text-slate-800 text-[16px]">វត្តមានសិស្សតាមថ្នាក់</h4>
            <p class="text-[14px] text-slate-500">ប្រៀបធៀបសិស្សមកវត្តមាន និងអវត្តមាន</p>
          </div>

          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-[#10b981]"></div>
              <span class="text-xs text-slate-500">វត្តមាន</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-[#ef4444]"></div>
              <span class="text-xs text-slate-500">អវត្តមាន</span>
            </div>

            <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-xl border border-slate-200">
              <input type="date" v-model="selectedDate" @change="fetchDashboardData"
                class="bg-transparent text-sm text-slate-700 outline-none font-bold cursor-pointer" />
            </div>
          </div>
        </div>

        <div class="h-80 w-full">
          <Bar v-if="loaded" :data="barChartData" :options="barChartOptions" />
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { Users, UserCheck, GraduationCap, School } from 'lucide-vue-next'
import api from '../../services/authService'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

// ─── State ────────────────────────────────────────────────────────────────────

const selectedDate = ref(new Date().toISOString().split('T')[0])
const academicYears = ref([])
const selectedYearId = ref('')
const loaded = ref(false)
const activeStatIndex = ref(-1)

const statsCards = reactive([
  {
    title: 'សិស្សសរុប',
    value: '0',
    subtext: 'ប្រុស 0 | ស្រី 0',
    icon: GraduationCap,
    bgClass: 'bg-blue-50',
    iconClass: 'text-blue-600'
  },
  {
    title: 'គ្រូសរុប',
    value: '0',
    subtext: 'ប្រុស 0 | ស្រី 0',
    icon: UserCheck,
    bgClass: 'bg-emerald-50',
    iconClass: 'text-emerald-600'
  },
  {
    title: 'អ្នកប្រើប្រាស់',
    value: '0',
    subtext: 'គណនីក្នុងប្រព័ន្ធ',
    icon: Users,
    bgClass: 'bg-indigo-50',
    iconClass: 'text-indigo-600',
    roles: [
      { name: 'admin', class: 'bg-blue-50 text-blue-500 border border-blue-100' },
      { name: 'teacher', class: 'bg-purple-50 text-purple-500 border border-purple-100' },
      { name: 'student', class: 'bg-amber-50 text-amber-600 border border-amber-100' }
    ]
  },
  {
    title: 'ថ្នាក់សរុប',
    value: '0',
    icon: School,
    bgClass: 'bg-orange-50',
    iconClass: 'text-orange-600'
  }
])

const barChartData = reactive({ labels: [], datasets: [] })
const pieChartData = reactive({
  labels: ['សិស្សប្រុស', 'សិស្សស្រី'],
  datasets: []
})

// ─── Chart Options ────────────────────────────────────────────────────────────

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#f1f5f9' },
      ticks: { stepSize: 2 }
    },
    x: { grid: { display: false } }
  }
}

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '80%',
  plugins: { legend: { display: false } }
}

// ─── Data Fetching ────────────────────────────────────────────────────────────

const fetchDashboardData = async () => {
  loaded.value = false

  try {
    const response = await api.get('/dashboard-stats', {
      params: {
        year_id: selectedYearId.value === 0 ? null : selectedYearId.value,
        date: selectedDate.value
      }
    })

    const data = response.data.data || response.data

    // Students
    statsCards[0].value = String(data.total_students || 0)
    statsCards[0].subtext = `ប្រុស ${data.students_male || 0} | ស្រី ${data.students_female || 0}`

    // Teachers
    statsCards[1].value = String(data.total_teachers || 0)
    statsCards[1].subtext = `ប្រុស ${data.teachers_male || 0} | ស្រី ${data.teachers_female || 0}`

    // Users
    statsCards[2].value = String(data.total_users || 0)
    statsCards[2].roles[0].count = data.users_admin
    statsCards[2].roles[1].count = data.users_teacher
    statsCards[2].roles[2].count = data.users_student

    // Classes
    statsCards[3].value = String(data.total_classes || 0)

    // Doughnut chart
    pieChartData.datasets = [
      {
        backgroundColor: ['#3b82f6', '#fbbf24'],
        data: [data.students_male || 0, data.students_female || 0]
      }
    ]

    // Bar chart
    barChartData.labels = data.chart_classes_labels || []
    barChartData.datasets = [
      {
        label: 'វត្តមាន',
        backgroundColor: '#10b981',
        borderRadius: 6,
        barThickness: 16,
        data: data.chart_present_counts || []
      },
      {
        label: 'អវត្តមាន',
        backgroundColor: '#ef4444',
        borderRadius: 6,
        barThickness: 16,
        data: data.chart_absent_counts || []
      }
    ]

    loaded.value = true

  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    loaded.value = true
  }
}
// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(async () => {
  try {
    const res = await api.get('/years')
    academicYears.value = res.data
    selectedYearId.value =
      academicYears.value.find(y => y.is_active)?.id || academicYears.value[0]?.id
    await fetchDashboardData()
  } catch (error) {
    console.error('Error initializing dashboard:', error)
  }
})
</script>