<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">
    
    <div v-if="!activeDetailClass">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងថ្នាក់រៀន</h1>
          <p class="text-sm text-slate-500 mt-1">រៀបចំបញ្ជីថ្នាក់ បែងចែកគ្រូបង្រៀន និងតាមដានចំនួនសិស្ស</p>
        </div>

        <button @click="openCreateModal"
          class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          បង្កើតថ្នាក់ថ្មី
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div v-for="item in summaryStats" :key="item.label"
          class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 transition-all hover:border-indigo-300">
          <div :class="['p-3 rounded-xl', item.iconBg]">
            <component :is="item.icon" class="w-6 h-6" :class="item.iconColor" />
          </div>
          <div>
            <p class="text-[14px] text-slate-500 font-medium uppercase tracking-wider">{{ item.label }}</p>
            <p class="text-lg font-bold text-slate-800">{{ item.value }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row gap-4 justify-between bg-slate-50/30">
          <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="relative max-w-sm w-full">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះថ្នាក់ ឬគ្រូ..."
                class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
            </div>

            <div class="relative min-w-[180px]">
              <select v-model="filters.academic_year_id" @change="fetchClasses"
                class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                <option value="">គ្រប់ឆ្នាំសិក្សាទាំងអស់</option>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.year_name || year.name }}
                </option>
              </select>
              <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <div v-if="loading" class="p-10 text-center text-slate-500 text-sm italic">កំពុងទាញទិន្នន័យ...</div>
          <table v-else class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50/50">
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ឈ្មោះថ្នាក់</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">គ្រូទទួលបន្ទុក</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">សិស្ស/កម្រិត</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="cls in filteredClasses" :key="cls.id" @click="viewDetail(cls)" class="hover:bg-slate-50/80 transition-colors group cursor-pointer">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100">
                      <Layout class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-700 leading-tight">ថ្នាក់ទី {{ cls.grade_level }}{{ cls.name }}</p>
                      <p class="text-[10px] text-slate-400 mt-0.5 uppercase">{{ cls.year?.year_name || cls.year?.name || 'N/A' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 text-sm text-slate-600">
                    <UserCheck class="w-4 h-4 text-slate-400" />
                    {{ cls.teacher ? cls.teacher.name_kh : 'មិនទាន់មាន' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm">
                    <span class="font-bold text-slate-700">{{ cls.students_count || cls.student_count || 0 }}</span>
                    <span class="text-slate-400 text-xs ml-1">សិស្ស</span>
                  </div>
                  <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">ថ្នាក់ទី {{ cls.grade_level }}</div>
                </td>
                <td class="px-6 py-4">
                  <span :class="[
                    'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                    cls.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                  ]">
                    {{ cls.is_active ? 'សកម្ម' : 'មិនសកម្ម' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right" @click.stop>
                  <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="viewDetail(cls)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="View Detail">
                      <ChevronRight class="w-4 h-4" />
                    </button>
                    <button @click="editClass(cls)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit">
                      <Edit3 class="w-4 h-4" />
                    </button>
                    <button @click="deleteClass(cls.id)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredClasses.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm italic">មិនឃើញមានថ្នាក់រៀនទេ</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-else class="animate-in fade-in duration-200">
      <button @click="activeDetailClass = null" class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-800 mb-4 transition-colors">
        ← ត្រឡប់ទៅបញ្ជីថ្នាក់វិញ
      </button>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
          <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-md uppercase">ព័ត៌មានលម្អិតថ្នាក់</span>
          <h2 class="text-2xl font-bold text-slate-800 mt-2">ថ្នាក់ទី៖ {{ activeDetailClass.grade_level }}{{ activeDetailClass.name }}</h2>
          <p class="text-sm text-slate-500 mt-1">
            គ្រូបន្ទុក៖ <span class="font-bold text-slate-700">{{ activeDetailClass.teacher?.name_kh || 'មិនទាន់មាន' }}</span> | 
            ឆ្នាំសិក្សា៖ <span class="font-bold text-slate-700">{{ activeDetailClass.year?.year_name || activeDetailClass.year?.name || 'N/A' }}</span>
          </p>
        </div>
        
        <button @click="isAddStudentModalOpen = true" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-100 transition-all active:scale-95">
          <Plus class="w-4 h-4" /> បញ្ចូលសិស្សទៅក្នុងថ្នាក់
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="font-bold text-sm text-slate-600">
            👥 បញ្ជីឈ្មោះសិស្សក្នុងថ្នាក់សរុប៖ {{ classStudents.length }} នាក់
          </div>
          <div v-if="classStudents.length > 0" class="flex items-center gap-2">
            <button @click="downloadExcel" class="flex items-center gap-1.5 text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl transition-all active:scale-95">
              <Download class="w-3.5 h-3.5" /> ទាញយកជា Excel
            </button>
            <button @click="downloadPDF" class="flex items-center gap-1.5 text-xs font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-xl transition-all active:scale-95">
              <Download class="w-3.5 h-3.5" /> ទាញយកជា PDF
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50/20 text-xs font-bold text-slate-400 uppercase">
                <th class="px-6 py-3 w-16 text-center">ល.រ</th>
                <th class="px-6 py-3">លេខកូដសិស្ស (Code)</th>
                <th class="px-6 py-3">ឈ្មោះសិស្ស (ខ្មែរ/ឡាតាំង)</th>
                <th class="px-6 py-3">ភេទ</th>
                <th class="px-6 py-3 text-right">សកម្មភាព</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
              <tr v-for="(std, index) in classStudents" :key="std.id" class="hover:bg-slate-50/50">
                <td class="px-6 py-3.5 text-center font-bold text-slate-400">{{ index + 1 }}</td>
                <td class="px-6 py-3.5 font-mono font-bold text-indigo-600">
                  {{ std.student_id_card || std.student_code || std.code || 'N/A' }}
                </td>
                <td class="px-6 py-3.5 font-bold text-slate-700">
                  {{ std.name_kh || std.name }} 
                  <span class="text-xs text-slate-400 font-normal ml-2" v-if="std.name_en">({{ std.name_en }})</span>
                </td>
                <td class="px-6 py-3.5 capitalize">{{ std.gender === 'male' ? 'ប្រុស' : std.gender === 'female' ? 'ស្រី' : std.gender || 'N/A' }}</td>
                <td class="px-6 py-3.5 text-right">
                  <button @click="removeStudentFromClass(std.id)" class="text-xs font-bold text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-colors">
                    ដកចេញពីថ្នាក់
                  </button>
                </td>
              </tr>
              <tr v-if="classStudents.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">មិនទាន់មានសិស្សនៅក្នុងថ្នាក់នេះនៅឡើយទេ</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="isAddStudentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="isAddStudentModalOpen = false"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 overflow-hidden animate-in zoom-in duration-150 text-slate-700">
        <h3 class="text-lg font-bold text-slate-800 mb-2">ស្វែងរកសិស្សបញ្ចូលទៅក្នុងថ្នាក់</h3>
        <p class="text-xs text-slate-400 mb-4">ប្អូនអាចវាយបញ្ចូលឈ្មោះសិស្ស ឬលេខកូដសិស្ស (Code) ដើម្បីស្វែងរក</p>

        <div class="relative mb-4">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="studentSearchQuery" @input="searchStudentsToAssign" type="text" placeholder="វាយឈ្មោះខ្មែរ ឬ កូដសិស្ស..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-indigo-500" />
        </div>

        <div class="max-h-60 overflow-y-auto border border-slate-100 rounded-xl divide-y divide-slate-50 bg-slate-50/50">
          <div v-if="searchingStudents" class="p-4 text-center text-xs text-slate-400 italic">កំពុងស្វែងរកសិស្ស...</div>
          
          <div v-else v-for="student in foundStudents" :key="student.id" class="p-3 flex items-center justify-between hover:bg-white transition-colors">
            <div>
              <p class="text-sm font-bold text-slate-700">{{ student.name_kh || student.name }}</p>
              <p class="text-[11px] font-mono font-bold text-indigo-500">{{ student.student_id_card || 'គ្មានទិន្នន័យកូដ' }}</p>
            </div>
            <button @click="assignStudentToClass(student.id)" class="text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition-all active:scale-95">
              + បញ្ចូល
            </button>
          </div>

          <div v-if="foundStudents.length === 0 && studentSearchQuery.trim() !== ''" class="p-4 text-center text-xs text-slate-400">
            ❌ មិនស្គាល់ ឬរកមិនឃើញសិស្សម្នាក់នេះឡើយ
          </div>
        </div>

        <div class="mt-5 flex justify-end">
          <button @click="isAddStudentModalOpen = false" class="text-sm font-bold text-slate-400 hover:text-slate-600 px-4 py-2">
            បិទផ្ទាំង
          </button>
        </div>
      </div>
    </div>

    <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeCreateModal"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in duration-200">
        <CreateClassModel @close="closeCreateModal" @refresh="handleClassCreated" />
      </div>
    </div>

    <EditeClass v-model="isEditModalOpen" :classData="selectedClass" @refresh="fetchClasses" />

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import {
  Plus, Search, Edit3, Trash2, UserCheck, Users, BookOpen, Layout, ChevronRight, ChevronDown, Download
} from 'lucide-vue-next'
import api from '../../services/authService'
import CreateClassModel from '../admin/create/CreateClassModel.vue'
import EditeClass from '../admin/edite/EditeClass.vue'

// 💡 នាំចូលបណ្ណាល័យសម្រាប់ទាញយក File
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import 'jspdf-autotable'

// --- Original States ---
const searchQuery = ref('')
const loading = ref(false)
const academicYears = ref([])
const classes = ref([])

const isCreateModalOpen = ref(false)
const isEditModalOpen = ref(false) 
const selectedClass = ref(null)    

// States សម្រាប់មុខងារគ្រប់គ្រងសិស្សក្នុងថ្នាក់
const activeDetailClass = ref(null)       
const classStudents = ref([])             
const isAddStudentModalOpen = ref(false)  
const studentSearchQuery = ref('')       
const foundStudents = ref([])             
const searchingStudents = ref(false)      

const filters = reactive({
  academic_year_id: ''
})

// --- Computed Stats ---
const summaryStats = computed(() => [
  { label: 'ថ្នាក់រៀនសរុប', value: classes.value.length, icon: Layout, iconBg: 'bg-indigo-50', iconColor: 'text-indigo-600' },
  { label: 'សិស្សសរុប', value: classes.value.reduce((acc, curr) => acc + (parseInt(curr.students_count || curr.student_count) || 0), 0), icon: Users, iconBg: 'bg-emerald-50', iconColor: 'text-emerald-600' },
  { label: 'ថ្នាក់សកម្ម', value: classes.value.filter(c => c.is_active).length, icon: BookOpen, iconBg: 'bg-amber-50', iconColor: 'text-amber-600' },
])

// --- Data Fetching ---
const fetchInitialData = async () => {
  try {
    const response = await api.get('/years')
    academicYears.value = response.data
  } catch (error) {
    console.error("Error loading years:", error)
  }
}

const fetchClasses = async () => {
  loading.value = true
  try {
    const params = {};
    if (filters.academic_year_id) {
      params.academic_year_id = filters.academic_year_id;
    }
    const response = await api.get('/classes', { params });
    classes.value = response.data;
    
    if (activeDetailClass.value) {
      const updatedCls = classes.value.find(c => c.id === activeDetailClass.value.id);
      if (updatedCls) activeDetailClass.value = updatedCls;
    }
  } catch (error) {
    console.error("Error loading classes:", error);
  } finally {
    loading.value = false;
  }
}

// --- Filter Logic ---
const filteredClasses = computed(() => {
  if (!classes.value) return []
  return classes.value.filter(cls => {
    const className = (cls.name || "").toLowerCase()
    const teacherName = (cls.teacher?.name_kh || "").toLowerCase()
    const yearName = (cls.year?.year_name || cls.year?.name || "").toLowerCase()
    const searchTerm = (searchQuery.value || "").toLowerCase()
    
    return className.includes(searchTerm) || 
           teacherName.includes(searchTerm) || 
           yearName.includes(searchTerm)
  })
})

// --- 💡 មុខងារទាញយកទិន្នន័យ (Excel & PDF) ---

// ១. ទាញយកជា Excel (.xlsx)
const downloadExcel = () => {
  if (classStudents.value.length === 0) return

  // រៀបចំទិន្នន័យដើម្បីបង្ហាញក្នុងសន្លឹក Excel
  const dataRows = classStudents.value.map((std, idx) => ({
    'ល.រ': idx + 1,
    'លេខកូដសិស្ស': std.student_id_card || std.student_code || std.code || 'N/A',
    'ឈ្មោះខ្មែរ': std.name_kh || std.name || '',
    'ឈ្មោះឡាតាំង': std.name_en || '',
    'ភេទ': std.gender === 'male' ? 'ប្រុស' : std.gender === 'female' ? 'ស្រី' : std.gender || 'N/A',
  }))

  const worksheet = XLSX.utils.json_to_sheet(dataRows)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'បញ្ជីឈ្មោះសិស្ស')

  // កំណត់ឈ្មោះ File តាមឈ្មោះថ្នាក់
  const className = `ថ្នាក់ទី_${activeDetailClass.value.grade_level}${activeDetailClass.value.name}`
  XLSX.writeFile(workbook, `បញ្ជីឈ្មោះសិស្ស_${className}.xlsx`)
}

// ២. ទាញយកជា PDF (.pdf)
const downloadPDF = () => {
  if (classStudents.value.length === 0) return

  const doc = new jsPDF()

  // 🔔 ចំណាំ៖ ប្រសិនបើអក្សរខ្មែរដាច់ ឬចេញសញ្ញាសួរ សូមបន្ថែម Khmer Font Base64 String នៅទីនេះ៖
  // doc.addFileToVFS('KhmerFont.ttf', 'BASE64_STRING_HERE');
  // doc.addFont('KhmerFont.ttf', 'KhmerFont', 'normal');
  // doc.setFont('KhmerFont');

  const className = `ថ្នាក់ទី៖ ${activeDetailClass.value.grade_level}${activeDetailClass.value.name}`
  const teacherName = `គ្រូទទួលបន្ទុក៖ ${activeDetailClass.value.teacher?.name_kh || 'មិនទាន់មាន'}`
  const academicYear = `ឆ្នាំសិក្សា៖ ${activeDetailClass.value.year?.year_name || activeDetailClass.value.year?.name || 'N/A'}`

  // បញ្ចូលចំណងជើងក្បាលទំព័រ
  doc.text(`បញ្ជីឈ្មោះសិស្សសរុប - ${className}`, 14, 15)
  doc.text(`${teacherName} | ${academicYear}`, 14, 22)

  // រៀបចំតារាងទិន្នន័យ
  const tableHeaders = [['ល.រ', 'លេខកូដសិស្ស', 'ឈ្មោះខ្មែរ', 'ឈ្មោះឡាតាំង', 'ភេទ']]
  const tableRows = classStudents.value.map((std, idx) => [
    idx + 1,
    std.student_id_card || std.student_code || std.code || 'N/A',
    std.name_kh || std.name || '',
    std.name_en || '',
    std.gender === 'male' ? 'ប្រុស' : std.gender === 'female' ? 'ស្រី' : std.gender || 'N/A'
  ])

  doc.autoTable({
    startY: 28,
    head: tableHeaders,
    body: tableRows,
    styles: { 
      font: 'Courier', // ប្តូរទៅឈ្មោះ Font ខ្មែររបស់អ្នក បើបានដំឡើងខាងលើ
      fontSize: 10 
    },
    headStyles: { fillColor: [79, 70, 229] } // ពណ៌ Indigo សក្តិសមនឹង UI
  })

  doc.save(`បញ្ជីឈ្មោះសិស្ស_${activeDetailClass.value.grade_level}${activeDetailClass.value.name}.pdf`)
}

// --- Actions គ្រប់គ្រងសិស្សក្នុងថ្នាក់ ---
const viewDetail = async (cls) => {
  activeDetailClass.value = cls
  fetchStudentsByClass(cls.id)
}

const fetchStudentsByClass = async (classId) => {
  try {
    const response = await api.get(`/classes/${classId}/students`)
    if (response.data && Array.isArray(response.data)) {
      classStudents.value = response.data
    } else if (response.data && Array.isArray(response.data.data)) {
      classStudents.value = response.data.data
    } else {
      classStudents.value = []
    }
  } catch (error) {
    console.error("Error fetching class students:", error)
    classStudents.value = []
  }
}

const searchStudentsToAssign = async () => {
  if (!studentSearchQuery.value.trim()) {
    foundStudents.value = []
    return
  }
  searchingStudents.value = true
  try {
    const response = await api.get('/students', {
      params: { search: studentSearchQuery.value }
    })
    const allStudents = Array.isArray(response.data) ? response.data : (response.data?.data || [])
    foundStudents.value = allStudents.filter(
      std => !classStudents.value.some(s => s.id === std.id)
    )
  } catch (error) {
    console.error("Error searching students:", error)
  } finally {
    searchingStudents.value = false
  }
}

const assignStudentToClass = async (studentId) => {
  try {
    // ហៅទៅកាន់ Route ថ្មីដែលយើងបង្កើត
    await api.post(`/classes/${activeDetailClass.value.id}/assign-student`, {
      student_id: studentId
    });

    studentSearchQuery.value = '';
    foundStudents.value = [];
    isAddStudentModalOpen.value = false; // បិទ Modal
    
    // Refresh ទិន្នន័យ
    await fetchStudentsByClass(activeDetailClass.value.id);
    await fetchClasses();
    
    alert("បញ្ចូលសិស្សជោគជ័យ!");
  } catch (error) {
    console.error("Error assigning student:", error);
    alert("មានបញ្ហាក្នុងការបញ្ចូលសិស្សទៅក្នុងថ្នាក់នេះ!");
  }
}

const removeStudentFromClass = async (studentId) => {
  if (confirm("តើប្អូនពិតជាចង់ដកសិស្សម្នាក់នេះចេញពីថ្នាក់រៀននេះមែនទេ?")) {
    try {
      await api.put(`/students/${studentId}`, {
        class_id: null,
        classroom_id: null
      })
      await fetchStudentsByClass(activeDetailClass.value.id)
      await fetchClasses()
    } catch (error) {
      console.error("Error removing student:", error)
      alert("មិនអាចដកសិស្សចេញបានឡើយ!");
    }
  }
}

// --- Original Actions ---
const openCreateModal = () => { isCreateModalOpen.value = true }
const closeCreateModal = () => { isCreateModalOpen.value = false }
const handleClassCreated = () => {
  fetchClasses()
  closeCreateModal()
}
const editClass = (cls) => {
  selectedClass.value = { ...cls }
  isEditModalOpen.value = true    
}
const deleteClass = async (id) => {
  if (confirm('តើអ្នកពិតជាចង់លុបថ្នាក់នេះមែនទេ?')) {
    try {
      await api.delete(`/classes/${id}`)
      fetchClasses()
    } catch (error) {
      alert(error.response?.data?.message || 'មិនអាចលុបបានទេ!');
    }
  }
}

onMounted(() => {
  fetchInitialData()
  fetchClasses()
})
</script>