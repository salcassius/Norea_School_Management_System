<template>
    <div class="p-6 bg-gray-50 min-h-screen font-['Battambang'] text-gray-800">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">គ្រប់គ្រងរបាយការណ៍</h1>
        </div>

        <div class="border-b border-gray-200 mb-6">
            <div class="flex space-x-8">
                <button v-for="tab in ['student', 'teacher']" :key="tab" @click="activeMainTab = tab" :class="[
                    'pb-3 text-lg font-medium transition-colors duration-200',
                    activeMainTab === tab ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700'
                ]">
                    {{ tab === 'student' ? 'របាយការណ៍សិស្ស' : 'របាយការណ៍គ្រូ' }}
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mb-6">
            <button v-for="report in reportTypes" :key="report.id" @click="activeReportType = report.id" :class="[
                'px-5 py-2 rounded-lg text-sm transition-all border',
                activeReportType === report.id ? 'bg-blue-50 border-blue-400 text-blue-600 font-medium shadow-sm' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
            ]">
                {{ report.name }}
            </button>
        </div>

        <div class="bg-blue-600 text-white rounded-t-xl p-5 shadow-sm">
            <p class="text-sm font-medium mb-3 opacity-90">តម្រងស្វែងរក</p>
            <div class="flex flex-col md:flex-row items-end gap-6">
                <div class="w-full md:w-64">
                    <label class="block text-sm font-medium mb-1.5 opacity-90">ឆ្នាំសិក្សា</label>
                    <select v-model="filterYear"
                        class="w-full text-gray-800 rounded-lg px-3 py-2 text-sm cursor-pointer focus:ring-2 focus:ring-blue-300">
                        <option value="" disabled>ជ្រើសរើសឆ្នាំ</option>
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.year_name }}
                        </option>
                    </select>
                </div>
                <div class="w-full md:w-64">
                    <label class="block text-sm font-medium mb-1.5 opacity-90">ថ្នាក់</label>
                    <select v-model="filterClass"
                        class="w-full text-gray-800 rounded-lg px-3 py-2 text-sm cursor-pointer focus:ring-2 focus:ring-blue-300">
                        <option value="" disabled>ជ្រើសរើសថ្នាក់</option>
                        <option v-for="cls in studentClasses" :key="cls.id" :value="cls.id">{{ cls.grade_level }}{{
                            cls.name }}</option>
                    </select>
                </div>
                <button @click="fetchReportData" :disabled="loading"
                    class="bg-emerald-500 hover:bg-emerald-600 px-6 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    {{ loading ? 'កំពុងផ្ទុក...' : 'បង្ហាញទិន្នន័យ' }}
                </button>
            </div>
        </div>

        <div class="bg-white rounded-b-xl border border-gray-200 shadow-sm overflow-hidden min-h-[300px]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-700 text-sm">
                            <th class="py-3 px-4 text-center">ល.រ</th>
                            <th class="py-3 px-4">ឈ្មោះសិស្ស</th>
                            <th class="py-3 px-4">ភេទ</th>
                            <th class="py-3 px-4">ថ្ងៃ ខែ ឆ្នាំកំណើត</th>
                            <th class="py-3 px-4">ថ្នាក់ទី</th>
                            <th class="py-3 px-4">មកពី</th>
                            <th class="py-3 px-4">ផ្សេងៗ</th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-center text-gray-500">{{ toKhmerNumber(index + 1) }}</td>
                            <td class="py-3 px-4 font-medium text-gray-900">{{ student.name_kh || student.name }}</td>
                            <td class="py-3 px-4">
                                {{ student.gender === 'male' ? 'ប្រុស' : 'ស្រី' }}
                            </td>
                            <td class="py-3 px-4">{{ formatDate(student.date_of_birth) }}</td>
                            <td class="py-3 px-4 font-medium text-gray-900">{{ student.from_class}}</td>
                            <td class="py-3 px-4 font-medium text-gray-900">{{ student.pri_school}}</td>
                        </tr>
                        <tr v-if="students.length === 0 && !loading">
                            <td colspan="5" class="py-10 text-center text-gray-400">មិនទាន់មានទិន្នន័យសម្រាប់បង្ហាញ</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-white border-t border-gray-100 flex justify-between items-center">
                <div class="text-sm text-gray-600">ទិន្នន័យសរុប៖ <span class="font-bold text-gray-900">{{
                    toKhmerNumber(students.length) }}</span> នាក់</div>
                <div class="space-x-3">
                    <!-- <button @click="exportPDF" class="bg-white border border-blue-400 text-blue-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">ទាញយក
                        PDF</button> -->
                    <button @click="exportExcel" class="bg-white border border-green-300 text-green-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">នាំចេញ
                        Excel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/authService'
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'

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
    { id: 2, name: 'របាយការណ៍ស្រង់ពិន្ទុ' },
    { id: 3, name: 'របាយការណ៍វត្តមាន' },
    { id: 4, name: 'លទ្ធផលសិក្សា' }
])

const loadFilters = async () => {
    try {
        const [years, classes] = await Promise.all([api.get('/years'), api.get('/classes')])
        academicYears.value = years.data.data || years.data
        studentClasses.value = classes.data.data || classes.data
    } catch (err) { console.error(err) }
}

const fetchReportData = async () => {
    if (!filterClass.value) return alert('សូមជ្រើសរើសថ្នាក់ជាមុនសិន')
    loading.value = true
    try {
        // ត្រូវប្រាកដថា API នេះត្រឡប់ទិន្នន័យសិស្សរួមជាមួយ Class info
        const res = await api.get(`/classes/${filterClass.value}/students`)
        students.value = res.data.data || res.data
    } catch (err) {
        console.error(err)
        alert('មានបញ្ហាក្នុងការទាញយកទិន្នន័យ')
    } finally {
        loading.value = false
    }
}

const getClassName = (classId) => {
  const cls = studentClasses.value.find(c => c.id === classId);
  return cls ? `${cls.grade_level}${cls.name}` : 'N/A';
}

const toKhmerNumber = (num) => {
    const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' };
    return String(num).replace(/[0-9]/g, (s) => map[s]);
}

const formatDate = (dateString) => {
  if (!dateString) return '';
  
  const d = new Date(dateString);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return toKhmerNumber(`${day}/${month}/${year}`);
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