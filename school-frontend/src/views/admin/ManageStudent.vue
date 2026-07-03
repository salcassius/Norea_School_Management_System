<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងសិស្សានុសិស្ស</h1>
        <p class="text-sm text-slate-500 mt-1">គ្រប់គ្រងទិន្នន័យ បញ្ជីឈ្មោះ និងស្ថានភាពសិក្សារបស់សិស្ស</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button @click="openCreateModal"
          class="w-full md:w-auto flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          <span>បន្ថែមសិស្សថ្មី</span>
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in studentStats" :key="stat.label" @click="handleStatClick(stat.filterType, stat.filterValue)"
        :class="[
          'p-5 rounded-2xl border transition-all cursor-pointer shadow-sm flex items-center gap-4 active:scale-95',
          activeFilter === stat.filterValue ? 'bg-white border-indigo-500 ring-4 ring-indigo-50' : 'bg-white border-slate-300 hover:border-indigo-300'
        ]">
        <div :class="['p-3 rounded-xl', stat.bgColor]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
        </div>
        <div>
          <p class="text-[14px] text-slate-500 font-medium uppercase tracking-wider">{{ stat.label }}</p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Export Buttons -->
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
      <button @click="exportToExcel"
        class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-md active:scale-95 whitespace-nowrap">
        <span>📄</span> <span>ទាញយក Excel</span>
      </button>
      <button @click="exportToPDF"
        class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-md active:scale-95 whitespace-nowrap">
        <span>📄</span> <span>ទាញយក PDF</span>
      </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row gap-4 justify-between bg-slate-50/30">
        <div class="relative max-w-sm w-full">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ, ID, ឬលេខទូរស័ព្ទ..."
            class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
        </div>

        <div class="flex items-center gap-3">
          <select v-model="selectedStatus"
            class="bg-white border border-slate-200 text-slate-600 text-sm rounded-lg px-4 py-2 outline-none focus:border-indigo-500 transition-all cursor-pointer">
            <option value="">ស្ថានភាពទាំងអស់</option>
            <option value="1">កំពុងរៀន</option>
            <option value="0">ឈប់រៀន/ព្យួរ</option>
          </select>
          <button v-if="activeFilter || searchQuery" @click="resetFilters"
            class="text-xs text-indigo-600 hover:underline font-bold">បង្ហាញទាំងអស់</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <div v-if="isLoading" class="p-20 text-center flex flex-col items-center gap-3">
          <div class="w-8 h-8 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
          <span class="text-slate-500 text-sm font-medium">កំពុងទាញទិន្នន័យ...</span>
        </div>

        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">សិស្សានុសិស្ស</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">អត្តលេខ / ភេទ</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ទំនាក់ទំនង</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">អាសយដ្ឋានបច្ចុប្បន្ន</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ឈ្មោះឪពុក</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ឈ្មោះម្តាយ</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="student in filteredStudents" :key="student.id"
              class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 shadow-sm">
                    <img v-if="student.photo" :src="`http://localhost:8000/storage/${student.photo}`"
                      class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                      <UserRound class="w-5 h-5" />
                    </div>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 leading-tight">{{ student.name_kh }}</p>
                    <p class="text-[12px] text-slate-600 mt-1 uppercase tracking-tight">{{ student.name_en }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-slate-600 font-medium uppercase">{{ student.student_id_card || 'N/A' }}</p>
                <p class="text-[15px] text-slate-600">{{ student.gender === 'female' ? 'ស្រី' : 'ប្រុស' }}</p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-slate-600 font-medium">{{ student.phone || 'N/A' }}</p>
                <p class="text-[14px] text-slate-600 truncate max-w-[150px]">{{ student.email }}</p>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600">
                <div class="text-[14px]">
                  {{ student.village }}, {{ student.commune }}, {{ student.district }}, {{ student.province }}
                </div>
              </td>
              <td class="px-4 py-4 text-slate-700 text-[14px]">
                <div>{{ student.guardian_dad_name }}</div>
                <div class="text-[13px] text-slate-700">{{ student.guardian_dad_phone }}</div>
              </td>
              <td class="px-4 py-4 text-[14px] text-slate-700">
                <div>{{ student.guardian_mom_name }}</div>
                <div class="text-[13px] text-slate-700">{{ student.guardian_mom_phone }}</div>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider border transition-all duration-300',
                  student.status == 1 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                ]">
                  {{ student.status == 1 ? 'កំពុងរៀន' : 'ឈប់រៀន/ព្យួរ' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="handleEdit(student)" title="កែសម្រួល"
                    class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-90 shadow-sm">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="openDeleteModal(student)" title="លុប"
                    class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all active:scale-90 shadow-sm">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredStudents.length === 0 && !isLoading">
              <td colspan="8" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <UserRound class="w-12 h-12 text-slate-200" />
                  <p class="text-slate-400 text-sm font-medium">មិនមានទិន្នន័យសិស្សត្រូវតាមលក្ខខណ្ឌស្វែងរកឡើយ</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modals -->
    <StudentCreateModal v-if="showCreateModal" 
    v-model="showCreateModal" 
    @refresh="handleCreateStudent" />
    <StudentEditModal v-if="showEditModal" 
    v-model="showEditModal" 
    :student-data="selectedStudent" 
    @refresh="handleUpdateStudent" />

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.show" :class="[
          'fixed top-5 right-5 z-[100] flex items-center gap-3 px-4 py-3 rounded-2xl border shadow-lg min-w-[260px] max-w-xs',
          toast.type === 'success'
            ? 'bg-white border-emerald-200 text-emerald-700'
            : 'bg-white border-rose-200 text-rose-600'
        ]">
          <div :class="[
            'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
            toast.type === 'success' ? 'bg-emerald-50' : 'bg-rose-50'
          ]">
            <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-500" />
            <XCircle v-else class="w-4 h-4 text-rose-500" />
          </div>
          <p class="text-sm font-medium flex-1">{{ toast.message }}</p>
          <button @click="toast.show = false" class="text-slate-300 hover:text-slate-500 transition-colors">
            <X class="w-4 h-4" />
          </button>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete Confirm Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isDeleteModalOpen"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(15, 15, 20, 0.5);"
          @click.self="closeDeleteModal">
          <Transition name="modal-scale">
            <div v-if="isDeleteModalOpen"
              class="bg-white rounded-2xl border border-slate-200 w-full max-w-sm p-8 text-center font-[Battambang]">

              <!-- Icon -->
              <div class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center mx-auto mb-5">
                <Trash2 class="w-7 h-7 text-rose-500 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]" />
              </div>

              <!-- Title & Message -->
              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបសិស្សានុសិស្ស</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingStudent?.name_kh }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <!-- Buttons -->
              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteStudent" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white bg-rose-500 hover:bg-rose-600 flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-60">
                  <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
                  <Trash2 v-else class="w-4 h-4" />
                  {{ isDeleting ? 'កំពុងលុប...' : 'លុបចោល' }}
                </button>
              </div>

            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import studentService from '../../services/studentService'
