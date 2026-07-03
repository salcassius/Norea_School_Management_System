<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">
    <div class="bg-white p-5 rounded-2xl border border-slate-300/80 shadow-sm flex flex-wrap items-end gap-4 mb-6">
      <div class="w-full sm:w-[220px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ថ្នាក់រៀន</label>
        <select v-model="filter.class_id" @change="fetchReport" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500 cursor-pointer">
          <option value="" disabled>--- ជ្រើសរើស ---</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">ថ្នាក់ទី៖ {{ cls.grade_level }} {{ cls.name }}</option>
        </select>
      </div>

      <div class="relative w-full sm:w-[180px]" v-click-outside="() => isMonthOpen = false">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ខែ</label>
        <button
          type="button"
          @click="isMonthOpen = !isMonthOpen"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500 cursor-pointer flex justify-between items-center"
        >
          <span>{{ selectedMonthLabel }}</span>
          <ChevronDown class="w-4 h-4 text-slate-400" />
        </button>

        <div
          v-if="isMonthOpen"
          class="absolute top-full left-0 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-60 overflow-y-auto"
        >
          <button
            v-for="m in khmerMonths"
            :key="m.value"
            type="button"
            @click="selectMonth(m.value)"
            :class="['w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition-colors', selectedMonth === m.value ? 'bg-indigo-600 text-white hover:bg-indigo-600' : 'text-slate-700']"
          >
            {{ m.label }}
          </button>
        </div>
      </div>

      <div class="w-full sm:w-[220px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ឆ្នាំសិក្សា</label>
        <select v-model="filter.year_id" @change="fetchReport" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500 cursor-pointer">
          <option value="" disabled>--- ជ្រើសរើស ---</option>
          <option v-for="yr in academicYears" :key="yr.id" :value="yr.id">{{ yr.year_name }}</option>
        </select>
      </div>
    </div>

    <div v-if="isLoading" class="p-12 text-center text-slate-500">កំពុងទាញយកទិន្នន័យ...</div>

    <div v-else-if="reportData.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-300/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 font-bold text-slate-800">បញ្ជីវត្តមានសិស្ស</div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 text-slate-500 text-[14px] uppercase">
              <tr>
                <th class="px-6 py-4">ឈ្មោះសិស្ស</th>
                <th class="px-6 py-4 text-center">វត្តមាន</th>
                <th class="px-6 py-4 text-center">អវត្តមាន</th>
                <th class="px-6 py-4 text-center">ច្បាប់</th>
                <th class="px-6 py-4 text-center">ភាគរយ</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="student in reportData" :key="student.student_id" class="hover:bg-slate-50/50">
                <td class="px-6 py-4 font-semibold text-slate-700">{{ student.student_name }}</td>
                <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ student.total_present }}</td>
                <td class="px-6 py-4 text-center font-bold text-rose-600">{{ student.total_absent }}</td>
                <td class="px-6 py-4 text-center font-bold text-blue-600">{{ student.total_excused || 0 }}</td>
                <td class="px-6 py-4 text-center">
                  <span :class="['px-3 py-1 rounded-full text-[11px] font-bold', student.percentage >= 90 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600']">
                    {{ student.percentage }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="bg-white p-5 rounded-2xl border border-slate-300/80 shadow-sm h-fit">
        <h3 class="font-bold text-slate-800 mb-4">សិស្សអវត្តមានច្រើនជាងគេ</h3>
        <div class="space-y-4">
          <div v-for="top in topAbsentees" :key="top.student_id" class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
            <span class="text-sm font-medium">{{ top.student_name }}</span>
            <span class="text-xs font-bold text-rose-600 bg-rose-100 px-2 py-1 rounded-lg">{{ top.total_absent }} ថ្ងៃ</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="p-12 text-center bg-white rounded-2xl border border-dashed text-slate-400">
      មិនមានទិន្នន័យសម្រាប់បង្ហាញទេ
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import api from '../../../services/authService'

const classes = ref([])
const academicYears = ref([])
const reportData = ref([])
const topAbsentees = ref([])
const isLoading = ref(false)
const isMonthOpen = ref(false)

const khmerMonths = [
  { value: '01', label: 'មករា' }, { value: '02', label: 'កុម្ភៈ' }, { value: '03', label: 'មីនា' },
  { value: '04', label: 'មេសា' }, { value: '05', label: 'ឧសភា' }, { value: '06', label: 'មិថុនា' },
  { value: '07', label: 'កក្កដា' }, { value: '08', label: 'សីហា' }, { value: '09', label: 'កញ្ញា' },
  { value: '10', label: 'តុលា' }, { value: '11', label: 'វិច្ឆិកា' }, { value: '12', label: 'ធ្នូ' }
]

const now = new Date()
const selectedMonth = ref(String(now.getMonth() + 1).padStart(2, '0'))
const selectedYear = ref(now.getFullYear())

const filter = ref({
  class_id: '',
  year_id: '',
  month: `${selectedYear.value}-${selectedMonth.value}`
})

const selectedMonthLabel = computed(() => khmerMonths.find(m => m.value === selectedMonth.value)?.label || '')

const selectMonth = (value) => {
  selectedMonth.value = value
  isMonthOpen.value = false
  filter.value.month = `${selectedYear.value}-${selectedMonth.value}`
  fetchReport()
}

const fetchReport = async () => {
  if (!filter.value.class_id || !filter.value.year_id) return
  isLoading.value = true
  try {
    const res = await api.get('/attendance/report', { params: filter.value })
    reportData.value = res.data.data || []
    topAbsentees.value = res.data.top_absentees || []
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  try {
    const [classRes, yearRes] = await Promise.all([api.get('/classes'), api.get('/years')])
    classes.value = classRes.data?.data || classRes.data
    academicYears.value = yearRes.data?.data || yearRes.data
    
    const active = academicYears.value.find(y => y.is_active)
    if (active) filter.value.year_id = active.id
  } catch (err) {
    console.error('Initialization error:', err)
  }
})

const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => { if (!(el == event.target || el.contains(event.target))) binding.value() }
    document.body.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) { document.body.removeEventListener('click', el.clickOutsideEvent) }
}
</script>