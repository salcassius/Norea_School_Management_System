<template>
  <div class="font-[Battambang] p-1 sm:p-2 md:p-2 min-h-screen bg-slate-50/50 text-slate-700">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-[21px] font-bold text-slate-800">
        បញ្ចូលពិន្ទុការប្រឡង 
        <span class="text-[15px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 ml-2">
          សម្រាប់គ្រូបន្ទប់ថ្នាក់
        </span>
      </h1>
      <p class="text-sm text-slate-500 mt-1">សូមជ្រើសរើសថ្នាក់ និងការប្រឡង ដើម្បីចាប់ផ្តើមស្រង់ និងបញ្ជូលពិន្ទុសិស្ស</p>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ថ្នាក់រៀន</label>
        <select v-model="selectedClass" @change="resetExamSelection"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-[14px] outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
          <option value="">ជ្រើសរើសថ្នាក់</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.grade_level }}{{ cls.name }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ប្រភេទការប្រឡង</label>
        <select v-model="inputSelectedType" @change="resetExamSelection"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
          <option value="">ជ្រើសរើសប្រភេទ</option>
          <option v-for="type in EXAM_TYPES" :key="type" :value="type">{{ type }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ឈ្មោះការប្រឡង</label>
        <select v-model="selectedExam" :disabled="!inputSelectedType"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 disabled:opacity-60 cursor-pointer">
          <option value="">ជ្រើសរើសការប្រឡង</option>
          <option v-for="exam in examsByInputType" :key="exam.id" :value="exam.id">{{ exam.name }}</option>
        </select>
      </div>

      <button @click="fetchStudentsForScoring" :disabled="isLoadingStudents || !selectedExam || !selectedClass"
        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 active:scale-[0.98] transition-all h-[42px] disabled:opacity-50 disabled:active:scale-100 shadow-sm shadow-indigo-200 cursor-pointer">
        {{ isLoadingStudents ? 'កំពុងផ្ទុក...' : 'បង្ហាញសិស្ស' }}
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="loadError" class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold">
      {{ loadError }}
    </div>

    <!-- Table Container -->
    <div v-if="students.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-auto max-h-[65vh]">
        <table class="w-full text-left border-collapse min-w-[1200px]">
          <thead class="bg-slate-100 sticky top-0 z-10">
            <tr>
              <th class="p-3 text-[15px] font-bold text-slate-600 text-center w-12">ល.រ</th>
              <th class="p-3 text-[15px] font-bold text-slate-600 min-w-[140px]">ឈ្មោះសិស្ស</th>
              <th class="p-3 text-[15px] font-bold text-slate-600 text-center">ភេទ</th>
              <th v-for="sub in subjects" :key="sub.id" class="p-3 text-[15px] font-bold text-slate-600 text-center whitespace-nowrap min-w-[90px]">
                {{ sub.name }}
              </th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">សរុប</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">មធ្យមភាគ</th>
              <th class="p-3 text-[15px] font-bold text-red-600 text-center min-w-[80px]">ចំ.ថ្នាក់</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">និទ្ទេសខ្មែរ</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">និទ្ទេស</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">លទ្ធផល</th>
            </tr>
          </thead>
          <!--
            ✅ FIX: use `students` (stable, original fetch order) instead of
            `sortedStudents` here. sortedStudents re-sorts by score total on
            every keystroke, which made rows jump around while typing. The
            table now keeps a fixed row order; the "ចំ.ថ្នាក់" (rank) column
            still shows the correct rank via studentRankMap, computed
            separately from the sorted list.
          -->
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-slate-50 transition-colors">
              <td class="p-3 text-[15px] text-slate-600 text-center">{{ index + 1 }}</td>
              <td class="p-3 text-[15px] font-bold text-slate-800 whitespace-nowrap">{{ student.name_kh }}</td>
              <td class="p-3 text-[15px] text-center">{{ getGenderKh(student.gender) }}</td>
              
              <!-- Score Inputs -->
              <td v-for="sub in subjects" :key="sub.id" class="p-2 text-center">
                <input type="number" step="any" v-model.number="student.scores[sub.id]"
                  :min="0" :max="getSubjectMaxScore(sub.name)"
                  :title="`អតិបរមា ${getSubjectMaxScore(sub.name)}`"
                  @input="handleScoreInput(student, sub.id, sub.name, $event.target.value)"
                  class="w-16 mx-auto block border border-slate-300 rounded-lg py-1 text-center text-sm font-bold text-indigo-900 transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                  placeholder="-" />
              </td>

              <!-- Live Calculations -->
              <td class="p-3 text-[15px] font-bold text-indigo-700 text-center bg-indigo-50/30">{{ calculateTotal(student) }}</td>
              <td class="p-3 text-[15px] font-semibold text-center">{{ calculateAverage(student) }}</td>
              <td class="p-3 text-[15px] font-bold text-red-600 text-center">{{ studentRankMap[student.id] }}</td>
              <td class="p-3 text-[15px] text-center font-bold text-emerald-600">{{ getGradeKh(calculateAverage(student)) }}</td>
              <td class="p-3 text-[15px] text-center font-bold text-blue-600">{{ getGradeEn(calculateAverage(student)) }}</td>
              <td class="p-3 text-[15px] text-center font-bold" :class="getResult(student).color">{{ getResult(student).text }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Sticky Action Bar at Bottom -->
      <div class="sticky bottom-0 z-20 p-4 border-t border-slate-200 bg-slate-50 flex flex-wrap justify-end gap-3 shadow-[0_-2px_6px_rgba(0,0,0,0.05)]">
        <button @click="exportExcel" class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-green-50 active:scale-[0.98] transition-all cursor-pointer">
          នាំចេញ Excel
        </button>
        <button @click="exportPDF" class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-red-50 active:scale-[0.98] transition-all cursor-pointer">
          នាំចេញ PDF
        </button>
        <button @click="saveScores" :disabled="isSaving"
          class="bg-emerald-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-emerald-700 active:scale-[0.98] transition-all disabled:opacity-50 disabled:active:scale-100 cursor-pointer shadow-md shadow-emerald-200 flex items-center gap-2">
          <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin" />
          <span>{{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកពិន្ទុ' }}</span>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="selectedExam && !isLoadingStudents" class="p-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
      សូមចុច "បង្ហាញសិស្ស" ដើម្បីចាប់ផ្តើមស្រង់ និងបញ្ជូលពិន្ទុ
    </div>

    <!-- Summary Statistics below table -->
    <div v-if="students.length > 0" class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex flex-wrap items-center gap-2 pb-4 mb-4 border-b border-slate-100">
        <span class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-sm font-bold">
          ចំនួនសិស្សសរុប {{ sortedStudents.length }} នាក់
        </span>
        <span class="px-3 py-1.5 rounded-full bg-pink-50 text-pink-700 text-sm font-bold">
          ស្រី {{ totalFemaleCount }} នាក់
        </span>
        <span class="px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-sm font-bold">
          ជាប់ {{ countPass }} នាក់
        </span>
        <span class="px-3 py-1.5 rounded-full bg-rose-50 text-rose-700 text-sm font-bold">
          ធ្លាក់ {{ countFail }} នាក់
        </span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div v-for="row in gradeBreakdown" :key="row.gradeEn"
          class="flex items-center justify-flex gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-100">
          <span class="text-sm font-bold text-slate-700">{{ row.gradeEn }} ({{ row.gradeKh }})</span>
          <span class="text-[14px] text-slate-600 whitespace-nowrap">{{ row.total }} នាក់ (ស្រី {{ row.female }})</span>
        </div>
      </div>
    </div>

    <div class="mt-10 text-right leading-8 justify-end">
      <div class="flex flex-col items-end">
        <div class="text-center">
          <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
          <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
          <p>គ្រូបន្ទប់ថ្នាក់</p>
          <br>
          <p class="mt-12 font-bold text-right mr-12">{{ selectedClassTeacherName }}</p>
        </div>
      </div>
      <div class="flex justify-between">
        <div class="text-center font-moul">
          <p>បានឃើញ និងឯកភាព</p>
          <p>នាយកសាលា</p>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.show" :class="[
          'fixed top-5 right-5 z-[100] flex items-center gap-3 px-4 py-3 rounded-2xl border shadow-lg min-w-[260px] max-w-xs font-[Battambang]',
          toast.type === 'success' ? 'bg-white border-emerald-200 text-emerald-700' : 'bg-white border-rose-200 text-rose-600'
        ]">
          <div :class="['w-8 h-8 rounded-full flex items-center justify-center shrink-0', toast.type === 'success' ? 'bg-emerald-50' : 'bg-rose-50']">
            <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-500" />
            <XCircle v-else class="w-4 h-4 text-rose-500" />
          </div>
          <p class="text-sm font-medium flex-1">{{ toast.message }}</p>
          <button @click="toast.show = false" class="text-slate-300 hover:text-slate-500 transition-colors">
            <X class="w-4 h-4" />
          </button>
        </div>
      </Transition>
    </Teleport>

    <!-- ========================================================================= -->
    <!-- Hidden Official Letterhead Export Area (For Landscape PDF Export) -->
    <!-- ========================================================================= -->
    <div ref="printArea" class="pdf-export-area">
      <div class="doc-header">
        <p class="kingdom-title">ព្រះរាជាណាចក្រកម្ពុជា</p>
        <p class="kingdom-motto">ជាតិ សាសនា ព្រះមហាក្សត្រ</p>
        <div class="ornament">
          <span class="ornament-line"></span>
          <span class="ornament-icon">❖</span>
          <span class="ornament-icon">❖</span>
          <span class="ornament-icon">❖</span>
          <span class="ornament-line"></span>
        </div>
      </div>

      <div class="doc-office">
        <p>{{ schoolName }}</p>
        <p>ថ្នាក់ទី{{ selectedClassLabel }}</p>
      </div>

      <h2 class="doc-title">
        តារាងស្រង់ពិន្ទុ<span class="latin-text">{{ selectedExamLabel }}</span>
      </h2>

      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width:4%">ល.រ</th>
            <th style="width:14%">គោត្តនាម-នាម</th>
            <th style="width:5%">ភេទ</th>
            <th v-for="sub in subjects" :key="'h-' + sub.id">{{ sub.name }}</th>
            <th style="width:7%">សរុប</th>
            <th style="width:8%">មធ្យមភាគ</th>
            <th style="width:8%">ចំណាត់ថ្នាក់</th>
            <th style="width:8%">និទ្ទេស</th>
            <th style="width:8%">លទ្ធផល</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, index) in sortedStudents" :key="'pdf-' + student.id">
            <td>{{ index + 1 }}</td>
            <td style="text-align:left; font-weight:bold;">{{ student.name_kh }}</td>
            <td>{{ getGenderKh(student.gender) }}</td>
            <td v-for="sub in subjects" :key="'c-' + sub.id + '-' + student.id">{{ formatNum(student.scores?.[sub.id]) }}</td>
            <td style="font-weight:bold;">{{ calculateTotal(student) }}</td>
            <td>{{ calculateAverage(student) }}</td>
            <td>{{ index + 1 }}</td>
            <td>{{ getGradeKh(calculateAverage(student)) }}</td>
            <td>{{ getResult(student).text }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបចំនួនសិស្ស៖ {{ sortedStudents.length }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ស្រីចំនួន៖ {{ totalFemaleCount }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ជាប់៖ {{ countPass }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ធ្លាក់៖ {{ countFail }} នាក់
        </strong>
      </div>

      <div class="doc-remarks" v-if="gradeBreakdown.length">
        <div class="remark-item" v-for="row in gradeBreakdown" :key="row.gradeEn">
          {{ row.gradeEn }} ({{ row.gradeKh }})៖ {{ row.total }} នាក់
          &nbsp;&nbsp;
          ស្រី {{ row.female }} នាក់
        </div>
      </div>

      <div class="doc-date">
        <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
        <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
        <p class="moul" style="margin-right: 35px;">គ្រូបន្ទប់ថ្នាក់</p>
        <p class="teacher-name">{{ selectedClassTeacherName }}</p>
      </div>
      <div class="doc-footer">
        <div class="signature-left">
          <p class="moul">បានឃើញ និងឯកភាព</p>
          <p class="moul">នាយកសាលា</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Loader2, CheckCircle2, XCircle, X } from 'lucide-vue-next'
import api from '../../services/authService' // ឬកែសម្រួល Path តាម Project ជាក់ស្តែងរបស់អ្នក
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'
// import { _ } from 'vue-router/dist/router-CWoNjPRp.mjs'

defineOptions({ name: 'InputScore' })

// --- State ---
const selectedClass = ref('')
const inputSelectedType = ref('')
const selectedExam = ref('')
const isSaving = ref(false)
const isLoadingStudents = ref(false)
const loadError = ref('')
const printArea = ref(null)

const students = ref([])
const classes = ref([])
const exams = ref([])
const subjects = ref([])

const schoolName = ref('អនុវិទ្យាល័យ នរា')
const EXAM_TYPES = ['ប្រចាំខែ', 'ឆមាស', 'ប្រចាំឆ្នាំ']
const PASS_MARK = 25 // សម្រាប់មធ្យមភាគ ៥០

// --- Toast ---
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// --- Computed ---
const examsByInputType = computed(() => exams.value.filter(e => e.type === inputSelectedType.value))

const selectedClassObj = computed(() => classes.value.find(c => c.id === selectedClass.value))
const selectedClassLabel = computed(() => {
  const cls = selectedClassObj.value
  return cls ? `${cls.grade_level}${cls.name}` : ''
})

const selectedClassTeacherName = computed(() => {
  const cls = selectedClassObj.value
  if (!cls) return ''
  return cls.teacher?.name_kh || cls.teacher?.name || cls.teacher_name || ''
})


const selectedExamLabel = computed(() => {
  const exam = exams.value.find(e => e.id === selectedExam.value)
  return exam ? exam.name : ''
})

const reportTitle = computed(() => {
  const parts = ['តារាងស្រង់ពិន្ទុ']
  if (selectedExamLabel.value) parts.push(selectedExamLabel.value)
  if (selectedClassLabel.value) parts.push(`ថ្នាក់ទី${selectedClassLabel.value}`)
  return parts.join(' ')
})

// ✅ តម្រៀបសម្រាប់ export (Excel/PDF) និងសម្រាប់គណនាចំណាត់ថ្នាក់
const sortedStudents = computed(() => {
  return [...students.value].sort((a, b) => calculateTotal(b) - calculateTotal(a))
})

// ✅ FIX: ចំណាត់ថ្នាក់ (rank) របស់សិស្សម្នាក់ៗ គណនាដោយឡែកពី sortedStudents
// ដើម្បីឲ្យតារាងបញ្ចូលពិន្ទុ (ដែលប្រើ `students` លំដាប់ថេរ) មិនលោត
// ជួរដេកនៅពេលអ្នកប្រើប្រាស់វាយពិន្ទុ ប៉ុន្តែចំណាត់ថ្នាក់នៅតែត្រឹមត្រូវ
const studentRankMap = computed(() => {
  const map = {}
  sortedStudents.value.forEach((s, idx) => {
    map[s.id] = idx + 1
  })
  return map
})

const totalFemaleCount = computed(() => sortedStudents.value.filter(s => getGenderKh(s.gender) === 'ស្រី').length)
const countPass = computed(() => sortedStudents.value.filter(s => Number(calculateAverage(s)) >= PASS_MARK).length)
const countFail = computed(() => sortedStudents.value.filter(s => Number(calculateAverage(s)) < PASS_MARK).length)

const gradeBreakdown = computed(() => {
  const grades = [
    { gradeEn: 'A', gradeKh: 'ល្អប្រសើរ' },
    { gradeEn: 'B', gradeKh: 'ល្អណាស់' },
    { gradeEn: 'C', gradeKh: 'ល្អ' },
    { gradeEn: 'D', gradeKh: 'ល្អបង្គួរ' },
    { gradeEn: 'E', gradeKh: 'មធ្យម' },
    { gradeEn: 'F', gradeKh: 'ធ្លាក់' }
  ]
  const counts = {}
  grades.forEach(({ gradeEn }) => { counts[gradeEn] = { total: 0, female: 0 } })

  sortedStudents.value.forEach(student => {
    const gen = getGradeEn(calculateAverage(student))
    if (!counts[gen]) counts[gen] = { total: 0, female: 0 }
    counts[gen].total += 1
    if (getGenderKh(student.gender) === 'ស្រី') counts[gen].female += 1
  })

  return grades.map(({ gradeEn, gradeKh }) => ({ gradeEn, gradeKh, ...counts[gradeEn] }))
})

// --- Helpers ---
const unwrapArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

const sanitizeFileName = (name) => name.replace(/[\\/:*?"<>|]/g, '-').trim()

const formatNum = (num) => {
  if (num === null || num === undefined || num === '') return ''
  return num
}

const getGenderKh = (gender) => {
  if (!gender) return '-'
  const g = String(gender).toLowerCase()
  if (g === 'm' || g === 'male' || g === 'ប្រុស') return 'ប្រុស'
  if (g === 'f' || g === 'female' || g === 'ស្រី') return 'ស្រី'
  return '-'
}

const resetExamSelection = () => {
  selectedExam.value = ''
  students.value = []
  loadError.value = ''
}

// =========================================================================
// ✅ យន្តការគណនាមធ្យមភាគ (អតិបរមា ៥០, គណិត/ខ្មែរ មេគុណ ២)
// =========================================================================

const getSubjectCoef = (subjectName) => {
  if (!subjectName) return 1
  const name = subjectName.trim()
  if (name.includes('គណិត') || name.includes('ខ្មែរ')) return 2
  return 1
}

const getSubjectMaxScore = (subjectName) => {
  if (!subjectName) return 50
  const name = String(subjectName).trim()
  if (name.includes('គណិត') || name.includes('ខ្មែរ')) return 100
  return 50
}

const clampScore = (value, subjectName) => {
  if (value === null || value === undefined || value === '') return ''

  const num = Number(value)
  if (Number.isNaN(num)) return ''

  const max = getSubjectMaxScore(subjectName)
  return Math.min(max, Math.max(0, num))
}

const handleScoreInput = (student, subjectId, subjectName, value) => {
  student.scores[subjectId] = clampScore(value, subjectName)
}

const sanitizeStudentScores = (student) => {
  const normalized = {}
  Object.entries(student.scores || {}).forEach(([subjectId, value]) => {
    const subject = subjects.value.find(sub => String(sub.id) === String(subjectId))
    normalized[subjectId] = clampScore(value, subject?.name)
  })
  return normalized
}

const totalCoefficient = computed(() => {
  return subjects.value.reduce((total, sub) => total + getSubjectCoef(sub.name), 0)
})

const calculateTotal = (student) => {
  return Object.values(student.scores || {}).reduce((acc, curr) => acc + (Number(curr) || 0), 0)
}

const calculateAverage = (student) => {
  const coef = totalCoefficient.value || 1
  return (calculateTotal(student) / coef).toFixed(2)
}

// Thresholds for Max 50
const getGradeKh = (avg) => {
  avg = Number(avg)
  if (avg >= 45) return 'ល្អប្រសើរ'
  if (avg >= 40) return 'ល្អណាស់'
  if (avg >= 35) return 'ល្អ'
  if (avg >= 30) return 'ល្អបង្គួរ'
  if (avg >= 25) return 'មធ្យម'
  return 'ធ្លាក់'
}

const getGradeEn = (avg) => {
  avg = Number(avg)
  if (avg >= 45) return 'A'
  if (avg >= 40) return 'B'
  if (avg >= 35) return 'C'
  if (avg >= 30) return 'D'
  if (avg >= 25) return 'E'
  return 'F'
}

const getResult = (student) => {
  const avg = Number(calculateAverage(student))
  return avg >= PASS_MARK ? { text: 'ជាប់', color: 'text-emerald-600' } : { text: 'ធ្លាក់', color: 'text-rose-600' }
}

// --- Fetch Data ---
const parseScoresSmartly = (rawScores) => {
  if (!rawScores) return {}
  if (typeof rawScores === 'string') {
    try { return JSON.parse(rawScores) } catch { return {} }
  }
  if (Array.isArray(rawScores)) {
    const obj = {}
    rawScores.forEach(item => { if (item.subject_id !== undefined) obj[item.subject_id] = item.score_value ?? item.score })
    return obj
  }
  return typeof rawScores === 'object' ? rawScores : {}
}

const fetchStudentsForScoring = async () => {
  if (!selectedClass.value || !selectedExam.value) {
    showToast('សូមជ្រើសរើសថ្នាក់ និងឈ្មោះការប្រឡងជាមុនសិន!', 'error')
    return
  }
  isLoadingStudents.value = true
  loadError.value = ''
  try {
    if (subjects.value.length === 0) {
      const sRes = await api.get('/subjects').catch(() => ({ data: [] }))
      subjects.value = unwrapArray(sRes.data)
    }

    let classStudents = []
    try {
      const cRes = await api.get(`/classes/${selectedClass.value}/students`)
      classStudents = unwrapArray(cRes.data)
    } catch (e) {
      try {
        const allStuRes = await api.get('/students')
        classStudents = unwrapArray(allStuRes.data).filter(s => s.class_id == selectedClass.value || s.from_class == selectedClassObj.value?.name)
      } catch (err) {
        console.error("មិនអាចទាញបញ្ជីសិស្សបានឡើយ", err)
      }
    }

    const scoreRes = await api.get('/scores', {
      params: { exam_id: selectedExam.value, class_id: selectedClass.value }
    }).catch(() => ({ data: [] }))
    const scoreRows = unwrapArray(scoreRes.data)

    if (classStudents.length > 0) {
      students.value = classStudents.map(stu => {
        const foundScoreRow = scoreRows.find(sc => Number(sc.student_id) === Number(stu.id) || Number(sc.id) === Number(stu.id))
        const existingScores = parseScoresSmartly(foundScoreRow?.scores)

        const initializedScores = {}
        subjects.value.forEach(sub => {
          const val = existingScores[sub.id] !== undefined ? existingScores[sub.id] : existingScores[String(sub.id)]
          initializedScores[sub.id] = clampScore((val !== undefined && val !== null && val !== '') ? Number(val) : '', sub.name)
        })

        return {
          id: stu.id,
          name_kh: stu.name_kh || stu.name || 'សិស្ស',
          name_en: stu.name_en || '',
          gender: stu.gender || 'male',
          scores: initializedScores
        }
      })
    } else if (scoreRows.length > 0) {
      students.value = scoreRows.map(row => {
        const existingScores = parseScoresSmartly(row.scores)
        const initializedScores = {}
        subjects.value.forEach(sub => {
          const val = existingScores[sub.id] !== undefined ? existingScores[sub.id] : existingScores[String(sub.id)]
          initializedScores[sub.id] = clampScore((val !== undefined && val !== null && val !== '') ? Number(val) : '', sub.name)
        })
        return {
          id: row.student_id || row.id,
          name_kh: row.name_kh || row.student?.name_kh || 'សិស្ស',
          name_en: row.name_en || row.student?.name_en || '',
          gender: row.gender || row.student?.gender || 'male',
          scores: initializedScores
        }
      })
    } else {
      students.value = []
      loadError.value = 'មិនមានទិន្នន័យសិស្សនៅក្នុងថ្នាក់រៀននេះទេ!'
    }
  } catch (err) {
    console.error('fetchStudents error:', err)
    loadError.value = 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យសិស្ស និងពិន្ទុ'
  } finally {
    isLoadingStudents.value = false
  }
}

// --- Save Scores ---
const saveScores = async () => {
  if (students.value.length === 0) return
  isSaving.value = true
  try {
    const payload = {
      exam_id: selectedExam.value,
      class_id: selectedClass.value,
      scores: students.value.map(s => {
        const cleanScores = {}
        Object.keys(s.scores || {}).forEach(subId => {
          const subject = subjects.value.find(sub => String(sub.id) === String(subId))
          const value = s.scores[subId]
          cleanScores[subId] = clampScore((value !== '' && value !== null) ? Number(value) : '', subject?.name)
        })
        return {
          student_id: s.id,
          scores: cleanScores
        }
      })
    }

    await api.post('/scores/save', payload)
    showToast('រក្សាទុកពិន្ទុបានជោគជ័យ!', 'success')
    
    // ✅ ទាញទិន្នន័យថ្មីមក Update លើអេក្រង់វិញភ្លាមៗ
    await fetchStudentsForScoring()
  } catch (err) {
    console.error('Error saving scores:', err)
    showToast('មានបញ្ហាក្នុងការរក្សាទុកពិន្ទុ សូមពិនិត្យមើលសិទ្ធិ ឬ API!', 'error')
  } finally {
    isSaving.value = false
  }
}

// ----- Styles for Excel -----
const THIN_BORDER = { style: 'thin', color: { rgb: '000000' } }
const ALL_BORDERS = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER }

const kingdomStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 15 }, alignment: { horizontal: 'center', vertical: 'center' } }
const mottoStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 }, alignment: { horizontal: 'center', vertical: 'center' } }
const officeStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 }, alignment: { horizontal: 'left', vertical: 'center' } }
const titleStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 14 }, alignment: { horizontal: 'center', vertical: 'center' } }
const tableHeaderStyle = { font: { name: 'Khmer OS Battambang', bold: true, sz: 11 }, alignment: { horizontal: 'center', vertical: 'center', wrapText: true }, border: ALL_BORDERS, fill: { fgColor: { rgb: 'F1F5F9' } } }
const tableCellStyle = { font: { name: 'Khmer OS Battambang', sz: 11 }, alignment: { horizontal: 'center', vertical: 'center' }, border: ALL_BORDERS }
const tableCellLeftStyle = { font: { name: 'Khmer OS Battambang', sz: 11 }, alignment: { horizontal: 'left', vertical: 'center' }, border: ALL_BORDERS }
const totalStyle = { font: { name: 'Khmer OS Battambang', bold: true, sz: 11 }, alignment: { horizontal: 'left', vertical: 'center' } }
const dateLineStyle = { font: { name: 'Khmer OS Battambang', sz: 11 }, alignment: { horizontal: 'right', vertical: 'center' } }
const dateMoulRightStyle = { font: { name: 'Khmer OS Muol Light', sz: 11 }, alignment: { horizontal: 'right', vertical: 'center' } }

