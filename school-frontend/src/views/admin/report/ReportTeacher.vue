<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">
    <div class="bg-white p-5 rounded-2xl border border-slate-300/80 shadow-sm flex flex-wrap items-end gap-4 mb-6">
      <div class="flex-1 min-w-[220px]">
        <label class="text-[14px] font-bold text-slate-600 uppercase">ស្វែងរក</label>
        <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ ឬលេខសម្គាល់..."
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

    <div class="w-full flex justify-end gap-2 my-4">
        <button @click="exportExcel" :disabled="filteredTeachers.length === 0"
          class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
          នាំចេញ Excel
        </button>
        <button @click="exportPDF" :disabled="filteredTeachers.length === 0"
          class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
          នាំចេញ PDF
        </button>
      </div>

    <div v-if="loadError" class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold">
      {{ loadError }}
    </div>

    <div v-if="isLoading" class="p-12 text-center text-slate-500">កំពុងផ្ទុកទិន្នន័យគ្រូបង្រៀន...</div>

    <div v-else-if="filteredTeachers.length > 0" class="bg-white rounded-2xl border border-slate-300/80 shadow-sm overflow-hidden">
      <div class="overflow-auto max-h-[65vh]">
        <table class="w-full text-left border-collapse min-w-[1100px]">
          <thead class="bg-slate-50 text-slate-600 text-[16px] uppercase sticky top-0 z-10">
            <tr>
              <th class="px-4 py-4">ល.រ</th>
              <th class="px-4 py-4">រូបថត</th>
              <th class="px-4 py-4">លេខសម្គាល់</th>
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
              <td class="px-4 py-3">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 border border-slate-200">
                  <img v-if="teacher.photo_url" :src="teacher.photo_url" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-slate-300 text-xs font-bold">
                    {{ initials(teacher.name_kh) }}
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ teacher.teacher_id_card }}</td>
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

    <div v-else class="p-12 text-center bg-white rounded-2xl border border-dashed text-slate-400">
      មិនមានទិន្នន័យគ្រូបង្រៀនត្រូវនឹងលក្ខខណ្ឌនេះទេ
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../../services/authService'
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'

const teachers = ref([])
const isLoading = ref(false)
const loadError = ref('')

const searchQuery = ref('')
const filterSpecialty = ref('')
const filterStatus = ref('')

// Same subject list offered when adding a teacher (TeacherModel.vue)
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

const initials = (name) => (name || '').trim().slice(0, 1) || '?'

// specialty may come back as a JSON string (as it's submitted via FormData
// in TeacherModel.vue) or already as an array — handle both.
const formatSpecialty = (specialty) => {
  if (!specialty) return '-'
  let list = specialty
  if (typeof specialty === 'string') {
    try { list = JSON.parse(specialty) } catch { list = [specialty] }
  }
  return Array.isArray(list) ? list.join(', ') : String(list)
}

const filteredTeachers = computed(() => {
  return teachers.value.filter(t => {
    const query = searchQuery.value.trim().toLowerCase()
    const matchesSearch = !query ||
      t.name_kh?.toLowerCase().includes(query) ||
      t.name_en?.toLowerCase().includes(query) ||
      t.teacher_id_card?.toLowerCase().includes(query)

    const matchesSpecialty = !filterSpecialty.value ||
      formatSpecialty(t.specialty).includes(filterSpecialty.value)

    const matchesStatus = filterStatus.value === '' ||
      String(t.status) === filterStatus.value

    return matchesSearch && matchesSpecialty && matchesStatus
  })
})

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

// ----- Export: Excel -----
const exportExcel = () => {
  if (filteredTeachers.value.length === 0) return

  const exportData = filteredTeachers.value.map((t, index) => ({
    'ល.រ': index + 1,
    'លេខសម្គាល់': t.teacher_id_card,
    'ឈ្មោះ (ខ្មែរ)': t.name_kh,
    'ឈ្មោះឡាតាំង': t.name_en,
    'ភេទ': getGenderKh(t.gender),
    'អ៊ីមែល': t.email,
    'ទូរស័ព្ទ': t.phone || '',
    'មុខវិជ្ជាជំនាញ': formatSpecialty(t.specialty),
    'ស្ថានភាព': t.status == 1 ? 'នៅបង្រៀន' : 'ឈប់បង្រៀន'
  }))

  const ws = XLSX.utils.json_to_sheet(exportData)
  ws['!cols'] = [
    { wch: 6 }, { wch: 14 }, { wch: 22 }, { wch: 22 },
    { wch: 8 }, { wch: 26 }, { wch: 14 }, { wch: 30 }, { wch: 14 }
  ]
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'គ្រូបង្រៀន')
  XLSX.writeFile(wb, 'teacher-report.xlsx')
}

// ----- Export: PDF -----
const exportPDF = () => {
  if (filteredTeachers.value.length === 0) return

  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })

  // Header (Latin only — jsPDF's built-in fonts don't render Khmer glyphs)
  doc.setFontSize(15)
  doc.setFont('helvetica', 'bold')
  doc.text('Teacher Report', 148, 18, { align: 'center' })

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text(`Export: ${new Date().toLocaleDateString()}`, 148, 26, { align: 'center' })

  const headers = ['No', 'ID', 'Name', 'Gender', 'Email', 'Phone', 'Status']
  const colWidths = [12, 25, 55, 20, 60, 30, 30]
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
  filteredTeachers.value.forEach((t, idx) => {
    if (idx % 2 === 0) {
      doc.setFillColor(248, 250, 252)
      doc.rect(startX, y, colWidths.reduce((a, b) => a + b, 0), 8, 'F')
    }
    const row = [
      idx + 1,
      t.teacher_id_card || '',
      t.name_en || t.name_kh, // Note: Khmer text (name_kh) renders blank/garbled with default jsPDF fonts
      t.gender === 'male' ? 'M' : 'F',
      t.email || '',
      t.phone || '',
      t.status == 1 ? 'Active' : 'Inactive'
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
  doc.text(`Total teachers: ${filteredTeachers.value.length}`, startX, y + 8)

  doc.save('teacher-report.pdf')
}

onMounted(fetchTeachers)
</script>