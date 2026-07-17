<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងការប្រឡង</h1>
        <p class="text-sm text-slate-500 mt-1">រៀបចំកាលវិភាគ បញ្ជីឈ្មោះ ស្រង់ពិន្ទុ និងលទ្ធផលប្រឡង</p>
      </div>

      <button v-if="currentTab === 'list'" @click="showCreateModal = true"
        class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
        <Plus class="w-5 h-5" /> បង្កើតការប្រឡងថ្មី
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-8 border-b border-slate-200 mb-6 bg-white px-4 pt-2 rounded-t-xl overflow-x-auto">
      <button @click="currentTab = 'list'" :class="tabClass('list')">
        ១. បញ្ជីការប្រឡង
      </button>
      <button @click="currentTab = 'input'" :class="tabClass('input')">
        ២. ស្រង់ពិន្ទុសិស្ស
      </button>
      <button @click="currentTab = 'report'" :class="tabClass('report')">
        ៣. លទ្ធផលប្រឡង
      </button>
    </div>

    <!-- ============== TAB 1: EXAM LIST ============== -->
    <div v-if="currentTab === 'list'">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div v-for="stat in examStats" :key="stat.label"
          class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
          <div :class="['p-3 rounded-xl', stat.bgColor]">
            <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
          </div>
          <div>
            <p class="text-[12px] text-slate-400 font-bold uppercase tracking-wider">{{ stat.label }}</p>
            <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
          </div>
        </div>
      </div>

      <div
        class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-[14px] font-bold text-slate-600 ml-1">ស្វែងរក</label>
          <div class="relative mt-1">
            <Search class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
            <input v-model="searchQuery" type="text" placeholder="ឈ្មោះការប្រឡង..."
              class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:border-indigo-500 outline-none" />
          </div>
        </div>

        <div>
          <label class="text-[14px] font-bold text-slate-600 ml-1">ឆ្នាំសិក្សា</label>
          <select v-model="selectedYear"
            class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm outline-none focus:border-indigo-500">
            <option value="">ទាំងអស់</option>
            <option v-for="year in academicYearsList" :key="year.id" :value="year.id">{{ year.year_name }}</option>
          </select>
        </div>

        <div>
          <label class="text-[14px] font-bold text-slate-600 ml-1">ប្រភេទការប្រឡង</label>
          <select v-model="selectedType"
            class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm outline-none focus:border-indigo-500">
            <option value="">ទាំងអស់</option>
            <option v-for="type in EXAM_TYPES" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <div v-if="isLoading" class="p-10 text-center text-sm text-slate-500">កំពុងផ្ទុកទិន្នន័យ...</div>
          <table v-else class="w-full text-left">
            <thead>
              <tr class="bg-slate-50/50 border-b border-slate-100">
                <th class="px-6 py-4 text-[13px] font-bold text-slate-600 uppercase">ឈ្មោះការប្រឡង</th>
                <th class="px-6 py-4 text-[13px] font-bold text-slate-600 uppercase">ប្រភេទ</th>
                <th class="px-6 py-4 text-[13px] font-bold text-slate-600 uppercase">កាលបរិច្ឆេទ</th>
                <th class="px-6 py-4 text-[13px] font-bold text-slate-600 uppercase text-right">សកម្មភាព</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="exam in filteredExams" :key="exam.id" class="hover:bg-slate-50/80 transition-colors group">
                <td class="px-6 py-4 font-bold text-slate-700">{{ exam.name }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-indigo-600">{{ exam.type || '-' }}</td>
                <td class="px-6 py-4 text-sm">{{ formatDate(exam.exam_date) }}</td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="openEditModal(exam.id)" title="កែសម្រួល"
                      class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all shadow-sm active:scale-90">
                      <Edit3 class="w-4 h-4" />
                    </button>
                    <button @click="openDeleteExamModal(exam)" title="លុប"
                      class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all shadow-sm active:scale-90">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredExams.length === 0">
                <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-sm">មិនមានទិន្នន័យការប្រឡងឡើយ</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ============== TAB 2: SCORE ENTRY ============== -->
    <!-- Delegated to its own self-contained component (fetches its own classes/subjects/exams,
         and handles its own government-letterhead styled, landscape/centered PDF + Excel export). -->
    <div v-if="currentTab === 'input'">
      <ResultInputScore />
    </div>

    <!-- ============== TAB 3: RESULTS REPORT ============== -->
    <!-- Delegated to its own self-contained component (same pattern as ResultInputScore, portrait export). -->
    <div v-if="currentTab === 'report'">
      <ResultScore />
    </div>

    <EditExam v-if="showEditModal" :examId="editExamId" :classes="classes" @close="showEditModal = false"
      @saved="handleEditSaved" />
    <CreateExamModel v-if="showCreateModal" @close="showCreateModal = false" @saved="handleSaved" :subjects="subjects"
      :classes="classes" />

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
        <div v-if="isDeleteExamModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(15, 15, 20, 0.5);" @click.self="closeDeleteExamModal">
          <Transition name="modal-scale">
            <div v-if="isDeleteExamModalOpen"
              class="bg-white rounded-2xl border border-slate-200 w-full max-w-sm p-8 text-center font-[battambang]">

              <div class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center mx-auto mb-5">
                <Trash2 class="w-7 h-7 text-rose-500 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]" />
              </div>

              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបវិញ្ញាសាប្រឡង</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingExam?.name }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះនឹងលុបពិន្ទុទាំងអស់ដែលពាក់ព័ន្ធ។</p>

              <div class="flex gap-3">
                <button @click="closeDeleteExamModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteExam" :disabled="isDeleting"
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
import { Plus, Search, ClipboardCheck, Edit3, Trash2, CheckCircle2, XCircle, X, Loader2 } from 'lucide-vue-next'
import api from '@/services/authService'
import CreateExamModel from '../../admin/create/CreateExamModel.vue'
import EditExam from '../edite/EditeExam.vue'
import ResultInputScore from './ResultInputScore.vue'
import ResultScore from './ResultScore.vue'

