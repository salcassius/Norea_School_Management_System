<template>
  <div class="font-[Battambang]">
    <!-- Filter bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[180px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ថ្នាក់</label>
        <select v-model="reportSelectedClass" @change="resetReportExamSelection"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
          <option value="">ជ្រើសរើសថ្នាក់</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.grade_level }}{{ cls.name }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[180px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ប្រភេទការប្រឡង</label>
        <select v-model="reportSelectedType" @change="resetReportExamSelection"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
          <option value="">ជ្រើសរើសប្រភេទ</option>
          <option v-for="type in EXAM_TYPES" :key="type" :value="type">{{ type }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[220px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ឈ្មោះការប្រឡង</label>
        <select v-model="reportSelectedExam" :disabled="!reportSelectedClass || !reportSelectedType"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 disabled:opacity-60">
          <option value="">ជ្រើសរើសការប្រឡង</option>
          <option v-for="exam in examsForReport" :key="exam.id" :value="exam.id">{{ exam.name }}</option>
        </select>
      </div>

      <button @click="fetchExamResults" :disabled="isLoadingResults || !reportSelectedExam"
        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 active:scale-[0.98] transition-all h-[42px] disabled:opacity-50 disabled:active:scale-100 shadow-sm shadow-indigo-200">
        {{ isLoadingResults ? 'កំពុងផ្ទុក...' : 'មើលលទ្ធផល' }}
      </button>
    </div>

    <div v-if="loadError" class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold">
      {{ loadError }}
    </div>

    <!-- Results table -->
    <div v-if="examResults.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-auto max-h-[65vh]">
        <table class="w-full text-left border-collapse min-w-[900px]">
          <thead class="bg-slate-100 sticky top-0 z-10">
            <tr>
              <th class="p-3 text-[15px] font-bold text-slate-600">ល.រ</th>
              <th class="p-3 text-[15px] font-bold text-slate-600">គោត្តនាម-នាម</th>
              <th class="p-3 text-[15px] font-bold text-slate-600 text-center">ភេទ</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">មធ្យមភាគ</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">ចំណាត់ថ្នាក់</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">និទ្ទេស</th>
              <th class="p-3 text-[15px] font-bold text-slate-600 text-center">មូលវិចារ</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(student, index) in rankedResults" :key="student.id" class="hover:bg-slate-50 transition-colors">
              <td class="p-3 text-sm text-slate-600">{{ index + 1 }}</td>
              <td class="p-3 text-sm font-bold text-slate-800">{{ student.name_kh }}</td>
              <td class="p-3 text-sm text-center">{{ getGenderKh(student.gender) }}</td>
              <td class="p-3 text-sm text-center font-bold">{{ calculateAverage(student) }}</td>
              <td class="p-3 text-sm text-center font-bold text-indigo-700">{{ student.rank }}</td>
              <td class="p-3 text-sm text-center font-bold"
                :class="getRemarkKh(calculateAverage(student)) === 'ធ្លាក់' ? 'text-rose-600' : 'text-green-600'">
                {{ getRemarkKh(calculateAverage(student)) }}
              </td>
              <td class="p-3 text-[15px] text-center font-bold" :class="getResult(student).color">
                {{ getResult(student).text }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Sticky export bar -->
      <div class="sticky bottom-0 z-20 p-4 border-t border-slate-200 bg-slate-50 flex flex-wrap justify-end gap-3 shadow-[0_-2px_6px_rgba(0,0,0,0.05)]">
        <button @click="exportExcel"
          class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-green-50 active:scale-[0.98] transition-all">
          នាំចេញ Excel
        </button>
        <button @click="exportPDF"
          class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-red-50 active:scale-[0.98] transition-all">
          នាំចេញ PDF
        </button>
      </div>
    </div>

    <div v-else-if="reportSelectedExam && !isLoadingResults"
      class="p-8 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
      មិនទាន់មានទិន្នន័យពិន្ទុសម្រាប់ការប្រឡងនេះទេ
    </div>

    <!--
      Totals summary: overall + gender + pass/fail chips, then grade/remark breakdown.
      Only appears AFTER clicking "មើលលទ្ធផល" and results actually come back (tied to examResults.length).
    -->
    <div v-if="examResults.length > 0" class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex flex-wrap items-center gap-2 pb-4 mb-4 border-b border-slate-100">
        <span class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-sm font-bold">
          ចំនួនសិស្សសរុប {{ rankedResults.length }} នាក់
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
        <div v-for="row in remarkBreakdown" :key="row.remark"
          class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-100">
          <span class="text-sm font-bold text-slate-700">{{ row.remark }}</span>
          <span class="text-xs text-slate-600 whitespace-nowrap">{{ row.total }} នាក់ (ស្រី {{ row.female }})</span>
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
          <p class="mt-12 font-bold text-right mr-12">
            {{ selectedClassTeacherName }}
          </p>
        </div>
      </div>
      <div class="flex justify-between">
        <div class="text-center font-moul">
          <p>បានឃើញ និងឯកភាព</p>
          <p>នាយកសាលា</p>
        </div>
      </div>
    </div>

    <!-- ផ្នែកលាក់សម្រាប់បង្កើត PDF ជាទម្រង់លិខិតបទដ្ឋានផ្លូវការ (ក្បាលក្រដាស់រាជរដ្ឋាភិបាល) -->
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
        <p>{{ ministryLine2 }}</p>
        <p>ថ្នាក់ទី{{ selectedClassLabel }}</p>
      </div>

      <h2 class="doc-title">
        បញ្ជីលទ្ធផល<span class="latin-text">{{ selectedExamLabel }}</span>
      </h2>
      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width:6%">ល.រ</th>
            <th style="width:26%">គោត្តនាម-នាម</th>
            <th style="width:8%">ភេទ</th>
            <th style="width:14%">មធ្យមភាគ</th>
            <th style="width:14%">ចំណាត់ថ្នាក់</th>
            <th style="width:12%">និទ្ទេស</th>
            <th style="width:20%">មូលវិចារ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, index) in rankedResults" :key="'pdf-' + student.id">
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td style="text-align:left">{{ student.name_kh }}</td>
            <td>{{ getGenderKh(student.gender) }}</td>
            <td>{{ toKhmerNumber(calculateAverage(student)) }}</td>
            <td>{{ toKhmerNumber(student.rank) }}</td>
            <td>{{ getGradeKh(calculateAverage(student)) }}</td>
            <td>{{ getResult(student).text }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបចំនួនសិស្ស៖ {{ toKhmerNumber(rankedResults.length) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ស្រីចំនួន៖ {{ toKhmerNumber(totalFemaleCount) }} នាក់
        </strong>
      </div>

      <!-- គ្រប់កម្រិត total ទាំងអស់ ជា ២ជួរ ហើយចន្លោះនៃ ២ជួរនៅជិតគ្នា -->
      <div class="doc-remarks" v-if="remarkBreakdown.length">
        <div class="remark-item" v-for="row in remarkBreakdown" :key="row.remark">
          {{ row.remark }}៖ {{ toKhmerNumber(row.total) }} នាក់
          &nbsp;&nbsp;
          ស្រី {{ toKhmerNumber(row.female) }} នាក់
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
import api from '@/services/authService'
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'

defineOptions({ name: 'ResultScore' })

// Fixed list of exam type options
const EXAM_TYPES = ['ប្រចាំខែ', 'ឆមាស', 'ប្រចាំឆ្នាំ']
// Average (out of 100) needed to be considered "ជាប់" (passing)
const PASS_MARK = 50
// លំដាប់និទ្ទេស/មូលវិចារ ប្រើសម្រាប់ការបូកសរុបតាមភេទ (គ្រប់កម្រិតទាំងអស់ តាមលំដាប់បង្ហាញ)
const REMARK_ORDER = ['ល្អប្រសើរ', 'ល្អណាស់', 'ល្អ', 'ល្អបង្គួរ', 'មធ្យម', 'ធ្លាក់']

// --- Shared data (this component fetches its own — fully self-contained) ---
const exams = ref([])
const subjects = ref([])
const classes = ref([])
const isLoading = ref(false)
const loadError = ref('')

// --- Report filters/state ---
const reportSelectedClass = ref('')
const reportSelectedType = ref('')
const reportSelectedExam = ref('')
const examResults = ref([])
const isLoadingResults = ref(false)
const printArea = ref(null)

// អាចកែសម្រួលឈ្មោះការិយាល័យ/សាលារបស់អ្នកនៅទីនេះ
const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

// Helper: unwrap API responses that may come as [...] or { data: [...] }
const unwrapArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

// Exams offered in the report dropdown must match BOTH the selected class and the selected type
const examsForReport = computed(() => exams.value.filter(e =>
  e.type === reportSelectedType.value &&
  (!reportSelectedClass.value || e.class_id === reportSelectedClass.value || !e.class_id)
))

const selectedClassObj = computed(() => classes.value.find(c => c.id === reportSelectedClass.value))

const selectedClassLabel = computed(() => {
  const cls = selectedClassObj.value
  return cls ? `${cls.grade_level}${cls.name}` : ''
})

// ឈ្មោះគ្រូបន្ទប់ថ្នាក់ (ព្យាយាមទាញពីទំនាក់ទំនង teacher, បន្ទាប់មករក teacher_name, ចុងក្រោយ teacher_id ជា fallback)
const selectedClassTeacherName = computed(() => {
  const cls = selectedClassObj.value
  if (!cls) return ''
  return cls.teacher?.name_kh || cls.teacher?.name || cls.teacher_name || cls.teacher_id || ''
})

const selectedExamLabel = computed(() => {
  const exam = exams.value.find(e => e.id === reportSelectedExam.value)
  return exam ? exam.name : ''
})

// ចំណងជើងឯកសារពេញ៖ ប្រើសម្រាប់ទាំងចំណងជើងក្នុងឯកសារ និងឈ្មោះឯកសារពេលទាញយក
const reportTitle = computed(() => {
  const parts = ['បញ្ជីលទ្ធផលប្រឡង']
  if (selectedExamLabel.value) parts.push(selectedExamLabel.value)
  if (selectedClassLabel.value) parts.push(`ថ្នាក់ទី${selectedClassLabel.value}`)
  return parts.join(' ')
})

// ធ្វើអោយឈ្មោះឯកសារមានសុវត្ថិភាព (ដកតួអក្សរដែលប្រព័ន្ធឯកសារមិនអនុញ្ញាត)
const sanitizeFileName = (name) => name.replace(/[\\/:*?"<>|]/g, '-').trim()

const toKhmerNumber = (num) => {
  const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' }
  return String(num).replace(/[0-9]/g, (s) => map[s])
}

const resetReportExamSelection = () => { reportSelectedExam.value = ''; examResults.value = [] }

const calculateTotal = (student) => Object.values(student.scores || {}).reduce((acc, curr) => acc + (Number(curr) || 0), 0)

const calculateAverage = (student) => {
  let totalScore = 0
  let totalMax = 0
  subjects.value.forEach(subject => {
    totalScore += Number(student.scores?.[subject.id] || 0)
    totalMax += Number(subject.max_score)
  })
  if (totalMax === 0) return 0
  return ((totalScore / totalMax) * 100).toFixed(2)
}

const getGradeKh = (avg) => {
  avg = Number(avg)
  if (avg >= 90) return 'A'
  if (avg >= 80) return 'B'
  if (avg >= 70) return 'C'
  if (avg >= 60) return 'D'
  if (avg >= 50) return 'E'
  return 'F'
}

const getGenderKh = (gender) => {
  if (!gender) return '-'
  const g = String(gender).toLowerCase()
  if (g === 'm' || g === 'male' || g === 'ប្រុស') return 'ប្រុស'
  if (g === 'f' || g === 'female' || g === 'ស្រី') return 'ស្រី'
  return '-'
}

const isFemale = (gender) => getGenderKh(gender) === 'ស្រី'

const getRemarkKh = (avg) => {
  avg = Number(avg)
  if (avg >= 90) return 'ល្អប្រសើរ'
  if (avg >= 80) return 'ល្អណាស់'
  if (avg >= 70) return 'ល្អ'
  if (avg >= 60) return 'ល្អបង្គួរ'
  if (avg >= 50) return 'មធ្យម'
  return 'ធ្លាក់'
}

// Pass/fail based on the pass mark (average out of 100)
const getResult = (student) => {
  const avg = Number(calculateAverage(student))
  if (avg >= PASS_MARK) return { text: 'ជាប់', color: 'text-emerald-600' }
  return { text: 'ធ្លាក់', color: 'text-rose-700' }
}

// Ranks students by total score, handling ties with standard competition ranking (1,2,2,4...)
const rankedResults = computed(() => {
  const sorted = [...examResults.value].sort((a, b) => calculateTotal(b) - calculateTotal(a))
  let rank = 0
  let prevTotal = null
  let position = 0
  return sorted.map(student => {
    position += 1
    const total = calculateTotal(student)
    if (total !== prevTotal) {
      rank = position
      prevTotal = total
    }
    return { ...student, rank }
  })
})

// ចំនួនសិស្សស្រីសរុប
const totalFemaleCount = computed(() => rankedResults.value.filter(s => isFemale(s.gender)).length)

// ជាប់/ធ្លាក់សរុប
const countPass = computed(() => rankedResults.value.filter(s => Number(calculateAverage(s)) >= PASS_MARK).length)
const countFail = computed(() => rankedResults.value.filter(s => Number(calculateAverage(s)) < PASS_MARK).length)

// បូកសរុបចំនួនសិស្ស (សរុប + ស្រី) តាមមូលវិចារ "គ្រប់កម្រិតទាំងអស់" សូម្បីតែកម្រិតគ្មានសិស្ស (០ នាក់) ក៏បង្ហាញដែរ
const remarkBreakdown = computed(() => {
  const counts = {}
  REMARK_ORDER.forEach(remark => { counts[remark] = { total: 0, female: 0 } })

  rankedResults.value.forEach(student => {
    const remark = getRemarkKh(calculateAverage(student))
    if (!counts[remark]) counts[remark] = { total: 0, female: 0 }
    counts[remark].total += 1
    if (isFemale(student.gender)) counts[remark].female += 1
  })

  return REMARK_ORDER.map(remark => ({ remark, ...counts[remark] }))
})

const fetchExamResults = async () => {
  if (!reportSelectedExam.value || !reportSelectedClass.value) return

  isLoadingResults.value = true
  loadError.value = ''
  examResults.value = []

  try {
    const res = await api.get('/scores', {
      params: {
        exam_id: reportSelectedExam.value,
        class_id: reportSelectedClass.value
      }
    })

    const list = unwrapArray(res.data)

    examResults.value = list.map(row => ({
      id: row.student_id,
      name_kh: row.student?.name_kh || '',
      gender: row.student?.gender || '',
      scores: row.scores || row.subject_scores || {}
    }))
  } catch (err) {
    console.error('fetchExamResults failed:', err)
    loadError.value = 'មានបញ្ហាក្នុងការទាញយកលទ្ធផលប្រឡង'
  } finally {
    isLoadingResults.value = false
  }
}

// ----- ម៉ូតបែបលិខិតបទដ្ឋាន សម្រាប់ Excel (ដូចគ្នានឹង PDF) -----
const THIN_BORDER = { style: 'thin', color: { rgb: '000000' } }
const ALL_BORDERS = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER }

const kingdomStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 16 },
  alignment: { horizontal: 'center', vertical: 'center' },
}
const mottoStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 13 },
  alignment: { horizontal: 'center', vertical: 'center' },
}
const officeStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 13 },
  alignment: { horizontal: 'left', vertical: 'center' },
}
const titleStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 13 },
  alignment: { horizontal: 'center', vertical: 'center' },
}
const tableHeaderStyle = {
  font: { name: 'Khmer OS Battambang', bold: true, sz: 12 },
  alignment: { horizontal: 'center', vertical: 'center' },
  border: ALL_BORDERS,
}
const tableCellStyle = {
  font: { name: 'Khmer OS Battambang', sz: 12 },
  alignment: { horizontal: 'center', vertical: 'center' },
  border: ALL_BORDERS,
}
const totalStyle = {
  font: { name: 'Khmer OS Battambang', bold: true, sz: 12 },
  alignment: { horizontal: 'left', vertical: 'center' },
}
const remarkLineStyle = {
  font: { name: 'Khmer OS Battambang', sz: 11 },
  alignment: { horizontal: 'left', vertical: 'center' },
}
const dateLineStyle = {
  font: { name: 'Khmer OS Battambang', sz: 12 },
  alignment: { horizontal: 'right', vertical: 'center' },
}
const dateMoulRightStyle = {
  font: { name: 'Khmer OS Muol Light', sz: 12 },
  alignment: { horizontal: 'right', vertical: 'center' },
}
const approvalMoulStyle = {
  font: { name: 'Khmer OS Muol Light', sz: 12 },
  alignment: { horizontal: 'center', vertical: 'center' },
}

