<template>
  <div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-bold text-slate-700 mb-1">ថ្នាក់</label>
        <select v-model="reportSelectedClass" @change="resetReportExamSelection" class="w-full p-2.5 border border-slate-300 rounded-xl outline-none focus:border-indigo-500">
          <option value="">-- ជ្រើសរើសថ្នាក់ --</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{cls.grade_level}}{{ cls.name }}</option>
        </select>
      </div>
      
      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-bold text-slate-700 mb-1">ប្រភេទប្រឡង</label>
        <select v-model="reportSelectedType" @change="resetReportExamSelection" class="w-full p-2.5 border border-slate-300 rounded-xl outline-none focus:border-indigo-500">
          <option value="">-- ជ្រើសរើសប្រភេទ --</option>
          <option v-for="type in EXAM_TYPES" :key="type" :value="type">{{ type }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-bold text-slate-700 mb-1">ការប្រឡង</label>
        <select v-model="reportSelectedExam" class="w-full p-2.5 border border-slate-300 rounded-xl outline-none focus:border-indigo-500">
          <option value="">-- ជ្រើសរើសការប្រឡង --</option>
          <option v-for="exam in examsForReport" :key="exam.id" :value="exam.id">{{ exam.name }}</option>
        </select>
      </div>

      <button 
        @click="fetchExamResults" 
        :disabled="!reportSelectedExam || isLoadingResults"
        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 disabled:opacity-50"
      >
        {{ isLoadingResults ? 'កំពុងផ្ទុក...' : 'បង្ហាញលទ្ធផល' }}
      </button>
    </div>

    <div v-if="examResults.length > 0" class="overflow-x-auto bg-white rounded-2xl border border-slate-200 shadow-sm">
      <table class="w-full text-left border-collapse min-w-[1000px]">
        <thead class="bg-slate-100">
          <tr>
            <th class="p-3 text-[15px] font-bold text-slate-600 text-center">ល.រ</th>
            <th class="p-3 text-[15px] font-bold text-slate-600">ឈ្មោះសិស្ស</th>
            <th v-for="sub in subjects" :key="sub.id" class="p-3 text-[15px] font-bold text-slate-600 text-center">{{ sub.name }}</th>
            <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">សរុប</th>
            <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">មធ្យមភាគ</th>
            <th class="p-3 text-[15px] font-bold text-red-600 text-center">ចំណាត់ថ្នាក់</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="student in rankedResults" :key="student.id" class="hover:bg-slate-50">
            <td class="p-3 text-[15px] text-center text-slate-600">{{ student.rank }}</td>
            <td class="p-3 text-[15px] font-bold text-slate-800">{{ student.name_kh }}</td>
            <td v-for="sub in subjects" :key="sub.id" class="p-3 text-[15px] text-center">
              {{ student.scores[sub.id] || 0 }}
            </td>
            <td class="p-3 text-[15px] font-bold text-indigo-700 text-center">{{ calculateTotal(student) }}</td>
            <td class="p-3 text-[15px] text-center">{{ calculateAverage(student) }}</td>
            <td class="p-3 text-[15px] font-bold text-red-600 text-center">{{ student.rank }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else-if="!isLoadingResults" class="p-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
      <p>មិនទាន់មានទិន្នន័យលទ្ធផល។ សូមជ្រើសរើសថ្នាក់ និងការប្រឡងរួចចុច "បង្ហាញលទ្ធផល"</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Plus, Search, ClipboardCheck, CheckCircle2, Clock, Edit3, Trash2, XCircle, X, Loader2 } from 'lucide-vue-next'
import api from '@/services/authService'

// --- Constants ---
const EXAM_TYPES = ['ប្រចាំខែ', 'ឆមាស', 'ប្រចាំឆ្នាំ']
const PASS_MARK = 50

// --- Shared State ---
const exams = ref([])
const subjects = ref([])
const classes = ref([])
const academicYearsList = ref([])
const isLoading = ref(false)
const currentTab = ref('list')

// --- Helper: Toast ---
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

const tabClass = (tab) => [
  'pb-3 font-bold text-base transition-all duration-200 border-b-2 whitespace-nowrap',
  currentTab.value === tab ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-indigo-500'
]

// --- Calculations (Shared across components) ---
const calculateTotal = (student) => Object.values(student.scores || {}).reduce((acc, curr) => acc + (Number(curr) || 0), 0)

const calculateAverage = (student) => {
  if (subjects.value.length === 0) return 0
  return (calculateTotal(student) / subjects.value.length).toFixed(2)
}

// --- TAB 2 & 3 State ---
const selectedClass = ref('')
const inputSelectedType = ref('')
const selectedExam = ref('')
const students = ref([])
const isLoadingStudents = ref(false)
const isSaving = ref(false)

const reportSelectedClass = ref('')
const reportSelectedType = ref('')
const reportSelectedExam = ref('')
const examResults = ref([])
const isLoadingResults = ref(false)

const examsByInputType = computed(() => exams.value.filter(e =>
  e.type === inputSelectedType.value && (!selectedClass.value || e.class_id === selectedClass.value || !e.class_id)
))

const examsForReport = computed(() => exams.value.filter(e =>
  e.type === reportSelectedType.value && (!reportSelectedClass.value || e.class_id === reportSelectedClass.value || !e.class_id)
))

const rankedResults = computed(() => {
  const sorted = [...examResults.value].sort((a, b) => calculateTotal(b) - calculateTotal(a))
  let rank = 0, prevTotal = null, position = 0
  return sorted.map(student => {
    position += 1
    const total = calculateTotal(student)
    if (total !== prevTotal) { rank = position; prevTotal = total }
    return { ...student, rank }
  })
})

// --- API Methods ---
const fetchExamResults = async () => {
  if (!reportSelectedExam.value || !reportSelectedClass.value) return
  isLoadingResults.value = true
  try {
    const res = await api.get('/scores', { params: { exam_id: reportSelectedExam.value, class_id: reportSelectedClass.value } })
    const list = res.data.data || res.data || []
    examResults.value = list.map(row => ({ id: row.student_id, name_kh: row.student?.name_kh || '', gender: row.student?.gender || '', scores: row.scores || row.subject_scores || {} }))
  } catch (err) {
    showToast('មានបញ្ហាក្នុងការទាញយកលទ្ធផលប្រឡង', 'error')
  } finally {
    isLoadingResults.value = false
  }
}

const fetchData = async () => {
  isLoading.value = true
  try {
    const [examRes, yearRes, classRes, subjectRes] = await Promise.all([
      api.get('/exams'), api.get('/years'), api.get('/classes'), api.get('/subjects')
    ])
    exams.value = Array.isArray(examRes.data) ? examRes.data : (examRes.data.data || [])
    academicYearsList.value = Array.isArray(yearRes.data) ? yearRes.data : (yearRes.data.data || [])
    classes.value = Array.isArray(classRes.data) ? classRes.data : (classRes.data.data || [])
    subjects.value = Array.isArray(subjectRes.data) ? subjectRes.data : (subjectRes.data.data || [])
  } catch (err) {
    showToast('មានបញ្ហាក្នុងការទាញយកទិន្នន័យ', 'error')
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchData)
</script>

<style>
/* Your existing styles... */
</style>