import {
  Plus, Search, Edit3, Trash2, UserRound,
  Users, BookOpen, GraduationCap,
  CheckCircle2, XCircle, X, Loader2
} from 'lucide-vue-next'

import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

import StudentCreateModal from '../admin/create/CreateStudentModel.vue'
import StudentEditModal from '../admin/edite/EditeStudent.vue'

// --- State ---
const students = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedGender = ref('')
const activeFilter = ref('')

const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedStudent = ref(null)

const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const deletingStudent = ref(null)

const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

// --- Toast ---
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// --- Fetch ---
const fetchStudents = async () => {
  isLoading.value = true
  try {
    const res = await studentService.getStudents()
    students.value = Array.isArray(res) ? res : (res.data || [])
  } catch (error) {
    console.error('Fetch error:', error)
    students.value = []
    showToast('មិនអាចទាញទិន្នន័យបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

// --- Export ---
const exportToExcel = () => {
  const data = filteredStudents.value.map(s => ({
    'ID Card': s.student_id_card || 'N/A',
    'Name Khmer': s.name_kh,
    'Name English': s.name_en,
    'Gender': s.gender === 'female' ? 'ស្រី' : 'ប្រុស',
    'Email': s.email || 'N/A',
    'Phone': s.phone || 'N/A',
    'Address': `${s.village || ''} ${s.commune || ''} ${s.district || ''} ${s.province || ''}`,
    'Status': s.status == 1 ? 'កំពុងរៀន' : 'ឈប់រៀន'
  }))
  const worksheet = XLSX.utils.json_to_sheet(data)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Students')
  XLSX.writeFile(workbook, `Student_List_${new Date().toISOString().split('T')[0]}.xlsx`)
}

const exportToPDF = () => {
  try {
    const doc = new jsPDF()
    const tableColumn = ['ID Card', 'Full Name', 'Gender', 'Phone', 'Status']
    const tableRows = filteredStudents.value.map(s => [
      s.student_id_card || 'N/A',
      s.name_en || s.name_kh,
      s.gender === 'female' ? 'F' : 'M',
      s.phone || 'N/A',
      s.status == 1 ? 'Active' : 'Dropped'
    ])
    doc.setFontSize(16)
    doc.text('School Management System - Student Report', 14, 15)
    doc.setFontSize(10)
    doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 22)
    autoTable(doc, {
      head: [tableColumn],
      body: tableRows,
      startY: 28,
      theme: 'grid',
      headStyles: { fillColor: [79, 70, 229], fontStyle: 'bold' },
      styles: { fontSize: 9 }
    })
    doc.save(`Student_Report_${Date.now()}.pdf`)
  } catch (error) {
    console.error('PDF Export Error:', error)
    showToast('មានបញ្ហាក្នុងការបង្កើត PDF!', 'error')
  }
}

// --- Filters ---
const handleStatClick = (type, value) => {
  if (type === 'gender') {
    selectedStatus.value = ''
    selectedGender.value = selectedGender.value === value ? '' : value
    activeFilter.value = selectedGender.value
  } else if (type === 'status') {
    selectedGender.value = ''
    selectedStatus.value = selectedStatus.value === value ? '' : value
    activeFilter.value = selectedStatus.value
  } else {
    resetFilters()
  }
}

const resetFilters = () => {
  selectedGender.value = ''
  selectedStatus.value = ''
  activeFilter.value = ''
  searchQuery.value = ''
}

// --- Computed ---
const filteredStudents = computed(() => {
  return (students.value || []).filter(student => {
    const search = searchQuery.value.toLowerCase()
    const matchesSearch =
      (student.name_kh?.toLowerCase().includes(search)) ||
      (student.name_en?.toLowerCase().includes(search)) ||
      (student.student_id_card?.toLowerCase().includes(search)) ||
      (student.phone?.includes(search))
    const matchesStatus = selectedStatus.value === '' || student.status == selectedStatus.value
    const matchesGender = selectedGender.value === '' ||
      student.gender?.toLowerCase() === selectedGender.value.toLowerCase()
    return matchesSearch && matchesStatus && matchesGender
  })
})

const studentStats = computed(() => [
  {
    label: 'សិស្សសរុប',
    value: students.value.length + ' នាក់',
    icon: Users,
    bgColor: 'bg-indigo-50',
    iconColor: 'text-indigo-600',
    filterType: 'all',
    filterValue: ''
  },
  {
    label: 'សិស្សស្រី',
    value: students.value.filter(s => s.gender?.toLowerCase() === 'female').length + ' នាក់',
    icon: GraduationCap,
    bgColor: 'bg-rose-50',
    iconColor: 'text-rose-600',
    filterType: 'gender',
    filterValue: 'female'
  },
  {
    label: 'សិស្សប្រុស',
    value: students.value.filter(s => s.gender?.toLowerCase() === 'male').length + ' នាក់',
    icon: UserRound,
    bgColor: 'bg-blue-50',
    iconColor: 'text-blue-600',
    filterType: 'gender',
    filterValue: 'male'
  },
  {
    label: 'កំពុងរៀន',
    value: students.value.filter(s => s.status == 1).length + ' នាក់',
    icon: BookOpen,
    bgColor: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
    filterType: 'status',
    filterValue: '1'
  }
])

// --- Create / Edit ---
const openCreateModal = () => { showCreateModal.value = true }

const handleEdit = (student) => {
  selectedStudent.value = student
  showEditModal.value = true
}

// --- Delete Modal ---
const openDeleteModal = (student) => {
  deletingStudent.value = student
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  isDeleteModalOpen.value = false
  deletingStudent.value = null
}

const handleCreateStudent = async () => {
  try {
    isLoading.value = true
    showCreateModal.value = false
    await fetchStudents()
    showToast('បង្កើតសិស្សបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចបង្កើតសិស្សបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleUpdateStudent = async () => {
  try {
    isLoading.value = true
    showEditModal.value = false
    await fetchStudents()
    showToast('កែប្រែសិស្សបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចកែប្រែសិស្សបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

const confirmDeleteStudent = async () => {
  if (!deletingStudent.value) return
  try {
    isDeleting.value = true
    await studentService.deleteStudent(deletingStudent.value.id)
    isDeleteModalOpen.value = false
    deletingStudent.value = null
    await fetchStudents()
    showToast('បានលុបទិន្នន័យសិស្សដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast('មិនអាចលុបបានទេ: ' + (error.response?.data?.message || 'Error'), 'error')
  } finally {
    isDeleting.value = false
  }
}

onMounted(fetchStudents)
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-scale-enter-active,
.modal-scale-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-scale-enter-from,
.modal-scale-leave-to {
  transform: scale(0.95);
  opacity: 0;
}

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.toast-slide-enter-from,
.toast-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>