const styleCell = (ws, r, c, style) => {
  const addr = XLSX.utils.encode_cell({ r, c })
  if (!ws[addr]) ws[addr] = { t: 's', v: '' }
  ws[addr].s = style
}

// ----- Export: Excel (ជាទម្រង់ក្បាលក្រដាស់រាជរដ្ឋាភិបាល ដូចគ្នានឹង PDF) -----
const exportExcel = () => {
  if (rankedResults.value.length === 0) return

  const tableHeader = ['ល.រ', 'គោត្តនាម-នាម', 'ភេទ', 'មធ្យមភាគ', 'ចំណាត់ថ្នាក់', 'និទ្ទេស', 'មូលវិចារ']
  const colCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine2.value],
    [`ថ្នាក់ទី ${selectedClassLabel.value}`],
    [`បញ្ជីលទ្ធផល${selectedExamLabel.value}`],
    [],
  ]

  const tableRows = rankedResults.value.map((student, index) => {
    const avg = calculateAverage(student)
    return [
      toKhmerNumber(index + 1),
      student.name_kh,
      getGenderKh(student.gender),
      toKhmerNumber(avg),
      toKhmerNumber(student.rank),
      getGradeKh(avg),
      getResult(student).text,
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
    `សរុបចំនួនសិស្ស៖ ${toKhmerNumber(rankedResults.value.length)} នាក់    ស្រីចំនួន៖ ${toKhmerNumber(totalFemaleCount.value)} នាក់    សិស្សជាប់៖ ${toKhmerNumber(countPass.value)} នាក់    សិស្សធ្លាក់៖ ${toKhmerNumber(countFail.value)} នាក់`
  ])
  styleQueue.push({ r: summaryRowIdx, c: 0, style: totalStyle })

  const leftColEnd = Math.min(2, colCount - 1)
  const rightColStart = Math.min(4, colCount - 1)
  for (let i = 0; i < remarkBreakdown.value.length; i += 2) {
    const left = remarkBreakdown.value[i]
    const right = remarkBreakdown.value[i + 1]

    const leftText = left ? `${left.remark}៖ ${toKhmerNumber(left.total)} នាក់  ស្រី ${toKhmerNumber(left.female)} នាក់` : ''
    const rightText = right ? `${right.remark}៖ ${toKhmerNumber(right.total)} នាក់  ស្រី ${toKhmerNumber(right.female)} នាក់` : ''

    const rowValues = []
    rowValues[0] = leftText
    rowValues[rightColStart] = rightText
    const idx = pushRow(rowValues)

    styleQueue.push({ r: idx, c: 0, style: remarkLineStyle })
    mergeQueue.push({ r: idx, c1: 0, c2: leftColEnd })

    if (right) {
      styleQueue.push({ r: idx, c: rightColStart, style: remarkLineStyle })
      mergeQueue.push({ r: idx, c1: rightColStart, c2: colCount - 1 })
    }
  }

  pushRow([])

  const dateRow1Idx = pushRow(['ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............'])
  const dateRow2Idx = pushRow(['នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'])
  const teacherTitleIdx = pushRow(['គ្រូបន្ទប់ថ្នាក់'])
  const teacherNameIdx = pushRow([selectedClassTeacherName.value])

  mergeQueue.push({ r: dateRow1Idx, c1: 0, c2: colCount - 1 })
  mergeQueue.push({ r: dateRow2Idx, c1: 0, c2: colCount - 1 })
  mergeQueue.push({ r: teacherTitleIdx, c1: 0, c2: colCount - 1 })
  mergeQueue.push({ r: teacherNameIdx, c1: 0, c2: colCount - 1 })
  styleQueue.push({ r: dateRow1Idx, c: 0, style: dateLineStyle })
  styleQueue.push({ r: dateRow2Idx, c: 0, style: dateLineStyle })
  styleQueue.push({ r: teacherTitleIdx, c: 0, style: dateMoulRightStyle })
  styleQueue.push({ r: teacherNameIdx, c: 0, style: dateLineStyle })
  pushRow([])

  const approvalRow1Idx = pushRow(['បានឃើញ និងឯកភាព'])
  const approvalRow2Idx = pushRow(['នាយកសាលា'])

  const leftBlockEndCol = Math.min(2, colCount - 1)
  mergeQueue.push({ r: approvalRow1Idx, c1: 0, c2: leftBlockEndCol })
  mergeQueue.push({ r: approvalRow2Idx, c1: 0, c2: leftBlockEndCol })
  styleQueue.push({ r: approvalRow1Idx, c: 0, style: approvalMoulStyle })
  styleQueue.push({ r: approvalRow2Idx, c: 0, style: approvalMoulStyle })

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
    { s: { r: 6, c: 0 }, e: { r: 6, c: colCount - 1 } },
    ...mergeQueue.map(m => ({ s: { r: m.r, c: m.c1 }, e: { r: m.r, c: m.c2 } })),
  ]

  styleCell(ws, 0, 0, kingdomStyle)
  styleCell(ws, 1, 0, mottoStyle)
  styleCell(ws, 3, 0, officeStyle)
  styleCell(ws, 4, 0, officeStyle)
  styleCell(ws, 6, 0, titleStyle)

  for (let c = 0; c < colCount; c++) {
    styleCell(ws, tableHeaderRowIdx, c, tableHeaderStyle)
  }

  for (let r = tableStartRowIdx; r <= tableEndRowIdx; r++) {
    for (let c = 0; c < colCount; c++) {
      styleCell(ws, r, c, tableCellStyle)
    }
  }

  styleQueue.forEach(({ r, c, style }) => styleCell(ws, r, c, style))

  ws['!cols'] = [
    { wch: 6 }, { wch: 26 }, { wch: 8 }, { wch: 10 }, { wch: 12 }, { wch: 8 }, { wch: 10 }
  ]

  ws['!rows'] = [{ hpt: 24 }]

  // Portrait page setup (this report has few columns, so portrait fits comfortably)
  ws['!pageSetup'] = { orientation: 'portrait', fitToWidth: 1, fitToHeight: 0 }
  ws['!printOptions'] = { horizontalCentered: true }
  ws['!margins'] = { left: 0.5, right: 0.5, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'លទ្ធផល')
  XLSX.writeFile(wb, `${sanitizeFileName(reportTitle.value)}.xlsx`)
}

