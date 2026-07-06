<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">
    <div class="bg-white p-5 rounded-2xl border border-slate-300/80 shadow-sm flex flex-wrap items-end gap-4 mb-6">
      <div class="w-full sm:w-[220px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ថ្នាក់រៀន</label>
        <select v-model="filter.class_id" @change="fetchReport" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500 cursor-pointer">
          <option value="" disabled>--- ជ្រើសរើសថ្នាក់ ---</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">ថ្នាក់ទី៖ {{ cls.grade_level }} {{ cls.name }}</option>
        </select>
      </div>

      <div class="w-full sm:w-[220px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ឆ្នាំសិក្សា</label>
        <select v-model="filter.year_id" @change="fetchReport" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500 cursor-pointer">
          <option value="" disabled>--- ជ្រើសរើសឆ្នាំ ---</option>
          <option v-for="yr in academicYears" :key="yr.id" :value="yr.id">{{ yr.year_name }}</option>
        </select>
      </div>

      
    </div>

    <div class="w-full flex justify-end gap-2 my-4">
  <button 
    @click="exportExcel" 
    :disabled="reportData.length === 0"
    class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed"
  >
    នាំចេញ Excel
  </button>
  <button 
    @click="exportPDF" 
    :disabled="reportData.length === 0"
    class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed"
  >
    នាំចេញ PDF
  </button>
</div>
      
    <div v-if="isLoading" class="p-12 text-center text-slate-500">កំពុងគណនាទិន្នន័យប្រចាំឆ្នាំ...</div>

    <div v-else-if="reportData.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-300/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 font-bold text-slate-800">របាយការណ៍វត្តមានសិស្ស (ប្រចាំឆ្នាំ)</div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 text-slate-500 text-[14px] uppercase">
              <tr>
                <th class="px-6 py-4">ឈ្មោះសិស្ស</th>
                <th class="px-6 py-4 text-center">វត្តមាន</th>
                <th class="px-6 py-4 text-center">អវត្តមាន</th>
                <th class="px-6 py-4 text-center">ច្បាប់</th>
                <!-- <th class="px-6 py-4 text-center">ភាគរយ</th> -->
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="student in reportData" :key="student.student_id" class="hover:bg-slate-50/50">
                <td class="px-6 py-4 font-semibold text-slate-700">{{ student.student_name }}</td>
                <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ student.total_present }}</td>
                <td class="px-6 py-4 text-center font-bold text-rose-600">{{ student.total_absent }}</td>
                <td class="px-6 py-4 text-center font-bold text-blue-600">{{ student.total_excused || 0 }}</td>
                <!-- <td class="px-6 py-4 text-center">
                  <span :class="['px-3 py-1 rounded-full text-[11px] font-bold', student.percentage >= 90 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600']">
                    {{ student.percentage }}%
                  </span>
                </td> -->
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
      សូមជ្រើសរើសថ្នាក់ និងឆ្នាំសិក្សា ដើម្បីមើលរបាយការណ៍
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../services/authService'
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'

const classes = ref([])
const academicYears = ref([])
const reportData = ref([])
const topAbsentees = ref([])
const isLoading = ref(false)

const filter = ref({
  class_id: '',
  year_id: ''
})

const fetchReport = async () => {
  if (!filter.value.class_id || !filter.value.year_id) return
  isLoading.value = true
  try {
    // ហៅ API ដោយផ្ញើ filter ថ្មី (គ្មានខែ)
    const res = await api.get('/attendance/report', { params: filter.value })
    reportData.value = res.data.data || []
    topAbsentees.value = res.data.top_absentees || []
  } catch (err) {
    console.error('Error fetching report:', err)
  } finally {
    isLoading.value = false
  }
}

// ----- Export: Excel -----
const exportExcel = () => {
  if (reportData.value.length === 0) return

  const exportData = reportData.value.map((s, index) => ({
    'ល.រ': index + 1,
    'ឈ្មោះសិស្ស': s.student_name,
    'វត្តមាន': s.total_present,
    'អវត្តមាន': s.total_absent,
    'ច្បាប់': s.total_excused || 0,
    'ភាគរយ (%)': s.percentage
  }))

  const ws = XLSX.utils.json_to_sheet(exportData)
  ws['!cols'] = [
    { wch: 6 }, { wch: 24 }, { wch: 10 }, { wch: 10 }, { wch: 10 }, { wch: 12 }
  ]
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'វត្តមាន')
  XLSX.writeFile(wb, 'attendance-report.xlsx')
}

// ----- Export: PDF -----
const exportPDF = () => {
  if (reportData.value.length === 0) return

  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })

  // Header (Latin only — jsPDF's built-in fonts don't render Khmer glyphs)
  doc.setFontSize(15)
  doc.setFont('helvetica', 'bold')
  doc.text('Annual Attendance Report', 148, 18, { align: 'center' })

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text(`Export: ${new Date().toLocaleDateString()}`, 148, 26, { align: 'center' })

  const headers = ['No', 'Name', 'Present', 'Absent', 'Excused', 'Percentage']
  const colWidths = [15, 60, 30, 30, 30, 35]
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

  doc.setTextColor(30, 30, 30)
  y += 9
  reportData.value.forEach((s, idx) => {
    if (idx % 2 === 0) {
      doc.setFillColor(248, 250, 252)
      doc.rect(startX, y, colWidths.reduce((a, b) => a + b, 0), 8, 'F')
    }
    const row = [
      idx + 1,
      s.student_name, // Note: renders blank/garbled with default jsPDF fonts (no Khmer glyphs)
      s.total_present,
      s.total_absent,
      s.total_excused || 0,
      `${s.percentage}%`
    ]
    x = startX
    row.forEach((cell, i) => {
      doc.text(String(cell ?? ''), x + 2, y + 5.5)
      x += colWidths[i]
    })
    y += 8
  })

  doc.setFontSize(8)
  doc.setTextColor(120, 120, 120)
  doc.text(`Total students: ${reportData.value.length}`, startX, y + 8)

  // Second page: top absentees, if any
  if (topAbsentees.value.length > 0) {
    doc.addPage()
    doc.setFontSize(14)
    doc.setFont('helvetica', 'bold')
    doc.setTextColor(30, 30, 30)
    doc.text('Top Absentees', 148, 18, { align: 'center' })

    let ty = 32
    doc.setFontSize(10)
    doc.setFont('helvetica', 'normal')
    topAbsentees.value.forEach((top, idx) => {
      doc.text(`${idx + 1}. ${top.student_name} — ${top.total_absent} days absent`, 20, ty)
      ty += 7
    })
  }

  doc.save('attendance-report.pdf')
}

onMounted(async () => {
  try {
    const [classRes, yearRes] = await Promise.all([
      api.get('/classes'),
      api.get('/years')
    ])
    classes.value = classRes.data?.data || classRes.data
    academicYears.value = yearRes.data?.data || yearRes.data
    const active = academicYears.value.find(y => y.is_active)
    if (active) filter.value.year_id = active.id
  } catch (err) {
    console.error('Initialization error:', err)
  }
})
</script>