const styleCell = (ws, r, c, style) => {
  const addr = XLSX.utils.encode_cell({ r, c })
  if (!ws[addr]) ws[addr] = { t: 's', v: '' }
  ws[addr].s = style
}

// ----- Export: Excel -----
const exportExcel = () => {
  if (sortedStudents.value.length === 0) return

  const subNames = subjects.value.map(s => s.name)
  const tableHeader = ['ល.រ', 'ឈ្មោះសិស្ស', 'ភេទ', ...subNames, 'សរុប', 'មធ្យមភាគ', 'ចំ.ថ្នាក់', 'និទ្ទេស', 'លទ្ធផល']
  const colCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [schoolName.value],
    [`ថ្នាក់ទី${selectedClassLabel.value}`],
    [`តារាងស្រង់ពិន្ទុ ${selectedExamLabel.value}`],
    [],
  ]

  const tableRows = sortedStudents.value.map((s, index) => {
    const subScores = subjects.value.map(sub => formatNum(s.scores[sub.id]))
    return [
      index + 1,
      s.name_kh,
      getGenderKh(s.gender),
      ...subScores,
      calculateTotal(s),
      calculateAverage(s),
      index + 1,
      getGradeKh(calculateAverage(s)),
      getResult(s).text
    ]
  })

  let cursor = headerRows.length + 1 + tableRows.length
  const extraRows = []
  const styleQueue = []
  const mergeQueue = []

  const pushRow = (rowValues) => {
    extraRows.push(rowValues)
    const r = cursor
    cursor += 1
    return r
  }

  pushRow([])

  const summaryRowIdx = pushRow([
    `សរុបចំនួនសិស្ស៖ ${sortedStudents.value.length} នាក់    ស្រីចំនួន៖ ${totalFemaleCount.value} នាក់    ជាប់៖ ${countPass.value} នាក់    ធ្លាក់៖ ${countFail.value} នាក់`
  ])
  styleQueue.push({ r: summaryRowIdx, c: 0, style: totalStyle })
  mergeQueue.push({ r: summaryRowIdx, c1: 0, c2: colCount - 1 })

  pushRow([])

  const dateRow1Idx = pushRow(['', '', '', '', 'ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............'])
  const dateRow2Idx = pushRow(['', '', '', '', 'នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'])
  const teacherTitleIdx = pushRow(['', '', '', '', 'គ្រូបន្ទប់ថ្នាក់'])
  const teacherNameIdx = pushRow(['', '', '', '', selectedClassTeacherName.value])
  const approvalRow1Idx = pushRow(['បានឃើញ និងឯកភាព'])
  const approvalRow2Idx = pushRow(['នាយកសាលា'])

  const rightColStart = Math.max(3, colCount - 4)
  mergeQueue.push({ r: dateRow1Idx, c1: rightColStart, c2: colCount - 1 })
  mergeQueue.push({ r: dateRow2Idx, c1: rightColStart, c2: colCount - 1 })
  mergeQueue.push({ r: teacherTitleIdx, c1: rightColStart, c2: colCount - 1 })
  mergeQueue.push({ r: teacherNameIdx, c1: rightColStart, c2: colCount - 1 })
  mergeQueue.push({ r: approvalRow1Idx, c1: 0, c2: 2 })
  mergeQueue.push({ r: approvalRow2Idx, c1: 0, c2: 2 })

  styleQueue.push({ r: dateRow1Idx, c: rightColStart, style: dateLineStyle })
  styleQueue.push({ r: dateRow2Idx, c: rightColStart, style: dateLineStyle })
  styleQueue.push({ r: teacherTitleIdx, c: rightColStart, style: dateMoulRightStyle })
  styleQueue.push({ r: teacherNameIdx, c: rightColStart, style: dateLineStyle })
  styleQueue.push({ r: approvalRow1Idx, c: 0, style: dateMoulRightStyle })
  styleQueue.push({ r: approvalRow2Idx, c: 0, style: dateMoulRightStyle })

  const aoa = [...headerRows, tableHeader, ...tableRows, ...extraRows]
  const ws = XLSX.utils.aoa_to_sheet(aoa)

  const tableHeaderRowIdx = headerRows.length
  const tableStartRowIdx = tableHeaderRowIdx + 1
  const tableEndRowIdx = tableStartRowIdx + tableRows.length - 1

  ws['!merges'] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: colCount - 1 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: colCount - 1 } },
    { s: { r: 3, c: 0 }, e: { r: 3, c: colCount - 1 } },
    { s: { r: 4, c: 0 }, e: { r: 4, c: colCount - 1 } },
    { s: { r: 5, c: 0 }, e: { r: 5, c: colCount - 1 } },
    ...mergeQueue.map(m => ({ s: { r: m.r, c: m.c1 }, e: { r: m.r, c: m.c2 } })),
  ]

  styleCell(ws, 0, 0, kingdomStyle)
  styleCell(ws, 1, 0, mottoStyle)
  styleCell(ws, 3, 0, officeStyle)
  styleCell(ws, 4, 0, officeStyle)
  styleCell(ws, 5, 0, titleStyle)

  for (let c = 0; c < colCount; c++) {
    styleCell(ws, tableHeaderRowIdx, c, tableHeaderStyle)
  }

  for (let r = tableStartRowIdx; r <= tableEndRowIdx; r++) {
    for (let c = 0; c < colCount; c++) {
      const isLeftAlign = [1].includes(c)
      styleCell(ws, r, c, isLeftAlign ? tableCellLeftStyle : tableCellStyle)
    }
  }

  styleQueue.forEach(({ r, c, style }) => styleCell(ws, r, c, style))

  const cols = [{ wch: 6 }, { wch: 24 }, { wch: 8 }]
  for (let i = 0; i < subNames.length; i++) cols.push({ wch: 12 })
  cols.push({ wch: 10 }, { wch: 12 }, { wch: 10 }, { wch: 10 }, { wch: 12 })
  ws['!cols'] = cols

  ws['!rows'] = [{ hpt: 26 }]

  ws['!pageSetup'] = { orientation: 'landscape', fitToWidth: 1, fitToHeight: 0 }
  ws['!printOptions'] = { horizontalCentered: true }
  ws['!margins'] = { left: 0.5, right: 0.5, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'ស្រង់ពិន្ទុ')
  XLSX.writeFile(wb, `${sanitizeFileName(reportTitle.value)}.xlsx`)
}

