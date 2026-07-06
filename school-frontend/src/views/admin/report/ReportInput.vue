<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <!-- Filter bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ថ្នាក់</label>
        <select v-model="selectedClass" @change="resetExamSelection"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-[14px] outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
          <option value="">ជ្រើសរើសថ្នាក់</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.grade_level }}{{ cls.name }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ប្រភេទការប្រឡង</label>
        <select v-model="inputSelectedType" @change="resetExamSelection"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
          <option value="">ជ្រើសរើសប្រភេទ</option>
          <option v-for="type in EXAM_TYPES" :key="type" :value="type">{{ type }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 ml-1">ឈ្មោះការប្រឡង</label>
        <select v-model="selectedExam" :disabled="!inputSelectedType"
          class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 disabled:opacity-60">
          <option value="">ជ្រើសរើសការប្រឡង</option>
          <option v-for="exam in examsByInputType" :key="exam.id" :value="exam.id">{{ exam.name }}</option>
        </select>
      </div>

      <button @click="fetchStudentsForScoring" :disabled="isLoadingStudents || !selectedExam || !selectedClass"
        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 active:scale-[0.98] transition-all h-[42px] disabled:opacity-50 disabled:active:scale-100 shadow-sm shadow-indigo-200">
        {{ isLoadingStudents ? 'កំពុងផ្ទុក...' : 'បង្ហាញសិស្ស' }}
      </button>
    </div>

    <div v-if="loadError" class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold">
      {{ loadError }}
    </div>

    <div v-if="students.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Scrollable table area: header stays pinned while rows scroll -->
      <div class="overflow-auto max-h-[65vh]">
        <table class="w-full text-left border-collapse min-w-[1200px]">
          <thead class="bg-slate-100 sticky top-0 z-10 min-w-[100px]">
            <tr>
              <th class="p-3 text-[15px] font-bold text-slate-600">ល.រ</th>
              <th class="p-3 text-[15px] font-bold text-slate-600">ឈ្មោះសិស្ស</th>
              <th class="p-3 text-[15px] font-bold text-slate-600 text-center">ភេទ</th>
              <th v-for="sub in subjects" :key="sub.id" class="p-3 text-[15px] font-bold text-slate-600 text-center whitespace-nowrap min-w-[90px]">{{ sub.name }}</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">សរុប</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">មធ្យមភាគ</th>
              <th class="p-3 text-[15px] font-bold text-red-600 text-center min-w-[80px]">ចំ.ថ្នាក់</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">និទ្ទេស</th>
              <th class="p-3 text-[15px] font-bold text-indigo-600 text-center">លទ្ធផល</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(student, index) in sortedStudents" :key="student.id" class="hover:bg-slate-50 transition-colors">
              <td class="p-3 text-[15px] text-slate-600">{{ index + 1 }}</td>
              <td class="p-3 text-[15px] font-bold text-slate-800 whitespace-nowrap min-w-[140px]">{{ student.name_kh }}</td>
              <td class="p-3 text-[15px] text-center">{{ getGenderKh(student.gender) }}</td>
              <td v-for="sub in subjects" :key="sub.id" class="p-2">
                <input type="number" v-model.number="student.scores[sub.id]" class="w-16 mx-auto block border border-slate-300 rounded-lg py-1 text-center text-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none" />
              </td>
              <td class="p-3 text-[15px] font-bold text-indigo-700 text-center">{{ calculateTotal(student) }}</td>
              <td class="p-3 text-[15px] text-center">{{ calculateAverage(student) }}</td>
              <td class="p-3 text-[15px] font-bold text-red-600 text-center">{{ index + 1 }}</td>
              <td class="p-3 text-[15px] text-center font-bold text-emerald-600">{{ getGradeKh(calculateAverage(student)) }}</td>
              <td class="p-3 text-[15px] text-center font-bold" :class="getResult(student).color">{{ getResult(student).text }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Sticky action bar: always visible at the bottom of the viewport, never scrolls away -->
      <div class="sticky bottom-0 z-20 p-4 border-t border-slate-200 bg-slate-50 flex flex-wrap justify-end gap-3 shadow-[0_-2px_6px_rgba(0,0,0,0.05)]">
        <button @click="exportExcel" class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-green-50 active:scale-[0.98] transition-all">
          នាំចេញ Excel
        </button>
        <button @click="exportPDF" class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-red-50 active:scale-[0.98] transition-all">
          នាំចេញ PDF
        </button>
        <button @click="saveScores" :disabled="isSaving" class="bg-emerald-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-emerald-700 active:scale-[0.98] transition-all disabled:opacity-50 disabled:active:scale-100">
          {{ isSaving ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកពិន្ទុ' }}
        </button>
      </div>
    </div>

    <div v-else-if="selectedExam && !isLoadingStudents"
      class="p-8 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
      មិនទាន់មានទិន្នន័យសិស្សសម្រាប់ការប្រឡងនេះទេ
    </div>

    <!-- Totals summary: overall + gender + pass/fail chips, then grade breakdown (English letter + Khmer remark together) -->
    <div v-if="students.length > 0" class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex flex-wrap items-center gap-2 pb-4 mb-4 border-b border-slate-100">
        <span class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-sm font-bold">
          ចំនួនសិស្សសរុប {{ sortedStudents.length }} នាក់
        </span>
        <span class="py-1.5 rounded-full bg-pink-50 text-pink-700 text-sm font-bold">
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
        <div v-for="row in gradeBreakdown" :key="row.grade"
          class="flex items-center justify-flex gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-100">
          <span class="text-sm font-bold text-slate-700">{{ row.grade }} ({{ row.remark }})</span>
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

    <!-- ផ្នែកលាក់សម្រាប់បង្កើត PDF ជាទម្រង់លិខិតបទដ្ឋានផ្លូវការ (ក្បាលក្រដាស់រាជរដ្ឋាភិបាល) ដូចគ្នានឹង ReportResult -->
    <!-- ក្រដាស់ត្រូវបានកំណត់ជាទម្រង់ផ្ដេក (landscape) ហើយមាតិកាដាក់កណ្ដាលក្រដាស់ដោយស្វ័យប្រវត្តិ (margin auto) -->
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
        តារាងពិន្ទុ<span class="latin-text">{{ selectedExamLabel }}</span>
      </h2>

      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width:4%">ល.រ</th>
            <th style="width:14%">គោត្តនាម-នាម</th>
            <th style="width:5%">ភេទ</th>
            <th v-for="sub in subjects" :key="'h-' + sub.id">{{ sub.name }}</th>
            <th style="width:7%">សរុប</th>
            <th style="width:9%">មធ្យមភាគ</th>
            <th style="width:9%">ចំណាត់ថ្នាក់</th>
            <th style="width:7%">និទ្ទេស</th>
            <th style="width:9%">លទ្ធផល</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, index) in sortedStudents" :key="'pdf-' + student.id">
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td style="text-align:left">{{ student.name_kh }}</td>
            <td>{{ getGenderKh(student.gender) }}</td>
            <td v-for="sub in subjects" :key="'c-' + sub.id + '-' + student.id">{{ toKhmerNumber(student.scores?.[sub.id] ?? '') }}</td>
            <td>{{ toKhmerNumber(calculateTotal(student)) }}</td>
            <td>{{ toKhmerNumber(calculateAverage(student)) }}</td>
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td>{{ getGradeKh(calculateAverage(student)) }}</td>
            <td>{{ getResult(student).text }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបចំនួនសិស្ស៖ {{ toKhmerNumber(sortedStudents.length) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ស្រីចំនួន៖ {{ toKhmerNumber(totalFemaleCount) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ជាប់៖ {{ toKhmerNumber(countPass) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ធ្លាក់៖ {{ toKhmerNumber(countFail) }} នាក់
        </strong>
      </div>

      <!-- និទ្ទេសគ្រប់កម្រិត បង្ហាញទាំងអក្សរឡាតាំង (A, B, C...) និងមូលវិចារខ្មែរ ជា ២ជួរ -->
      <div class="doc-remarks" v-if="gradeBreakdown.length">
        <div class="remark-item" v-for="row in gradeBreakdown" :key="row.grade">
          {{ row.grade }} ({{ row.remark }})៖ {{ toKhmerNumber(row.total) }} នាក់
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

defineOptions({ name: 'ReportInputScore' })

const classes = ref([]), subjects = ref([]), exams = ref([]), students = ref([])
const selectedClass = ref(''), inputSelectedType = ref(''), selectedExam = ref('')
const isLoadingStudents = ref(false), isSaving = ref(false)
const loadError = ref('')
const printArea = ref(null)
const EXAM_TYPES = ['ប្រចាំខែ', 'ប្រចាំឆមាស', 'ប្រចាំឆ្នាំ']
// Pairs each Latin letter grade with its Khmer remark, in display order
const GRADE_REMARK_ORDER = [
  { grade: 'A', remark: 'ល្អប្រសើរ' },
  { grade: 'B', remark: 'ល្អណាស់' },
  { grade: 'C', remark: 'ល្អ' },
  { grade: 'D', remark: 'ល្អបង្គួរ' },
  { grade: 'E', remark: 'មធ្យម' },
  { grade: 'F', remark: 'ធ្លាក់' },
]
const PASS_MARK = 50

const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')


const unwrapArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

const examsByInputType = computed(() => exams.value.filter(e => e.type === inputSelectedType.value))

const sortedStudents = computed(() => {
  return [...students.value].sort((a, b) => calculateTotal(b) - calculateTotal(a))
})

const selectedClassObj = computed(() => classes.value.find(c => c.id === selectedClass.value))

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
  const exam = exams.value.find(e => e.id === selectedExam.value)
  return exam ? exam.name : ''
})

// ចំណងជើងឯកសារពេញ៖ ប្រើសម្រាប់ទាំងចំណងជើងក្នុងឯកសារ និងឈ្មោះឯកសារពេលទាញយក
const reportTitle = computed(() => {
  const parts = ['តារាងពិន្ទុ']
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

const getGenderKh = (gender) => {
  if (!gender) return '-'
  const g = String(gender).toLowerCase()
  if (g === 'm' || g === 'male' || g === 'ប្រុស') return 'ប្រុស'
  if (g === 'f' || g === 'female' || g === 'ស្រី') return 'ស្រី'
  return '-'
}

const isFemale = (gender) => getGenderKh(gender) === 'ស្រី'

const calculateTotal = (student) => Object.values(student.scores || {}).reduce((sum, s) => sum + (Number(s) || 0), 0)
const calculateAverage = (student) => (calculateTotal(student) / (subjects.value.length || 1)).toFixed(2)

const getGradeKh = (avg) => {
  avg = Number(avg)
  if (avg >= 90) return 'A'; if (avg >= 80) return 'B'; if (avg >= 70) return 'C';
  if (avg >= 60) return 'D'; if (avg >= 50) return 'E'; return 'F';
}

const getResult = (student) => {
  const avg = Number(calculateAverage(student))
  return avg >= PASS_MARK ? { text: 'ជាប់', color: 'text-emerald-600' } : { text: 'ធ្លាក់', color: 'text-rose-600' }
}

// ចំនួនសិស្សស្រីសរុប / ជាប់ / ធ្លាក់
const totalFemaleCount = computed(() => sortedStudents.value.filter(s => isFemale(s.gender)).length)
const countPass = computed(() => sortedStudents.value.filter(s => Number(calculateAverage(s)) >= PASS_MARK).length)
const countFail = computed(() => sortedStudents.value.filter(s => Number(calculateAverage(s)) < PASS_MARK).length)

// បូកសរុបចំនួនសិស្ស (សរុប + ស្រី) តាមនិទ្ទេស ដោយបង្ហាញទាំងអក្សរឡាតាំង (A,B,C...) និងមូលវិចារខ្មែរ រួមគ្នា
// គ្រប់កម្រិតទាំងអស់ត្រូវបង្ហាញ សូម្បីតែកម្រិតគ្មានសិស្ស (០ នាក់)
const gradeBreakdown = computed(() => {
  const counts = {}
  GRADE_REMARK_ORDER.forEach(({ grade }) => { counts[grade] = { total: 0, female: 0 } })

  sortedStudents.value.forEach(student => {
    const grade = getGradeKh(calculateAverage(student))
    if (!counts[grade]) counts[grade] = { total: 0, female: 0 }
    counts[grade].total += 1
    if (isFemale(student.gender)) counts[grade].female += 1
  })

  return GRADE_REMARK_ORDER.map(({ grade, remark }) => ({ grade, remark, ...counts[grade] }))
})

const fetchStudentsForScoring = async () => {
  isLoadingStudents.value = true
  loadError.value = ''
  try {
    const res = await api.get('/scores', {
      params: {
        exam_id: selectedExam.value,
        class_id: selectedClass.value
      }
    })
    const raw = unwrapArray(res.data)
    // Backend returns: { student_id, student: { name_kh, gender }, scores }
    // Normalize into the flat shape the table expects: { id, name_kh, gender, scores }
    students.value = raw.map(row => ({
      id: row.student_id ?? row.id,
      name_kh: row.student?.name_kh ?? row.name_kh,
      gender: row.student?.gender ?? row.gender,
      scores: row.scores || {}
    }))

    if (students.value.length === 0) {
      loadError.value = 'មិនទាន់មានទិន្នន័យពិន្ទុសម្រាប់ថ្នាក់/ការប្រឡងនេះទេ'
    }
  } catch (err) {
    console.error('fetchStudentsForScoring failed:', err)
    loadError.value = 'មិនអាចទាញយកទិន្នន័យសិស្សបានទេ'
  } finally {
    isLoadingStudents.value = false
  }
}

const saveScores = async () => {
  isSaving.value = true
  try {
    await api.post('/scores/save', {
      exam_id: selectedExam.value,
      class_id: selectedClass.value,
      data: students.value.map(s => ({
        student_id: s.id,
        scores: s.scores,
        average_score: calculateAverage(s),
        rank: sortedStudents.value.findIndex(st => st.id === s.id) + 1,
        grade: getGradeKh(calculateAverage(s))
      }))
    })
    alert('រក្សាទុកជោគជ័យ!')
  } catch (err) {
    console.error('saveScores failed:', err)
    alert('មានបញ្ហាក្នុងការរក្សាទុកពិន្ទុ')
  } finally {
    isSaving.value = false
  }
}

const resetExamSelection = () => { selectedExam.value = '' }

// ----- ម៉ូតបែបលិខិតបទដ្ឋាន សម្រាប់ Excel (ដូចគ្នានឹង ReportResult) -----
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

// ----- Export: Excel (ជាទម្រង់ក្បាលក្រដាស់រាជរដ្ឋាភិបាល ដូចគ្នានឹង ReportResult ប៉ុន្តែជាទម្រង់ផ្ដេក/landscape) -----
const exportExcel = () => {
  if (sortedStudents.value.length === 0) return

  const tableHeader = ['ល.រ', 'គោត្តនាម-នាម', 'ភេទ', ...subjects.value.map(s => s.name), 'សរុប', 'មធ្យមភាគ', 'ចំណាត់ថ្នាក់', 'និទ្ទេស', 'លទ្ធផល']
  const colCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine2.value],
    [`ថ្នាក់ទី ${selectedClassLabel.value}`],
    [`តារាងពិន្ទុ${selectedExamLabel.value}`],
    [],
  ]

  const tableRows = sortedStudents.value.map((student, index) => {
    const avg = calculateAverage(student)
    return [
      toKhmerNumber(index + 1),
      student.name_kh,
      getGenderKh(student.gender),
      ...subjects.value.map(sub => student.scores?.[sub.id] ?? ''),
      calculateTotal(student),
      toKhmerNumber(avg),
      toKhmerNumber(index + 1),
      getGradeKh(avg),
      getResult(student).text,
    ]
  })

  // --- បង្កើតជួរផ្នែកខាងក្រោមតារាង (សរុប / និទ្ទេសគ្រប់កម្រិត / កាលបរិច្ឆេទ / ហត្ថលេខា) ---
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

  pushRow([]) // ចន្លោះ

  const summaryRowIdx = pushRow([
    `សរុបចំនួនសិស្ស៖ ${toKhmerNumber(sortedStudents.value.length)} នាក់    ស្រីចំនួន៖ ${toKhmerNumber(totalFemaleCount.value)} នាក់    សិស្សជាប់៖ ${toKhmerNumber(countPass.value)} នាក់    សិស្សធ្លាក់៖ ${toKhmerNumber(countFail.value)} នាក់`
  ])
  styleQueue.push({ r: summaryRowIdx, c: 0, style: totalStyle })

  // និទ្ទេសគ្រប់កម្រិត (A,B,C...+ មូលវិចារខ្មែរ) ជា ២ជួរ ចន្លោះនៃ ២ជួរនៅជិតគ្នា
  const leftColEnd = Math.min(2, colCount - 1)
  const rightColStart = Math.min(4, colCount - 1)
  for (let i = 0; i < gradeBreakdown.value.length; i += 2) {
    const left = gradeBreakdown.value[i]
    const right = gradeBreakdown.value[i + 1]

    const leftText = left ? `${left.grade} (${left.remark})៖ ${toKhmerNumber(left.total)} នាក់  ស្រី ${toKhmerNumber(left.female)} នាក់` : ''
    const rightText = right ? `${right.grade} (${right.remark})៖ ${toKhmerNumber(right.total)} នាក់  ស្រី ${toKhmerNumber(right.female)} នាក់` : ''

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

  pushRow([]) // ចន្លោះ

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
  { wch: 6 },      
  { wch: 35 },    
  { wch: 8 },      

  ...subjects.value.map(() => ({
      wch: 18      
  })),

  { wch: 10 },
  { wch: 12 },
  { wch: 12 },
  { wch: 10 },
  { wch: 12 }
]

  ws['!rows'] = [{ hpt: 24 }]

  // ទំព័រ landscape (ផ្ដេក) និងកំណត់អោយមាតិកាចំកណ្ដាលទំព័រពេលបោះពុម្ព
  ws['!pageSetup'] = { orientation: 'landscape', fitToWidth: 1, fitToHeight: 0, scale: 100 }
  ws['!printOptions'] = { horizontalCentered: true, verticalCentered: false }
  ws['!margins'] = { left: 0.4, right: 0.4, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'ពិន្ទុ')
  XLSX.writeFile(wb, `${sanitizeFileName(reportTitle.value)}.xlsx`)
}

// ----- Export: PDF (ជាទម្រង់ក្បាលក្រដាស់រាជរដ្ឋាភិបាល ដោយប្រើ html2pdf.js — ជានិច្ចជាទម្រង់ផ្ដេក/landscape និងកណ្ដាលទំព័រ) -----
const exportPDF = async () => {
  if (sortedStudents.value.length === 0) return
  if (!printArea.value) return

  const opt = {
    // margin ខាងឆ្វេង/ស្តាំស្មើគ្នា + ទទឹង .pdf-export-area ត្រូវបានកំណត់អោយពេញកម្រិតទំព័រ landscape
    // (297mm - margin ២ខាង) ដូច្នេះមាតិកានឹងបង្ហាញនៅចំកណ្ដាលទំព័រដោយស្វ័យប្រវត្តិ
    margin: [10, 10, 10, 10],
    filename: `${sanitizeFileName(reportTitle.value)}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
  }

  // ត្រូវប្រាកដថា Font Battambang / Moul ត្រូវបានផ្ទុករួចមុននឹងថតរូប
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

onMounted(async () => {
  loadError.value = ''
  try {
    const [c, s, e] = await Promise.all([
      api.get('/classes'),
      api.get('/subjects'),
      api.get('/exams')
    ])
    classes.value = unwrapArray(c.data)
    subjects.value = unwrapArray(s.data)
    exams.value = unwrapArray(e.data)
  } catch (err) {
    console.error('Initial load failed:', err)
    loadError.value = 'មិនអាចទាញយកថ្នាក់ ឬ ការប្រឡងបានទេ សូមពិនិត្យ API'
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
  margin: 0 auto;
  border-collapse: collapse;
  font-size: 12px;
  text-align: center;
}

.pdf-table th,
.pdf-table td {
    border:1px solid #000;
    padding:5px 6px;
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