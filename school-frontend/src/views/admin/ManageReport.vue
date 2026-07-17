<template>
  <div class="p-6 bg-gray-50 min-h-screen font-['Battambang'] text-gray-800">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">គ្រប់គ្រងរបាយការណ៍</h1>
    </div>

    <!-- Main Tabs (ប៊ូតុងជ្រើសរើស រវាង សិស្ស និង គ្រូ) -->
    <div class="border-b border-gray-200 mb-6">
      <div class="flex space-x-8">
        <button v-for="tab in ['student', 'teacher']" :key="tab" @click="activeMainTab = tab" :class="[
          'pb-3 text-lg font-medium transition-colors duration-200',
          activeMainTab === tab ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700'
        ]">
          {{ tab === 'student' ? 'របាយការណ៍សិស្ស' : 'របាយការណ៍គ្រូបង្រៀន' }}
        </button>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- ផ្នែកទី ១៖ របាយការណ៍គ្រូបង្រៀន -->
    <!-- ========================================== -->
    <div v-if="activeMainTab === 'teacher'">
      <ReportTeacher />
    </div>

    <!-- ========================================== -->
    <!-- ផ្នែកទី ២៖ របាយការណ៍សិស្ស (បង្ហាញតែពេល Tab = student) -->
    <!-- ========================================== -->
    <div v-if="activeMainTab === 'student'">
      <!-- Sub-tabs សម្រាប់ប្រភេទរបាយការណ៍សិស្ស -->
      <div class="flex flex-wrap gap-3 mb-6">
        <button v-for="report in reportTypes" :key="report.id" @click="activeReportType = report.id" :class="[
          'px-5 py-2 rounded-lg text-sm transition-all border',
          activeReportType === report.id ? 'bg-blue-50 border-blue-400 text-blue-600 font-medium shadow-sm' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
        ]">
          {{ report.name }}
        </button>
      </div>

      <!-- Content របាយការណ៍សិស្សតាមប្រភេទ -->
      <div v-if="activeReportType === 1">
        <ReportStudentClass />
      </div>

      <!-- <div v-if="activeReportType === 2">
        <ReportInput />
      </div> -->

      <div v-if="activeReportType === 3">
        <ReportAttendance />
      </div>

      <div v-if="activeReportType === 4">
        <ReportResult />
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/authService'
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import ReportStudentClass from './report/ReportStudentClass.vue'
import ReportInput from '../admin/report/ReportInput.vue'
import ReportAttendance from './report/ReportAttendance.vue'
import ReportResult from './report/ReportResult.vue'
import ReportTeacher from './report/ReportTeacher.vue'

const academicYears = ref([])
const studentClasses = ref([])
const students = ref([])
const loading = ref(false)
const filterYear = ref('')
const filterClass = ref('')
const activeMainTab = ref('student')
const activeReportType = ref(1)

const reportTypes = ref([
  { id: 1, name: 'បញ្ជីឈ្មោះសិស្ស' },
  // { id: 2, name: 'របាយការណ៍ស្រង់ពិន្ទុ' },
  { id: 3, name: 'របាយការណ៍វត្តមាន' },
  { id: 4, name: 'លទ្ធផលសិក្សា' }
])

const loadFilters = async () => {
  try {
    const [years, classes] = await Promise.all([api.get('/years'), api.get('/classes')])
    academicYears.value = years.data?.data || years.data || []
    studentClasses.value = classes.data?.data || classes.data || []
  } catch (err) { 
    console.error(err) 
  }
}

const fetchReportData = async () => {
  if (!filterClass.value) return alert('សូមជ្រើសរើសថ្នាក់ជាមុនសិន')
  loading.value = true
  try {
    const res = await api.get(`/classes/${filterClass.value}/students`)
    students.value = res.data?.data || res.data || []
  } catch (err) {
    console.error(err)
    alert('មានបញ្ហាក្នុងការទាញយកទិន្នន័យ')
  } finally {
    loading.value = false
  }
}

const getClassName = (classId) => {
  const cls = studentClasses.value.find(c => c.id === classId)
  return cls ? `${cls.grade_level}${cls.name}` : 'N/A'
}

const toKhmerNumber = (num) => {
  if (num === null || num === undefined) return ''
  const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' }
  return String(num).replace(/[0-9]/g, (s) => map[s])
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const d = new Date(dateString)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  return toKhmerNumber(`${day}/${month}/${year}`)
}

onMounted(loadFilters)

const exportExcel = () => {
  const exportData = students.value.map((s, i) => ({
    'ល.រ': toKhmerNumber(i + 1),
    'ឈ្មោះសិស្ស': s.name_kh || s.name,
    'ភេទ': s.gender === 'male' ? 'ប្រុស' : 'ស្រី',
    'ថ្ងៃខែឆ្នាំកំណើត': formatDate(s.date_of_birth),
    'ថ្នាក់ទី': s.from_class,
    'មកពី': s.pri_school,
    'ផ្សេងៗ': s.note || '',
  }))

  const ws = XLSX.utils.json_to_sheet(exportData)
  ws['!cols'] = [
    { wch: 6 }, { wch: 24 }, { wch: 8 }, { wch: 18 }, { wch: 12 }, { wch: 16 }
  ]
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'បញ្ជីសិស្ស')
  XLSX.writeFile(wb, 'student-list.xlsx')
}

const exportPDF = () => {
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })

  // ក្បាលផ្នែក
  doc.setFontSize(15)
  doc.setFont('helvetica', 'bold')
  doc.text('Student List / បញ្ជីឈ្មោះសិស្ស', 148, 18, { align: 'center' })

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text(`Export: ${new Date().toLocaleDateString()}`, 148, 26, { align: 'center' })

  // ក្បាលតារាង
  const headers = ['ល.រ', 'ឈ្មោះ', 'ភេទ', 'ថ្ងៃខែឆ្នាំ', 'ថ្នាក់', 'មកពី']
  const colWidths = [15, 55, 20, 45, 25, 40]
  const startX = 15
  let y = 36

  doc.setFillColor(37, 99, 235)
  doc.rect(startX, y, colWidths.reduce((a, b) => a + b, 0), 9, 'F')
  doc.setTextColor(255, 255, 255)
  doc.setFontSize(9)
  let x = startX
  headers.forEach((h, i) => {
    doc.text(h, x + 2, y + 6)
    x += colWidths[i]
  })

  // ទិន្នន័យ
  doc.setTextColor(30, 30, 30)
  y += 9
  students.value.forEach((s, idx) => {
    if (idx % 2 === 0) {
      doc.setFillColor(248, 250, 252)
      doc.rect(startX, y, colWidths.reduce((a, b) => a + b, 0), 8, 'F')
    }
    const row = [
      toKhmerNumber(idx + 1),
      s.name_kh || s.name,
      s.gender === 'male' ? 'Pros' : 'Srey',
      formatDate(s.date_of_birth),
      s.from_class,
      s.pri_school
    ]
    x = startX
    row.forEach((cell, i) => {
      doc.text(String(cell || ''), x + 2, y + 5.5)
      x += colWidths[i]
    })
    y += 8
  })

  doc.setFontSize(8)
  doc.setTextColor(120, 120, 120)
  doc.text(`សរុប: ${toKhmerNumber(students.value.length)} នាក់`, startX, y + 8)

  doc.save('student-list.pdf')
}
</script>