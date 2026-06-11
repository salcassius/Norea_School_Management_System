<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងគ្រូបង្រៀន</h1>
        <p class="text-sm text-slate-500 mt-1">គ្រប់គ្រងព័ត៌មាន និងឯកទេសរបស់លោកគ្រូ អ្នកគ្រូក្នុងប្រព័ន្ធ</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button @click="exportToExcel"
          class="flex items-center justify-center gap-2 bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-200 text-slate-700 hover:text-emerald-700 px-4 py-2.5 rounded-xl font-medium transition-all shadow-sm active:scale-95">
          <FileSpreadsheet class="w-5 h-5 text-emerald-600" />
          ទាញយកជា Excel
        </button>

        <button @click="exportToPDF"
          class="flex items-center justify-center gap-2 bg-white border border-slate-200 hover:bg-rose-50 hover:border-rose-200 text-slate-700 hover:text-rose-700 px-4 py-2.5 rounded-xl font-medium transition-all shadow-sm active:scale-95">
          <FileText class="w-5 h-5 text-red-500" />
          ទាញយកជា PDF
        </button>

        <button @click="showCreateModal = true"
          class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          បន្ថែមគ្រូបង្រៀនថ្មី
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in teacherStats" :key="stat.label" 
        class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 transition-all hover:border-indigo-300">
        <div :class="['p-3 rounded-xl', stat.bgColor]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
        </div>
        <div>
          <p class="text-[14px] text-slate-500 font-medium uppercase tracking-wider">{{ stat.label }}</p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row gap-4 justify-between bg-slate-50/30">
        <div class="relative max-w-sm w-full">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ, ID, ឬលេខទូរស័ព្ទ..."
            class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
        </div>
        
        <div v-if="searchQuery" class="flex items-center">
          <button @click="searchQuery = ''" class="text-xs text-indigo-600 hover:underline">បង្ហាញទាំងអស់</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <div v-if="isLoading" class="p-10 text-center text-slate-500 text-sm">កំពុងទាញទិន្នន័យ...</div>
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">គ្រូបង្រៀន</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ឯកទេស</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ទំនាក់ទំនង</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="teacher in filteredTeachers" :key="teacher.id" class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100 overflow-hidden">
                    <img v-if="teacher.photo_url" :src="teacher.photo_url" class="w-full h-full object-cover" />
                    <span v-else class="text-sm font-bold text-indigo-600 uppercase">{{ (teacher.name_kh || 'T').charAt(0) }}</span>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 leading-tight">{{ teacher.name_kh }}</p>
                    <p class="text-[11px] text-slate-400 mt-1 uppercase tracking-tighter">{{ teacher.name_en }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="sub in getSpecialties(teacher.specialty).slice(0, 2)" :key="sub" 
                    class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-medium border border-slate-200">
                    {{ sub }}
                  </span>
                  <span v-if="getSpecialties(teacher.specialty).length > 2" class="text-[10px] text-slate-400 font-bold ml-1">
                    +{{ getSpecialties(teacher.specialty).length - 2 }}
                  </span>
                  <span v-if="getSpecialties(teacher.specialty).length === 0" class="text-[10px] text-slate-300 italic">គ្មាន</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-slate-600 font-medium">{{ teacher.phone || 'N/A' }}</p>
                <p class="text-[11px] text-slate-400 truncate max-w-[150px]">{{ teacher.user?.email || 'គ្មានគណនី' }}</p>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                  teacher.status == 1 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                ]">
                  {{ teacher.status == 1 ? 'សកម្ម' : 'ផ្អាក' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openEditModal(teacher)"
                    class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all active:scale-90"
                    title="Edit">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="handleDelete(teacher.id)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredTeachers.length === 0">
              <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm">មិនមានទិន្នន័យគ្រូបង្រៀនឡើយ</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <TeacherCreateModal 
      v-if="showCreateModal" 
      v-model="showCreateModal" 
      @refresh="fetchTeachers" 
    />

    <EditeTeacher 
      v-if="isEditModalOpen" 
      v-model="isEditModalOpen" 
      :teacher-data="selectedTeacher" 
      @refresh="fetchTeachers" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import teacherService from '../../services/teacherService'
import { 
  Plus, Search, Edit3, Trash2, 
  GraduationCap, Users, UserCheck, UserMinus,
  FileSpreadsheet, FileText, BookOpen 
} from 'lucide-vue-next'

// Export Libraries
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

// Import Modals
import TeacherCreateModal from '../admin/create/CreateTeacherModel.vue'
import EditeTeacher from './edite/EditeTeacher.vue'

// --- State ---
const teachers = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const showCreateModal = ref(false)
const isEditModalOpen = ref(false)
const selectedTeacher = ref(null)

// --- Methods ---
const fetchTeachers = async () => {
  isLoading.value = true
  try {
    const res = await teacherService.getTeachers()
    teachers.value = Array.isArray(res) ? res : (res.data || [])
  } catch (error) {
    console.error("Fetch Error:", error)
  } finally {
    isLoading.value = false
  }
}

const openEditModal = (teacher) => {
  selectedTeacher.value = { ...teacher } 
  isEditModalOpen.value = true
}

const handleDelete = async (id) => {
  if (!confirm('តើអ្នកប្រាកដថាចង់លុបគ្រូនេះមែនទេ?')) return
  try {
    await teacherService.deleteTeacher(id)
    await fetchTeachers()
  } catch (error) {
    alert('មិនអាចលុបបានទេ! ទិន្នន័យនេះអាចកំពុងប្រើប្រាស់ក្នុងប្រព័ន្ធ។')
  }
}

const getSpecialties = (data) => {
  if (!data) return []
  try {
    return typeof data === 'string' ? JSON.parse(data) : data
  } catch {
    return [data]
  }
}

// --- Export Logic ---
const exportToExcel = () => {
  const dataToExport = filteredTeachers.value.map(t => ({
    'ឈ្មោះខ្មែរ': t.name_kh,
    'ឈ្មោះអង់គ្លេស': t.name_en,
    'ភេទ': t.gender,
    'ឯកទេស': getSpecialties(t.specialty).join(', '),
    'លេខទូរស័ព្ទ': t.phone || 'N/A',
    'អ៊ីមែល': t.user?.email || 'N/A',
    'ស្ថានភាព': t.status == 1 ? 'សកម្ម' : 'ផ្អាក'
  }))

  const worksheet = XLSX.utils.json_to_sheet(dataToExport)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, "Teachers List")
  
  XLSX.writeFile(workbook, `បញ្ជីឈ្មោះគ្រូបង្រៀន_${new Date().toLocaleDateString()}.xlsx`)
}

const exportToPDF = () => {
  try {
    const doc = new jsPDF()
    
    // ក្បាលតារាង (Headers)
    const tableColumn = ["Name EN", "Gender", "Specialty", "Phone", "Status"]
    
    // ទិន្នន័យ (Rows)
    const tableRows = filteredTeachers.value.map(t => [
      t.name_en || t.name_kh,
      t.gender === 'female' ? 'Female' : 'Male',
      getSpecialties(t.specialty).join(', '),
      t.phone || 'N/A',
      t.status == 1 ? 'Active' : 'Inactive'
    ])

    // ចំណងជើងរបាយការណ៍
    doc.setFontSize(18)
    doc.text("Teacher List Report", 14, 15)
    doc.setFontSize(10)
    doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 22)

    // បង្កើតតារាង (ប្រើ autoTable function ផ្ទាល់)
    autoTable(doc, {
      head: [tableColumn],
      body: tableRows,
      startY: 28,
      theme: 'striped',
      headStyles: { fillColor: [79, 70, 229] }, // ពណ៌ Indigo-600
      styles: { font: 'helvetica' } // ប្រើ Helvetica ដើម្បីជៀសវាងបញ្ហា Font ខ្មែរ
    })

    // ទាញយក File
    doc.save(`Teacher_Report_${new Date().getTime()}.pdf`)
  } catch (error) {
    console.error("PDF Export Error:", error)
    alert("មិនអាចបង្កើត PDF បានទេ! សូមពិនិត្យមើល Console សម្រាប់ព័ត៌មានលម្អិត។")
  }
}

