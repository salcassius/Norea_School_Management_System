<template>
  <div class="font-[Battambang] p-1 sm:p-2 md:p-2 min-h-screen bg-slate-50/50 text-slate-700">
    <!-- Filter bar -->
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

    <!-- Export Buttons -->
    <div class="w-full flex justify-end gap-2 my-4">
      <button @click="exportExcel" :disabled="reportData.length === 0"
        class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
        នាំចេញ Excel
      </button>
      <button @click="exportPDF" :disabled="reportData.length === 0"
        class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
        នាំចេញ PDF
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="p-12 text-center text-slate-500">កំពុងគណនាទិន្នន័យប្រចាំឆ្នាំ...</div>

    <!-- Main Content -->
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
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="student in reportData" :key="student.student_id" class="hover:bg-slate-50/50">
                <td class="px-6 py-4 font-semibold text-slate-700">{{ student.student_name }}</td>
                <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ student.total_present }}</td>
                <td class="px-6 py-4 text-center font-bold text-rose-600">{{ student.total_absent }}</td>
                <td class="px-6 py-4 text-center font-bold text-blue-600">{{ student.total_excused || 0 }}</td>
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

    <!-- Empty State -->
    <div v-else class="p-12 text-center bg-white rounded-2xl border border-dashed text-slate-400">
      សូមជ្រើសរើសថ្នាក់ និងឆ្នាំសិក្សា ដើម្បីមើលរបាយការណ៍
    </div>

    <!-- Summary & Signature Preview on Web -->
    <div v-if="reportData.length > 0" class="mt-10 text-right leading-8 justify-end">
      <div class="flex flex-col items-end">
        <div class="text-center">
          <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
          <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
          <p class="moul">គ្រូបន្ទប់ថ្នាក់</p>
          <br>
          <p class="mt-12 font-bold text-right mr-12">{{ selectedClassTeacherName }}</p>
        </div>
      </div>
      <div class="flex justify-between -mt-36">
        <div class="text-center font-moul">
          <p>បានឃើញ និងឯកភាព</p>
          <p>នាយកសាលា</p>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- Hidden Official Letterhead Export Area (For PDF Export - Formal Style) -->
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
        <p>{{ ministryLine1 }}</p>
        <p>{{ ministryLine2 }}</p>
      </div>

      <h2 class="doc-title">
        បញ្ជីវត្តមានសិស្សប្រចាំឆ្នាំ ថ្នាក់ទី{{ selectedClassLabel }}
        <span class="block text-[13px] font-normal mt-1">ឆ្នាំសិក្សា {{ selectedYearLabel }}</span>
      </h2>

      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width:8%">ល.រ</th>
            <th style="width:38%">ឈ្មោះសិស្ស</th>
            <th style="width:18%">វត្តមាន (ថ្ងៃ)</th>
            <th style="width:18%">អវត្តមាន (ថ្ងៃ)</th>
            <th style="width:18%">ច្បាប់ (ថ្ងៃ)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, index) in reportData" :key="'pdf-' + student.student_id">
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td style="text-align:left; font-weight:bold;">{{ student.student_name }}</td>
            <td style="color:#059669; font-weight:bold;">{{ toKhmerNumber(student.total_present) }}</td>
            <td style="color:#e11d48; font-weight:bold;">{{ toKhmerNumber(student.total_absent) }}</td>
            <td style="color:#2563eb; font-weight:bold;">{{ toKhmerNumber(student.total_excused || 0) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបចំនួនសិស្ស៖ {{ toKhmerNumber(reportData.length) }} នាក់ 
          &nbsp;&nbsp;&nbsp;&nbsp;
          អវត្តមានសរុប៖ {{ toKhmerNumber(totalAbsent) }} ថ្ងៃ 
          &nbsp;&nbsp;&nbsp;&nbsp;
          ច្បាប់សរុប៖ {{ toKhmerNumber(totalExcused) }} ថ្ងៃ
        </strong>
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
import api from '../../../services/authService'
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'

const classes = ref([])
const academicYears = ref([])
const reportData = ref([])
const topAbsentees = ref([])
const isLoading = ref(false)
const printArea = ref(null)

const filter = ref({
  class_id: '',
  year_id: ''
})

const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

const selectedClassObj = computed(() => classes.value.find(c => c.id === filter.value.class_id))
const selectedYearObj = computed(() => academicYears.value.find(y => y.id === filter.value.year_id))

const selectedClassLabel = computed(() => {
  const cls = selectedClassObj.value
  return cls ? `${cls.grade_level}${cls.name}` : ''
})

const selectedYearLabel = computed(() => {
  const yr = selectedYearObj.value
  return yr ? yr.year_name : ''
})

const selectedClassTeacherName = computed(() => {
  const cls = selectedClassObj.value
  if (!cls) return '...........................................'
  return cls.teacher?.name_kh || cls.teacher?.name || cls.teacher_name || '...........................................'
})

const totalAbsent = computed(() => reportData.value.reduce((sum, s) => sum + Number(s.total_absent || 0), 0))
const totalExcused = computed(() => reportData.value.reduce((sum, s) => sum + Number(s.total_excused || 0), 0))

const toKhmerNumber = (num) => {
  if (num === null || num === undefined || num === '') return ''
  const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' }
  return String(num).replace(/[0-9]/g, (s) => map[s])
}

const sanitizeFileName = (name) => name.replace(/[\\/:*?"<>|]/g, '-').trim()

const fetchReport = async () => {
  if (!filter.value.class_id || !filter.value.year_id) return
  isLoading.value = true
  try {
    const res = await api.get('/attendance/report', { params: filter.value })
    reportData.value = res.data?.data || res.data || []
    topAbsentees.value = res.data?.top_absentees || []
  } catch (err) {
    console.error('Error fetching report:', err)
  } finally {
    isLoading.value = false
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
const approvalMoulStyle = { font: { name: 'Khmer OS Muol Light', sz: 11 }, alignment: { horizontal: 'center', vertical: 'center' } }

const styleCell = (ws, r, c, style) => {
  const addr = XLSX.utils.encode_cell({ r, c })
  if (!ws[addr]) ws[addr] = { t: 's', v: '' }
  ws[addr].s = style
}

// ----- Export: Excel -----
const exportExcel = () => {
  if (reportData.value.length === 0) return

  const tableHeader = ['ល.រ', 'ឈ្មោះសិស្ស', 'វត្តមាន (ថ្ងៃ)', 'អវត្តមាន (ថ្ងៃ)', 'ច្បាប់ (ថ្ងៃ)']
  const colCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine1.value],
    [ministryLine2.value],
    [`បញ្ជីវត្តមានសិស្សប្រចាំឆ្នាំ ថ្នាក់ទី${selectedClassLabel.value}`],
    [`ឆ្នាំសិក្សា ${selectedYearLabel.value}`],
    [],
  ]

  const tableRows = reportData.value.map((s, index) => {
    return [
      toKhmerNumber(index + 1),
      s.student_name || '',
      toKhmerNumber(s.total_present || 0),
      toKhmerNumber(s.total_absent || 0),
      toKhmerNumber(s.total_excused || 0),
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
    `សរុបចំនួនសិស្ស៖ ${toKhmerNumber(reportData.value.length)} នាក់    អវត្តមានសរុប៖ ${toKhmerNumber(totalAbsent.value)} ថ្ងៃ    ច្បាប់សរុប៖ ${toKhmerNumber(totalExcused.value)} ថ្ងៃ`
  ])
  styleQueue.push({ r: summaryRowIdx, c: 0, style: totalStyle })
  mergeQueue.push({ r: summaryRowIdx, c1: 0, c2: colCount - 1 })

  pushRow([])

  const dateRow1Idx = pushRow(['', '', 'ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............'])
  const dateRow2Idx = pushRow(['', '', 'នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'])
  const teacherTitleIdx = pushRow(['', '', 'គ្រូបន្ទប់ថ្នាក់'])
  const teacherNameIdx = pushRow(['', '', selectedClassTeacherName.value])

  mergeQueue.push({ r: dateRow1Idx, c1: 2, c2: colCount - 1 })
  mergeQueue.push({ r: dateRow2Idx, c1: 2, c2: colCount - 1 })
  mergeQueue.push({ r: teacherTitleIdx, c1: 2, c2: colCount - 1 })
  mergeQueue.push({ r: teacherNameIdx, c1: 2, c2: colCount - 1 })
  styleQueue.push({ r: dateRow1Idx, c: 2, style: dateLineStyle })
  styleQueue.push({ r: dateRow2Idx, c: 2, style: dateLineStyle })
  styleQueue.push({ r: teacherTitleIdx, c: 2, style: dateMoulRightStyle })
  styleQueue.push({ r: teacherNameIdx, c: 2, style: dateLineStyle })
  pushRow([])

  const approvalRow1Idx = pushRow(['បានឃើញ និងឯកភាព'])
  const approvalRow2Idx = pushRow(['នាយកសាលា'])

  const leftBlockEndCol = Math.min(1, colCount - 1)
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
    { s: { r: 5, c: 0 }, e: { r: 5, c: colCount - 1 } },
    { s: { r: 6, c: 0 }, e: { r: 6, c: colCount - 1 } },
    ...mergeQueue.map(m => ({ s: { r: m.r, c: m.c1 }, e: { r: m.r, c: m.c2 } })),
  ]

  styleCell(ws, 0, 0, kingdomStyle)
  styleCell(ws, 1, 0, mottoStyle)
  styleCell(ws, 3, 0, officeStyle)
  styleCell(ws, 4, 0, officeStyle)
  styleCell(ws, 5, 0, titleStyle)
  styleCell(ws, 6, 0, titleStyle)

  for (let c = 0; c < colCount; c++) {
    styleCell(ws, tableHeaderRowIdx, c, tableHeaderStyle)
  }

  for (let r = tableStartRowIdx; r <= tableEndRowIdx; r++) {
    for (let c = 0; c < colCount; c++) {
      const isLeftAlign = [1].includes(c) // ឈ្មោះសិស្ស តម្រឹមឆ្វេង
      styleCell(ws, r, c, isLeftAlign ? tableCellLeftStyle : tableCellStyle)
    }
  }

  styleQueue.forEach(({ r, c, style }) => styleCell(ws, r, c, style))

  ws['!cols'] = [
    { wch: 8 },  // ល.រ
    { wch: 32 }, // ឈ្មោះសិស្ស
    { wch: 16 }, // វត្តមាន
    { wch: 16 }, // អវត្តមាន
    { wch: 16 }, // ច្បាប់
  ]

  ws['!rows'] = [{ hpt: 26 }]

  ws['!pageSetup'] = { orientation: 'portrait', fitToWidth: 1, fitToHeight: 0 }
  ws['!printOptions'] = { horizontalCentered: true }
  ws['!margins'] = { left: 0.5, right: 0.5, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'វត្តមាន')
  XLSX.writeFile(wb, `${sanitizeFileName('របាយការណ៍វត្តមាន_ថ្នាក់ទី_' + selectedClassLabel.value)}.xlsx`)
}

// ----- Export: PDF (Khmer Unicode Support via html2pdf) -----
const exportPDF = async () => {
  if (reportData.value.length === 0) return
  if (!printArea.value) return

  const opt = {
    margin: [12, 10, 12, 10],
    filename: `${sanitizeFileName('របាយការណ៍វត្តមាន_ថ្នាក់ទី_' + selectedClassLabel.value)}.pdf`,
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
    console.error('PDF export failed:', err)
  } finally {
    printArea.value.style.display = 'none'
  }
}

onMounted(async () => {
  try {
    const [classRes, yearRes] = await Promise.all([
      api.get('/classes'),
      api.get('/years')
    ])
    classes.value = classRes.data?.data || classRes.data || []
    academicYears.value = yearRes.data?.data || yearRes.data || []
    const active = academicYears.value.find(y => y.is_active)
    if (active) filter.value.year_id = active.id
  } catch (err) {
    console.error('Initialization error:', err)
  }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

/* --- Hidden PDF Export Template Styles --- */
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
  font-size: 13px;
  margin: 14px 0 18px;
}

.doc-office p {
  font-family: 'Moul', serif;
  font-size: 13px;
  line-height: 1.8;
  margin: 2px 0;
}

.doc-title {
  font-family: 'Moul', Arial, sans-serif;
  font-weight: 500;
  font-size: 15px;
  text-align: center;
  margin: 10px 0 16px;
}

.pdf-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
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
  background: #f8fafc;
}

.doc-summary {
  margin-top: 15px;
  font-size: 13px;
  font-weight: bold;
}

.doc-date {
  text-align: right;
  font-size: 13px;
  margin-top: 18px;
  line-height: 1.8;
}

.teacher-name {
  margin-top: 50px;
  font-weight: bold;
  text-align: right;
  margin-right: 10px;
}

.doc-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-top: -120px;
}

.signature-left {
  width: 45%;
  text-align: center;
}

.moul {
  font-family: 'Moul', serif;
  font-size: 13px;
}
</style>