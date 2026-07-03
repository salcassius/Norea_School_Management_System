<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col gap-4 mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងគ្រូបង្រៀន</h1>
          <p class="text-sm text-slate-500 mt-1">គ្រប់គ្រងព័ត៌មាន និងឯកទេសរបស់លោកគ្រូ អ្នកគ្រូក្នុងប្រព័ន្ធ</p>
        </div>

        <button @click="showCreateModal = true"
          class="w-full md:w-auto flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          បន្ថែមគ្រូបង្រៀនថ្មី
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in teacherStats" :key="stat.label" @click="handleStatClick(stat.filterType, stat.filterValue)"
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

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 w-full p-4">
        <div class="flex items-center gap-3 w-full md:max-w-sm">
          <div class="relative w-full">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ, ID..."
              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
          </div>
          <button v-if="searchQuery" @click="searchQuery = ''"
            class="text-xs text-indigo-600 font-medium hover:underline shrink-0">លុប</button>
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
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ថ្ងៃចូលបម្រើការងារ
              </th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ថ្ងៃខែឆ្នាំកំណើត</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ទីកន្លែងកំណើត</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">អាសយដ្ឋានបច្ចុប្បន្ន
              </th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="teacher in filteredTeachers" :key="teacher.id"
              class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 shadow-sm flex items-center justify-center">
                    <!-- Photo loads fine and hasn't errored -->
                    <img v-if="teacher.photo && !brokenPhotos.has(teacher.id)" :src="getPhotoUrl(teacher.photo)"
                      @error="onPhotoError(teacher.id)" class="w-full h-full object-cover" />
                    <!-- No photo on record at all -->
                    <UserRound v-else-if="!teacher.photo" class="w-5 h-5 text-slate-400" />
                    <!-- Had a photo path but it failed to load -->
                    <span v-else class="text-sm font-bold text-indigo-600 uppercase">{{ (teacher.name_kh ||
                      'T').charAt(0) }}</span>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 leading-tight">{{ teacher.name_kh }}</p>
                    <p class="text-[11px] text-slate-600 mt-1 uppercase tracking-tighter">{{ teacher.name_en }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="sub in getSpecialties(teacher.specialty).slice(0, 2)" :key="sub"
                    class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[14px] font-medium border border-slate-200">
                    {{ sub }}
                  </span>
                  <span v-if="getSpecialties(teacher.specialty).length > 2"
                    class="text-[10px] text-slate-400 font-bold ml-1">
                    +{{ getSpecialties(teacher.specialty).length - 2 }}
                  </span>
                  <span v-if="getSpecialties(teacher.specialty).length === 0"
                    class="text-[10px] text-slate-300 italic">គ្មាន</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-slate-600 font-medium">{{ teacher.phone || 'N/A' }}</p>
                <p class="text-[13px] text-slate-600 truncate max-w-[150px]">{{ teacher.user?.email || 'គ្មានគណនី' }}
                </p>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600">
                {{ teacher.hire_date ? new Date(teacher.hire_date).toLocaleDateString('km-KH') : 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-slate-600">
                {{ teacher.dob ? new Date(teacher.dob).toLocaleDateString('km-KH') : 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-slate-600">
                <div class="text-[15px]">
                  {{ teacher.pob_village }}, {{ teacher.pob_commune }}, {{ teacher.pob_district }}, {{ teacher.province
                  }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600">
                <div class="text-[15px]">
                  {{ teacher.village }}, {{ teacher.commune }}, {{ teacher.district }}, {{ teacher.province }}
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-[13px] font-bold uppercase tracking-wider border',
                  teacher.status == 1 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                ]">
                  {{ teacher.status == 1 ? 'នៅបង្រៀន' : 'ឈប់បង្រៀន/ផ្អាក' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(teacher)"
                    class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-90 shadow-sm"
                    title="Edit">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="openDeleteModal(teacher)"
                    class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all active:scale-90 shadow-sm"
                    title="Delete">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredTeachers.length === 0">
              <td colspan="9" class="px-6 py-10 text-center text-slate-400 text-sm">មិនមានទិន្នន័យគ្រូបង្រៀនឡើយ</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <TeacherCreateModal v-if="showCreateModal" v-model="showCreateModal" :loading="isLoading"
      @refresh="handleCreateTeacher" />

    <EditeTeacher v-if="isEditModalOpen" v-model="isEditModalOpen" :teacher-data="selectedTeacher" :loading="isLoading"
      @refresh="handleUpdateTeacher" />

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

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(15, 15, 20, 0.5);" @click.self="closeDeleteModal">

          <Transition name="modal-scale">
            <div v-if="isDeleteModalOpen"
              class="bg-white rounded-2xl border border-slate-200 w-full max-w-sm p-8 text-center font-[Battambang]">

              <div class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center mx-auto mb-5">
                <Trash2 class="w-7 h-7 text-rose-500 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]" />
              </div>

              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបគ្រូបង្រៀន</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុបគ្រូបង្រៀន
                <span class="font-bold text-blue-700">{{ deletingTeacher?.name_kh }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteTeacher" :disabled="isDeleting"
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
import teacherService from '../../services/teacherService'
import {
  Plus, Search, Edit3, Trash2, UserRound,
  GraduationCap, Users, UserCheck, UserMinus,
  CheckCircle2, XCircle, X, Loader2
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
const selectedStatus = ref('')
const selectedGender = ref('')
const activeFilter = ref('')
const showCreateModal = ref(false)
const isEditModalOpen = ref(false)
const selectedTeacher = ref(null)
const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const deletingTeacher = ref(null)

// tracks teacher IDs whose photo failed to load -> falls back to initial-letter avatar
const brokenPhotos = ref(new Set())

const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

// --- Toast ---
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

const API_BASE_URL = (
  import.meta.env.VITE_API_BASE_URL ||
  import.meta.env.VITE_API_URL ||
  'http://localhost:8000'
).replace(/\/+$/, '')

const getPhotoUrl = (path) => {
  if (!path) return null
  if (/^https?:\/\//i.test(path)) return path // already absolute
  const cleanPath = path.replace(/^\/?storage\/?/, '') // avoid double "storage/storage"
  return `${API_BASE_URL}/storage/${cleanPath}`
}

const onPhotoError = (teacherId) => {
  brokenPhotos.value.add(teacherId)
}

// --- Fetch ---
const fetchTeachers = async () => {
  isLoading.value = true
  try {
    const res = await teacherService.getTeachers()
    teachers.value = Array.isArray(res) ? res : (res.data || [])
    brokenPhotos.value = new Set()
  } catch (error) {
    console.error('Fetch Error:', error)
    showToast('មិនអាចទាញទិន្នន័យបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

// --- Filters ---
const handleStatClick = (type, value) => {
  if (type === 'gender') {
    selectedGender.value = selectedGender.value === value ? '' : value
    activeFilter.value = selectedGender.value
  } else if (type === 'status') {
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

// --- Helpers ---
const getSpecialties = (data) => {
  if (!data) return []
  try {
    return typeof data === 'string' ? JSON.parse(data) : data
  } catch {
    return [data]
  }
}

// --- Edit Modal ---
const openEditModal = (teacher) => {
  selectedTeacher.value = teacher
  isEditModalOpen.value = true
}

// --- Delete Modal ---
const openDeleteModal = (teacher) => {
  deletingTeacher.value = teacher
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false
  deletingTeacher.value = null
}

// --- Create Teacher Handler ---
// The create modal already performs the API call itself and only emits
// 'refresh' once it succeeds, so this just needs to close the modal,
// refresh the table, and show a toast.
const handleCreateTeacher = async () => {
  try {
    isLoading.value = true
    showCreateModal.value = false
    await fetchTeachers()
    showToast('បង្កើតគ្រូបង្រៀនបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចបង្កើតគ្រូបង្រៀនបានទេ', 'error')
  } finally {
    isLoading.value = false
  }
}

// --- Edit Teacher Handler ---
// Same idea: EditeTeacher.vue already performed the API update itself.
const handleUpdateTeacher = async () => {
  try {
    isLoading.value = true
    isEditModalOpen.value = false
    await fetchTeachers()
    showToast('កែប្រែគ្រូបង្រៀនបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចកែប្រែគ្រូបង្រៀនបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

const confirmDeleteTeacher = async () => {
  if (!deletingTeacher.value) return
  try {
    isDeleting.value = true
    await teacherService.deleteTeacher(deletingTeacher.value.id)
    closeDeleteModal()
    await fetchTeachers()
    showToast('បានលុបឈ្មោះគ្រូដោយជោគជ័យ!', 'success')
  } catch (err) {
    showToast('មិនអាចលុបគ្រូនេះបានទេ: ' + (err.response?.data?.message || 'Error'), 'error')
  } finally {
    isDeleting.value = false
  }
}

// --- Computed ---
const filteredTeachers = computed(() => {
  return teachers.value.filter(t => {
    const query = searchQuery.value.toLowerCase().trim()
    const matchesSearch = !query ||
      t.name_kh?.toLowerCase().includes(query) ||
      t.name_en?.toLowerCase().includes(query) ||
      t.phone?.includes(query) ||
      t.teacher_id_card?.toLowerCase().includes(query)

    const matchesStatus = selectedStatus.value === '' || t.status == selectedStatus.value
    const matchesGender = selectedGender.value === '' ||
      ['female', 'ស្រី'].includes(t.gender?.toLowerCase()) === (selectedGender.value === 'female')

    return matchesSearch && matchesStatus && matchesGender
  })
})

const teacherStats = computed(() => {
  const list = teachers.value || []
  return [
    { label: 'គ្រូសរុប', value: list.length + ' នាក់', icon: GraduationCap, bgColor: 'bg-indigo-50', iconColor: 'text-indigo-600', filterType: '', filterValue: '' },
    { label: 'គ្រូសកម្ម', value: list.filter(t => t.status == 1).length + ' នាក់', icon: UserCheck, bgColor: 'bg-emerald-50', iconColor: 'text-emerald-600', filterType: 'status', filterValue: '1' },
    { label: 'គ្រូស្រី', value: list.filter(t => ['female', 'ស្រី'].includes(t.gender?.toLowerCase())).length + ' នាក់', icon: Users, bgColor: 'bg-rose-50', iconColor: 'text-rose-600', filterType: 'gender', filterValue: 'female' },
    { label: 'គ្រូផ្អាក', value: list.filter(t => t.status == 0).length + ' នាក់', icon: UserMinus, bgColor: 'bg-slate-100', iconColor: 'text-slate-500', filterType: 'status', filterValue: '0' }
  ]
})

// --- Export ---
const exportToExcel = () => {
  const data = filteredTeachers.value.map(t => ({
    'ឈ្មោះខ្មែរ': t.name_kh || '',
    'ឈ្មោះអង់គ្លេស': t.name_en || '',
    'ឯកទេស': getSpecialties(t.specialty).join(', '),
    'លេខទូរស័ព្ទ': t.phone || '',
    'អ៊ីម៉ែល': t.user?.email || '',
    'ថ្ងៃចូលបម្រើការងារ': t.hire_date ? new Date(t.hire_date).toLocaleDateString('km-KH') : '',
    'ថ្ងៃខែឆ្នាំកំណើត': t.dob ? new Date(t.dob).toLocaleDateString('km-KH') : '',
    'ស្ថានភាព': t.status == 1 ? 'សកម្ម' : 'ផ្អាក'
  }))
  const ws = XLSX.utils.json_to_sheet(data)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Teachers')
  XLSX.writeFile(wb, 'teachers.xlsx')
}

const exportToPDF = () => {
  const doc = new jsPDF({ orientation: 'landscape' })
  const rows = filteredTeachers.value.map(t => [
    t.name_kh || '',
    t.name_en || '',
    getSpecialties(t.specialty).join(', '),
    t.phone || '',
    t.user?.email || '',
    t.hire_date ? new Date(t.hire_date).toLocaleDateString('km-KH') : '',
    t.status == 1 ? 'Active' : 'Inactive'
  ])
  autoTable(doc, {
    head: [['ឈ្មោះខ្មែរ', 'ឈ្មោះអង់គ្លេស', 'ឯកទេស', 'ទូរស័ព្ទ', 'អ៊ីម៉ែល', 'ថ្ងៃចូលបម្រើ', 'ស្ថានភាព']],
    body: rows,
    styles: { fontSize: 9 },
    headStyles: { fillColor: [79, 70, 229] }
  })
  doc.save('teachers.pdf')
}

onMounted(fetchTeachers)
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

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