// --- Computed ---
const filteredTeachers = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  if (!query) return teachers.value

  return teachers.value.filter(t => {
    return (t.name_kh?.toLowerCase().includes(query)) || 
           (t.name_en?.toLowerCase().includes(query)) || 
           (t.phone?.includes(query)) ||
           (t.teacher_id_card?.toLowerCase().includes(query))
  })
})

const teacherStats = computed(() => {
  const list = teachers.value || []
  return [
    {
      label: 'គ្រូសរុប',
      value: list.length + ' នាក់',
      icon: GraduationCap,
      bgColor: 'bg-indigo-50',
      iconColor: 'text-indigo-600'
    },
    {
      label: 'គ្រូសកម្ម',
      value: list.filter(t => t.status == 1).length + ' នាក់',
      icon: UserCheck,
      bgColor: 'bg-emerald-50',
      iconColor: 'text-emerald-600'
    },
    {
      label: 'គ្រូស្រី',
      value: list.filter(t => ['female', 'ស្រី'].includes(t.gender?.toLowerCase())).length + ' នាក់',
      icon: Users,
      bgColor: 'bg-rose-50',
      iconColor: 'text-rose-600'
    },
    {
      label: 'គ្រូផ្អាក',
      value: list.filter(t => t.status == 0).length + ' នាក់',
      icon: UserMinus,
      bgColor: 'bg-slate-100',
      iconColor: 'text-slate-500'
    }
  ]
})

onMounted(fetchTeachers)
</script>