// ----- Export: PDF -----
const exportPDF = async () => {
  if (sortedStudents.value.length === 0) return
  if (!printArea.value) return

  const opt = {
    margin: [10, 8, 10, 8],
    filename: `${sanitizeFileName(reportTitle.value)}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
  }

  if (document.fonts && document.fonts.ready) {
    await document.fonts.ready
  }

  printArea.value.style.display = 'block'
  try {
    await html2pdf().set(opt).from(printArea.value).save()
  } catch (err) {
    console.error('PDF export failed:', err)
    showToast('មានបញ្ហាក្នុងការទាញយក PDF', 'error')
  } finally {
    printArea.value.style.display = 'none'
  }
}

// --- Lifecycle ---
onMounted(async () => {
  try {
    const [cRes, eRes, sRes] = await Promise.all([api.get('/classes'), api.get('/exams'), api.get('/subjects')])
    classes.value = unwrapArray(cRes.data)
    subjects.value = unwrapArray(sRes.data)
    exams.value = unwrapArray(eRes.data).map(item => {
      let t = item.type || ''
      if (t === 'ប្រចាំឆមាស') t = 'ឆមាស'
      if (t === 'ចុងឆ្នាំ' || t === 'ប្រឡងចុងឆ្នាំ') t = 'ប្រចាំឆ្នាំ'
      return { ...item, type: t }
    })
  } catch (err) {
    console.error('Error loading initial data:', err)
    loadError.value = 'មានបញ្ហាក្នុងការទាញយកថ្នាក់ និងមុខវិជ្ជា'
  }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

.pdf-export-area {
  display: none;
  width: 277mm;
  margin: 0 auto;
  padding: 10px 20px 20px;
  background: #ffffff;
  color: #000000;
  font-family: 'Battambang', sans-serif;
}

.doc-header {
  text-align: center;
  margin-bottom: 10px;
}

.kingdom-title {
  font-family: 'Moul', serif;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.kingdom-motto {
  font-family: 'Moul', serif;
  font-weight: 500;
  font-size: 14px;
  margin: 3px 0 10px;
}

.ornament {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.ornament-line {
  width: 70px;
  height: 1px;
  background: #000000;
}

.ornament-icon {
  font-size: 11px;
  line-height: 1;
}

.doc-office {
  font-family: 'Moul', serif, Arial, Helvetica, sans-serif;
  font-weight: 500;
  font-size: 14px;
  margin: 14px 0 18px;
}

.doc-office p {
  font-family: 'Moul', serif;
  font-size: 14px;
  line-height: 1.8;
  margin: 2px 0;
}

.doc-title {
  font-family: 'Moul', Arial, sans-serif;
  font-weight: 500;
  font-size: 14px;
  text-align: center;
  margin: 0 0 16px;
}

.latin-text {
  font-family: 'Moul', sans-serif, Arial;
  font-weight: 500;
  font-size: 14px;
}

.pdf-table {
  width: 100%;
  margin: 0 auto;
  border-collapse: collapse;
  font-size: 12px;
  text-align: center;
}

.pdf-table th,
.pdf-table td {
  border: 1px solid #000000;
  padding: 5px 6px;
  white-space: nowrap;
}

.pdf-table th {
  font-weight: 700;
  text-align: center;
  background: #ffffff;
}

.doc-summary {
  margin-top: 15px;
  font-size: 13px;
  font-weight: bold;
}

.doc-remarks {
  display: grid;
  grid-template-columns: max-content max-content;
  column-gap: 10px;
  row-gap: 2px;
  justify-content: start;
  margin-top: 10px;
  font-size: 13px;
}

.remark-item {
  line-height: 1;
}

.doc-date {
  text-align: right;
  font-size: 13px;
  margin-top: 18px;
  line-height: 1.8;
}

.teacher-name {
  margin-top: 60px;
  font-weight: bold;
  text-align: right;
  margin-right: 10px;
}

.doc-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-top: 35px;
}

.signature-left,
.signature-right {
  width: 40%;
  text-align: center;
}

.moul {
  font-family: 'Moul', serif;
  font-size: 14px;
  margin-right: 50px;
}

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.toast-slide-enter-from,
.toast-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>