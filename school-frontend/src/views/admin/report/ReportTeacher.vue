<template>
  <div class="font-[Battambang] sm:p-2 min-h-screen bg-slate-50/50 text-slate-700">
    <!-- Filter bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-300/80 shadow-sm flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-4 mb-6">
      <div class="w-full flex-1 sm:min-w-[220px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ស្វែងរក</label>
        <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ ឬលេខទូរស័ព្ទ..."
          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500" />
      </div>

      <div class="w-full sm:w-[200px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">មុខវិជ្ជា</label>
        <select v-model="filterSpecialty" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500 cursor-pointer">
          <option value="">-- ទាំងអស់ --</option>
          <option v-for="sub in specialtyOptions" :key="sub" :value="sub">{{ sub }}</option>
        </select>
      </div>

      <div class="w-full sm:w-[180px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ស្ថានភាព</label>
        <select v-model="filterStatus" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 mt-1 outline-none focus:border-indigo-500 cursor-pointer">
          <option value="">-- ទាំងអស់ --</option>
          <option value="1">នៅបង្រៀន</option>
          <option value="0">ឈប់បង្រៀន/ផ្អាក</option>
        </select>
      </div>
    </div>

    <!-- Export buttons -->
    <div class="w-full flex flex-col sm:flex-row justify-end gap-2 my-4">
      <button @click="exportExcel" :disabled="filteredTeachers.length === 0"
        class="w-full sm:w-auto bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
        ទាញយក Excel
      </button>
      <button @click="exportPDF" :disabled="filteredTeachers.length === 0"
        class="w-full sm:w-auto bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
        ទាញយក PDF
      </button>
    </div>

    <!-- Error message -->
    <div v-if="loadError" class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold">
      {{ loadError }}
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="p-12 text-center text-slate-500">កំពុងផ្ទុកទិន្នន័យគ្រូបង្រៀន...</div>

    <!-- Main Table -->
    <div v-else-if="filteredTeachers.length > 0" class="bg-white rounded-2xl border border-slate-300/80 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-slate-100 font-bold text-slate-800 text-lg">បញ្ជីឈ្មោះគ្រូបង្រៀន</div>
      <div class="overflow-auto max-h-[65vh]">
        <table class="w-full text-left border-collapse min-w-[800px]">
          <thead class="bg-slate-50 text-slate-600 text-[15px] uppercase sticky top-0 z-10">
            <tr>
              <th class="px-4 py-4">ល.រ</th>
              <!-- <th class="px-4 py-4">រូបថត</th> -->
              <!-- <th class="px-4 py-4">លេខសម្គាល់</th> -->
              <th class="px-4 py-4">ឈ្មោះ (ខ្មែរ)</th>
              <th class="px-4 py-4">ឈ្មោះឡាតាំង</th>
              <th class="px-4 py-4 text-center">ភេទ</th>
              <th class="px-4 py-4">អ៊ីមែល</th>
              <th class="px-4 py-4">ទូរស័ព្ទ</th>
              <th class="px-4 py-4">មុខវិជ្ជាជំនាញ</th>
              <th class="px-4 py-4 text-center">ស្ថានភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(teacher, index) in filteredTeachers" :key="teacher.id" class="hover:bg-slate-50/50">
              <td class="px-4 py-3 text-sm text-slate-500">{{ index + 1 }}</td>
              <!-- <td class="px-4 py-3">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 border border-slate-200">
                  <img v-if="teacher.photo_url" :src="teacher.photo_url" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-slate-300 text-xs font-bold">
                    {{ initials(teacher.name_kh) }}
                  </div>
                </div>
              </td> -->
              <!-- <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ teacher.teacher_id_card }}</td> -->
              <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ teacher.name_kh }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ teacher.name_en }}</td>
              <td class="px-4 py-3 text-sm text-center">{{ getGenderKh(teacher.gender) }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ teacher.email }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ teacher.phone || '-' }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatSpecialty(teacher.specialty) }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-3 py-1 rounded-full text-[13px] font-bold',
                  teacher.status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600']">
                  {{ teacher.status == 1 ? 'នៅបង្រៀន' : 'ឈប់បង្រៀន/ព្យួរ' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="p-12 text-center bg-white rounded-2xl border border-dashed text-slate-400">
      មិនមានទិន្នន័យគ្រូបង្រៀនត្រូវនឹងលក្ខខណ្ឌនេះទេ
    </div>

    <!-- UI Signature Preview at Bottom -->
    <div v-if="filteredTeachers.length > 0" class="mt-10 flex flex-col items-center md:items-end leading-8">
      <div class="text-center">
        <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
        <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
        <p class="font-moul mt-1">នាយកសាលា</p>
      </div>
      <!-- <div class="flex justify-between -mt-36">
        <div class="text-center font-moul">
          <p>បានឃើញ និងឯកភាព</p>
          <p>នាយកសាលា</p>
        </div>
      </div> -->
    </div>

    <!-- ========================================================= -->
    <!-- Hidden Official Letterhead Export Area (For PDF Export) -->
    <!-- ========================================================= -->
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

      <h2 class="doc-title">បញ្ជីឈ្មោះគ្រូបង្រៀន</h2>

      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width:8%">ល.រ</th>
            <th style="width:23%">ឈ្មោះ (ខ្មែរ)</th>
            <th style="width:23%">ឈ្មោះឡាតាំង</th>
            <th style="width:10%">ភេទ</th>
            <th style="width:16%">ទូរស័ព្ទ</th>
            <th style="width:20%">មុខវិជ្ជាជំនាញ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(teacher, index) in filteredTeachers" :key="'pdf-' + (teacher.id || index)">
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td style="text-align:left; font-weight:bold;">{{ teacher.name_kh || '-' }}</td>
            <td style="text-align:left;">{{ teacher.name_en || '-' }}</td>
            <td>{{ getGenderKh(teacher.gender) }}</td>
            <td>{{ teacher.phone || '-' }}</td>
            <td style="text-align:left;">{{ formatSpecialty(teacher.specialty) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបចំនួនគ្រូបង្រៀន៖ {{ toKhmerNumber(filteredTeachers.length) }} នាក់ 
          &nbsp;&nbsp;&nbsp;&nbsp;
          ស្រី៖ {{ toKhmerNumber(totalFemale) }} នាក់ 
          &nbsp;&nbsp;&nbsp;&nbsp;
          កំពុងបង្រៀន៖ {{ toKhmerNumber(totalActive) }} នាក់ 
          &nbsp;&nbsp;&nbsp;&nbsp;
          ឈប់បង្រៀន៖ {{ toKhmerNumber(totalInactive) }} នាក់
        </strong>
      </div>

      <div class="doc-date">
        <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
        <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
        <p class="moul">នាយកសាលា</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../../services/authService'
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'

const teachers = ref([])
const isLoading = ref(false)
const loadError = ref('')
const printArea = ref(null)

const searchQuery = ref('')
const filterSpecialty = ref('')
const filterStatus = ref('')

// អាចកែសម្រួលឈ្មោះស្ថាប័ន / សាលារបស់អ្នកនៅទីនេះ
const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

// បញ្ជីមុខវិជ្ជាជំនាញ
const specialtyOptions = ['គណិតវិទ្យា', 'រូបវិទ្យា', 'គីមីវិទ្យា', 'ជីវវិទ្យា', 'អក្សរសាស្ត្រខ្មែរ', 'ភាសាអង់គ្លេស', 'ភូមិវិទ្យា', 'ប្រវត្តិវិទ្យា', 'សីលធម៌', 'ផែនដី', 'គេហ:']

const unwrapArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

const getGenderKh = (gender) => {
  if (!gender) return '-'
  const g = String(gender).toLowerCase()
  if (g === 'male' || g === 'm') return 'ប្រុស'
  if (g === 'female' || g === 'f') return 'ស្រី'
  return '-'
}

const formatSpecialty = (specialty) => {
  if (!specialty) return '-'
  let list = specialty
  if (typeof specialty === 'string') {
    try { list = JSON.parse(specialty) } catch { list = [specialty] }
  }
  return Array.isArray(list) ? list.join(', ') : String(list)
}

const toKhmerNumber = (num) => {
  if (num === null || num === undefined || num === '') return ''
  const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' }
  return String(num).replace(/[0-9]/g, (s) => map[s])
}

const sanitizeFileName = (name) => name.replace(/[\\/:*?"<>|]/g, '-').trim()

const filteredTeachers = computed(() => {
  return teachers.value.filter(t => {
    const query = searchQuery.value.trim().toLowerCase()
    const matchesSearch = !query ||
      t.name_kh?.toLowerCase().includes(query) ||
      t.name_en?.toLowerCase().includes(query) ||
      t.phone?.toLowerCase().includes(query)

    const matchesSpecialty = !filterSpecialty.value ||
      formatSpecialty(t.specialty).includes(filterSpecialty.value)

    const matchesStatus = filterStatus.value === '' ||
      String(t.status) === filterStatus.value

    return matchesSearch && matchesSpecialty && matchesStatus
  })
})

const totalFemale = computed(() => {
  return filteredTeachers.value.filter(t => {
    const g = String(t.gender).toLowerCase()
    return g === 'female' || g === 'f'
  }).length
})

const totalActive = computed(() => filteredTeachers.value.filter(t => t.status == 1).length)
const totalInactive = computed(() => filteredTeachers.value.filter(t => t.status != 1).length)

const fetchTeachers = async () => {
  isLoading.value = true
  loadError.value = ''
  try {
    const res = await api.get('/teachers')
    teachers.value = unwrapArray(res.data)
  } catch (err) {
    console.error('fetchTeachers failed:', err)
    loadError.value = 'មិនអាចទាញយកទិន្នន័យគ្រូបង្រៀនបានទេ'
  } finally {
    isLoading.value = false
  }
}

// ----- Styles for Excel (Same style as ReportResult / ReportAttendance) -----
const THIN_BORDER = { style: 'thin', color: { rgb: '000000' } }
const ALL_BORDERS = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER }

const kingdomStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 15 },
  alignment: { horizontal: 'center', vertical: 'center' },
}
const mottoStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 },
  alignment: { horizontal: 'center', vertical: 'center' },
}
const officeStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 },
  alignment: { horizontal: 'left', vertical: 'center' },
}
const titleStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 14 },
  alignment: { horizontal: 'center', vertical: 'center' },
}
const tableHeaderStyle = {
  font: { name: 'Khmer OS Battambang', bold: true, sz: 11 },
  alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
  border: ALL_BORDERS,
  fill: { fgColor: { rgb: 'F1F5F9' } }
}
const tableCellStyle = {
  font: { name: 'Khmer OS Battambang', sz: 11 },
  alignment: { horizontal: 'center', vertical: 'center' },
  border: ALL_BORDERS,
}
const tableCellLeftStyle = {
  font: { name: 'Khmer OS Battambang', sz: 11 },
  alignment: { horizontal: 'left', vertical: 'center' },
  border: ALL_BORDERS,
}
const totalStyle = {
  font: { name: 'Khmer OS Battambang', bold: true, sz: 11 },
  alignment: { horizontal: 'left', vertical: 'center' },
}
const dateLineStyle = {
  font: { name: 'Khmer OS Battambang', sz: 11 },
  alignment: { horizontal: 'right', vertical: 'center' },
}
const dateMoulRightStyle = {
  font: { name: 'Khmer OS Muol Light', sz: 11 },
  alignment: { horizontal: 'right', vertical: 'center' },
}
const approvalMoulStyle = {
  font: { name: 'Khmer OS Muol Light', sz: 11 },
  alignment: { horizontal: 'center', vertical: 'center' },
}