defineOptions({ name: 'Exam' })

// Fixed list of exam type options used in the tab-1 filter
const EXAM_TYPES = ['ប្រចាំខែ', 'ឆមាស', 'ប្រចាំឆ្នាំ']

// --- Shared data (only what tab 1 / the create & edit modals need) ---
const exams = ref([])
const subjects = ref([])
const classes = ref([])
const academicYearsList = ref([])
const isLoading = ref(false)
const currentTab = ref('list')

// --- Toast ---
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

const tabClass = (tab) => [
  'pb-3 font-bold text-base transition-all duration-200 border-b-2 whitespace-nowrap',
  currentTab.value === tab ? 'text-indigo-600 border-indigo-600' : 'text-slate-500 border-transparent hover:text-indigo-500'
]

// --- TAB 1: Exam list state ---
const searchQuery = ref('')
const selectedYear = ref('')
const selectedType = ref('')
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editExamId = ref(null)

const filteredExams = computed(() => {
  return exams.value.filter(e => {
    const matchesSearch = e.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesYear = selectedYear.value === '' || e.year_id === selectedYear.value
    const matchesType = selectedType.value === '' || e.type === selectedType.value
    return matchesSearch && matchesYear && matchesType
  })
})

const examStats = computed(() => [
  { label: 'សរុប', value: exams.value.length + ' លើក', icon: ClipboardCheck, bgColor: 'bg-indigo-50', iconColor: 'text-indigo-600' },
])

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Intl.DateTimeFormat('km-KH', { year: 'numeric', month: 'numeric', day: 'numeric' }).format(new Date(dateString))
}

const openEditModal = (id) => { editExamId.value = id; showEditModal.value = true }
const handleSaved = () => { showCreateModal.value = false; fetchData(); showToast('បានបង្កើតការប្រឡងដោយជោគជ័យ!') }
const handleEditSaved = () => { showEditModal.value = false; fetchData(); showToast('បានកែសម្រួលការប្រឡងដោយជោគជ័យ!') }

// --- TAB 1: Delete exam (wired to confirmation modal) ---
const isDeleteExamModalOpen = ref(false)
const deletingExam = ref(null)
const isDeleting = ref(false)

const openDeleteExamModal = (exam) => {
  deletingExam.value = exam
  isDeleteExamModalOpen.value = true
}

const closeDeleteExamModal = () => {
  if (isDeleting.value) return
  isDeleteExamModalOpen.value = false
  deletingExam.value = null
}

const confirmDeleteExam = async () => {
  if (!deletingExam.value) return
  isDeleting.value = true
  try {
    await api.delete(`/exams/${deletingExam.value.id}`)
    showToast('បានលុបការប្រឡងដោយជោគជ័យ!', 'success')
    isDeleteExamModalOpen.value = false
    deletingExam.value = null
    await fetchData()
  } catch (err) {
    console.error(err)
    showToast('មានបញ្ហាក្នុងការលុបការប្រឡង: ' + (err.response?.data?.message || 'Error'), 'error')
  } finally {
    isDeleting.value = false
  }
}

const fetchData = async () => {
  isLoading.value = true
  try {
    const [examRes, yearRes, classRes, subjectRes] = await Promise.all([
      api.get('/exams'),
      api.get('/years'),
      api.get('/classes'),
      api.get('/subjects')
    ])
    exams.value = Array.isArray(examRes.data) ? examRes.data : (examRes.data.data || [])
    academicYearsList.value = Array.isArray(yearRes.data) ? yearRes.data : (yearRes.data.data || [])
    classes.value = Array.isArray(classRes.data) ? classRes.data : (classRes.data.data || [])
    subjects.value = Array.isArray(subjectRes.data) ? subjectRes.data : (subjectRes.data.data || [])
  } catch (err) {
    console.error(err)
    showToast('មានបញ្ហាក្នុងការទាញយកទិន្នន័យ', 'error')
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchData)
</script>

<style>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-scale-enter-active,
.modal-scale-leave-active {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-scale-enter-from,
.modal-scale-leave-to {
  transform: scale(0.9);
  opacity: 0;
}

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.3s ease;
}

.toast-slide-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.toast-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>