// ----- Export: PDF (ជាទម្រង់ក្បាលក្រដាស់រាជរដ្ឋាភិបាល ដោយប្រើ html2pdf.js) -----
const exportPDF = async () => {
  if (rankedResults.value.length === 0) return
  if (!printArea.value) return

  const opt = {
    margin: [12, 10, 12, 10],
    filename: `${sanitizeFileName(reportTitle.value)}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  }

  if (document.fonts && document.fonts.ready) {
    await document.fonts.ready
  }

  printArea.value.style.display = 'block'
  try {
    await html2pdf().set(opt).from(printArea.value).save()
  } catch (err) {
    console.error(err)
    loadError.value = 'មានបញ្ហាក្នុងការនាំចេញ PDF'
  } finally {
    printArea.value.style.display = 'none'
  }
}

const fetchData = async () => {
  isLoading.value = true
  loadError.value = ''
  try {
    const [examRes, classRes, subjectRes] = await Promise.all([
      api.get('/exams'),
      api.get('/classes'),
      api.get('/subjects')
    ])
    exams.value = unwrapArray(examRes.data)
    classes.value = unwrapArray(classRes.data)
    subjects.value = unwrapArray(subjectRes.data)
  } catch (err) {
    console.error('fetchData failed:', err)
    loadError.value = 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យ (ថ្នាក់/ការប្រឡង/មុខវិជ្ជា)'
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchData)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

.pdf-export-area {
  display: none;
  width: 190mm;
  margin: 0 auto;
  padding: 14px 24px 24px;
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
  margin: 2;
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
  border-collapse: collapse;
  font-size: 13px;
  text-align: center;
}

.pdf-table th,
.pdf-table td {
  border: 1px solid #000000;
  padding: 6px 8px;
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

.doc-remarks p {
  margin: 2px 0;
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

.signature-right {
  margin-top: 10px;
}
</style>