const styleCell = (ws, r, c, style) => {
  const addr = XLSX.utils.encode_cell({ r, c })
  if (!ws[addr]) ws[addr] = { t: 's', v: '' }
  ws[addr].s = style
}

// ----- Export: Excel -----
const exportExcel = () => {
  if (filteredTeachers.value.length === 0) return

  // មិនមាន រូបថត, លេខសម្គាល់, អ៊ីមែល ទេ
  const tableHeader = ['ល.រ', 'ឈ្មោះ (ខ្មែរ)', 'ឈ្មោះឡាតាំង', 'ភេទ', 'ទូរស័ព្ទ', 'មុខវិជ្ជាជំនាញ', 'ស្ថានភាព']
  const colCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine1.value],
    [ministryLine2.value],
    ['បញ្ជីឈ្មោះគ្រូបង្រៀន'],
    [],
  ]

  const tableRows = filteredTeachers.value.map((t, index) => {
    return [
      toKhmerNumber(index + 1),
      t.name_kh || '',
      t.name_en || '',
      getGenderKh(t.gender),
      t.phone || '',
      formatSpecialty(t.specialty),
      t.status == 1 ? 'នៅបង្រៀន' : 'ឈប់បង្រៀន/ផ្អាក'
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
    `សរុបគ្រូបង្រៀន៖ ${toKhmerNumber(filteredTeachers.value.length)} នាក់    ស្រី៖ ${toKhmerNumber(totalFemale.value)} នាក់    កំពុងបង្រៀន៖ ${toKhmerNumber(totalActive.value)} នាក់    ឈប់បង្រៀន៖ ${toKhmerNumber(totalInactive.value)} នាក់`
  ])
  styleQueue.push({ r: summaryRowIdx, c: 0, style: totalStyle })
  mergeQueue.push({ r: summaryRowIdx, c1: 0, c2: colCount - 1 })

  pushRow([])

  const dateRow1Idx = pushRow(['ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............'])
  const dateRow2Idx = pushRow(['នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'])
  const teacherTitleIdx = pushRow(['អ្នករៀបចំរបាយការណ៍'])
  const teacherNameIdx = pushRow(['...........................................'])

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
      // តម្រឹមឆ្វេងសម្រាប់ ឈ្មោះខ្មែរ (1), ឈ្មោះឡាតាំង (2), និងមុខវិជ្ជា (5)
      const styleToApply = (c === 1 || c === 2 || c === 5) ? tableCellLeftStyle : tableCellStyle
      styleCell(ws, r, c, styleToApply)
    }
  }

  styleQueue.forEach(({ r, c, style }) => styleCell(ws, r, c, style))

  ws['!cols'] = [
    { wch: 8 },  // ល.រ
    { wch: 24 }, // ឈ្មោះ (ខ្មែរ)
    { wch: 24 }, // ឈ្មោះឡាតាំង
    { wch: 10 }, // ភេទ
    { wch: 18 }, // ទូរស័ព្ទ
    { wch: 28 }, // មុខវិជ្ជាជំនាញ
    { wch: 16 }  // ស្ថានភាព
  ]

  ws['!rows'] = [{ hpt: 26 }]

  ws['!pageSetup'] = { orientation: 'portrait', fitToWidth: 1, fitToHeight: 0 }
  ws['!printOptions'] = { horizontalCentered: true }
  ws['!margins'] = { left: 0.5, right: 0.5, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'គ្រូបង្រៀន')
  XLSX.writeFile(wb, `${sanitizeFileName('បញ្ជីឈ្មោះគ្រូបង្រៀន')}.xlsx`)
}

// ----- Export: PDF -----
const exportPDF = async () => {
  if (filteredTeachers.value.length === 0) return
  if (!printArea.value) return

  const opt = {
    margin: [12, 10, 12, 10],
    filename: `${sanitizeFileName('បញ្ជីឈ្មោះគ្រូបង្រៀន')}.pdf`,
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

onMounted(fetchTeachers)
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
  text-align: right;
  margin-right: 40px;
}
</style>