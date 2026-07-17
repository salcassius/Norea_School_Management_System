<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">
    <div class="max-w-7xl mx-auto space-y-6">

      <!-- Header + class picker: always visible, never swapped out -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 font-[Battambang]">គ្រប់គ្រងកាលវិភាគសិក្សា</h1>
          <p class="text-sm text-slate-500 mt-1">សូមជ្រើសរើសថ្នាក់រៀន ដើម្បីមើល ឬរៀបចំកាលវិភាគសិក្សា</p>
        </div>
      </div>

      <div v-if="studentClasses.length === 0"
        class="p-8 text-center bg-white rounded-2xl border border-slate-200/80 shadow-sm max-w-64">
        <p class="text-slate-400 font-medium text-sm">មិនមានទិន្នន័យថ្នាក់រៀនសម្រាប់ឆ្នាំសិក្សាដែលបានជ្រើសរើសឡើយ</p>
      </div>

      <div v-else class="w-full md:w-64">
        <label class="text-[15px] font-bold text-slate-600 ml-1">សូមជ្រើសរើសថ្នាក់</label>
        <select v-model="filterClass"
          class="w-full mt-1 bg-slate-50 border border-slate-300 rounded-lg py-2 px-3 text-[14px] outline-none focus:border-indigo-500 cursor-pointer">
          <option value="" disabled>ជ្រើសរើសថ្នាក់</option>
          <option v-for="cls in studentClasses" :key="cls.id" :value="cls.id">
           ថ្នាក់ទី {{ cls.grade_level }}{{ cls.name }}
          </option>
        </select>
      </div>

      <!-- Schedule section: appears inline below once a class is picked, no view-swap -->
      <div v-if="selectedClassData" class="space-y-4 animate-in fade-in duration-300">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">

          <div>
            <h2 class="text-lg font-bold text-slate-800 font-[battambang]">
              កាលវិភាគថ្នាក់ទី៖ {{ selectedClassData?.grade_level }}{{ selectedClassData?.name }}
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">រៀបចំ និងតាមដានម៉ោងសិក្សាប្រចាំសប្តាហ៍</p>
          </div>

          <!-- ប៊ូតុងទាញយក Excel និង PDF -->
          <div class="flex items-center gap-3 flex-wrap">
            <button v-if="selectedClassData" @click="exportToExcel"
              class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
               ទាញយក Excel
            </button>
            <button v-if="selectedClassData" @click="importPdf"
              class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
              ទាញយក PDF
            </button>
          </div>

        </div>

        <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
          <div v-if="isScheduleLoading" class="p-12 text-center text-slate-400 font-medium">
            កំពុងទាញយកទិន្នន័យកាលវិភាគ...
          </div>
          <div v-else id="scheduleTable" class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[1200px] border-collapse">
              <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                  <th
                    class="p-4 text-[14px] font-bold text-slate-600 uppercase tracking-wider text-center w-36 border-r border-slate-100">
                    ម៉ោងសិក្សា</th>
                  <th v-for="day in days" :key="day" class="p-4 text-sm font-bold text-slate-700 text-center">{{ day }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(slot, idx) in timeSlots" :key="idx" class="group">
                  <td class="p-4 border-r border-slate-100 text-center bg-slate-50/50">
                    <span
                      class="text-[14px] font-bold text-indigo-600 bg-indigo-50/60 border border-indigo-100 px-2.5 py-1 rounded-lg">{{
                        slot.time }}</span>
                  </td>
                  <td v-for="day in days" :key="day" class="p-2.5 relative min-h-[110px] align-top">

                    <div v-if="getScheduleEntry(day, slot.time)"
                      :class="['p-3 h-[85px] rounded-xl border shadow-sm w-full group/card transition-all w-fit min-w-[160px]', getSubjectTheme(getScheduleEntry(day, slot.time).subject?.name)]">

                      <div class="flex justify-between items-start gap-1">
                        <p class="text-[16px] font-bold truncate">
                          {{ getScheduleEntry(day, slot.time).subject?.name || '---' }}
                        </p>

                        <div class="flex gap-0.5 opacity-0 group-hover/card:opacity-100 transition-opacity no-print">
                          <button @click.stop="openEditModal(getScheduleEntry(day, slot.time))"
                            class="text-indigo-600 hover:bg-indigo-100 p-0.5 rounded cursor-pointer">
                            <Edit3 class="w-3 h-3" />
                          </button>
                          <button @click.stop="openDeleteModal(getScheduleEntry(day, slot.time))"
                            class="text-rose-600 hover:bg-rose-100 p-0.5 rounded cursor-pointer">
                            <Trash2 class="w-3 h-3" />
                          </button>
                        </div>
                      </div>

                      <p class="text-[16px] text-slate-700 font-medium truncate mt-0.5">
                        {{ getScheduleEntry(day, slot.time).teacher?.name_kh || 'មិនមានគ្រូ' }}
                      </p>
                    </div>

                    <div v-else @click="openCreateModal(day, slot.time)"
                      class="w-full h-[85px] rounded-xl border border-dashed border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/20 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer no-print">
                      <PlusCircle class="w-5 h-5 text-indigo-400" />
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Modals -->
    <!--
      ចំណាំសំខាន់៖ CreateScheduleModel.vue និង EditeSchedule.vue ត្រូវ emit 2 events:
        1. @saved -> ពេលរក្សាទុកជោគជ័យ (200/201)
        2. @error -> ពេលមានបញ្ហា (ឧ. គ្រូបង្រៀនជាន់ម៉ោង / ថ្នាក់ជាន់ម៉ោង) ដោយផ្ញើសារជា payload
           ឧទាហរណ៍ក្នុង Modal នីមួយៗ៖
             try {
               await api.post('/schedules', form)   // ឬ api.put(`/schedules/${id}`, form)
               emit('saved')
             } catch (error) {
               emit('error', error.response?.data?.message || 'មិនអាចរក្សាទុកកាលវិភាគបានទេ!')
             }
    -->
    <CreateSchedule v-if="showCreateModal" :initData="modalFormPayload" :subjects="subjects" :teachers="teachers"
      @close="showCreateModal = false" @saved="handleScheduleSaved" @error="handleScheduleError" />
    <EditSchedule v-if="showEditModal" :entryData="modalFormPayload" :subjects="subjects" :teachers="teachers"
      @close="showEditModal = false" @saved="handleScheduleSaved" @error="handleScheduleError" />

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.show" :class="[
          'fixed top-5 right-5 z-[100] flex items-center gap-3 px-4 py-3 rounded-2xl border shadow-lg min-w-[260px] max-w-xs font-[Battambang]',
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

              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបម៉ោងសិក្សា</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingEntry?.subject?.name || 'ម៉ោងសិក្សានេះ' }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50 cursor-pointer">
                  បោះបង់
                </button>
                <button @click="confirmDeleteEntry" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white bg-rose-500 hover:bg-rose-600 flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-60 cursor-pointer">
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

    <!-- ========================================================================================= -->
    <!-- Hidden Official Letterhead Export Area (For Landscape PDF Export - Compact 1 Page Fit) -->
    <!-- ========================================================================================= -->
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
        កាលវិភាគសិក្សាថ្នាក់ទី៖ {{ selectedClassData?.grade_level }}{{ selectedClassData?.name }}
      </h2>

      <!-- តារាងកាលវិភាគសម្រាប់ PDF (បានបង្រួម Padding កុំឱ្យលោតចូល Page 2) -->
      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width:12%">ម៉ោងសិក្សា</th>
            <th v-for="day in days" :key="'pdf-' + day" style="width:14.6%">{{ day }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(slot, idx) in timeSlots" :key="'pdf-row-' + idx">
            <td style="font-weight:bold; background:#f8fafc; font-size: 11px;">{{ slot.time }}</td>
            <td v-for="day in days" :key="'pdf-cell-' + day" style="vertical-align: middle; padding: 4px 2px;">
              <div v-if="getScheduleEntry(day, slot.time)">
                <div style="font-weight:bold; font-size:11px; color:#1e293b; line-height: 1.3;">
                  {{ getScheduleEntry(day, slot.time).subject?.name || '-' }}
                </div>
                <div style="font-size:10px; color:#475569; margin-top:2px;">
                  {{ getScheduleEntry(day, slot.time).teacher?.name_kh || 'មិនមានគ្រូ' }}
                </div>
              </div>
              <div v-else style="color:#cbd5e1; font-size:11px;">---</div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- កន្លែងហត្ថលេខា នាយកសាលា (ឆ្វេង) និង គ្រូបន្ទប់ថ្នាក់ (ស្ដាំ) -->
      <div class="doc-footer">
        <div class="signature-left">
          <p class="moul">បានឃើញ និងឯកភាព</p>
          <p class="moul">នាយកសាលា</p>
        </div>
        <div class="signature-right">
          <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
          <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
          <p class="moul">គ្រូបន្ទប់ថ្នាក់</p>
          <p class="teacher-name">{{ homeroomTeacherName }}</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { FileText, FileSpreadsheet, MapPin, ChevronRight, ArrowLeft, School, LayoutDashboard, Edit3, Trash2, PlusCircle, Loader2, CheckCircle2, XCircle, X } from 'lucide-vue-next'
import api from '../../services/authService'
import CreateSchedule from '../admin/create/CreateScheduleModel.vue'
import EditSchedule from '../admin/edite/EditeSchedule.vue'

// Export libraries
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'

// ─── Toast ────────────────────────────────────────────────────────────────────
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// ─── States ───────────────────────────────────────────────────────────────────
const selectedClassData = ref(null)
const selectedShift = ref('morning')
const isClassesLoading = ref(false)
const isScheduleLoading = ref(false)
const printArea = ref(null)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const modalFormPayload = ref(null)

const selectedYearId = ref(null)
const filterClass = ref('')
const academicYears = ref([])
const classes = ref([])
const subjects = ref([])
const teachers = ref([])
const scheduleData = ref([])

// Delete modal state
const isDeleteModalOpen = ref(false)
const deletingEntry = ref(null)
const isDeleting = ref(false)

const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

const days = ['ច័ន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍']

const timeSlots = [
  { time: '07:00 - 08:00', shift: 'morning' },
  { time: '08:00 - 09:00', shift: 'morning' },
  { time: '09:00 - 10:00', shift: 'morning' },
  { time: '10:00 - 11:00', shift: 'morning' },
  { time: '02:00 - 03:00', shift: 'afternoon' },
  { time: '03:00 - 04:00', shift: 'afternoon' },
  { time: '04:00 - 05:00', shift: 'afternoon' }
]

const ensureArray = (payload) => {
  if (!payload) return []
  if (Array.isArray(payload)) return payload
  if (payload.data && Array.isArray(payload.data)) return payload.data
  return typeof payload === 'object' ? Object.values(payload).filter(i => typeof i === 'object') : []
}

// ទាញយកឈ្មោះគ្រូបន្ទប់ថ្នាក់មកបង្ហាញក្នុងហត្ថលេខា
const homeroomTeacherName = computed(() => {
  if (!selectedClassData.value) return '...........................................'
  const t = selectedClassData.value.teacher
  return t?.name_kh || t?.name || '...........................................'
})

// ─── Fetch ────────────────────────────────────────────────────────────────────
const fetchInitialData = async () => {
  isClassesLoading.value = true
  try {
    const [yearsRes, classesRes, subjectsRes, teachersRes] = await Promise.all([
      api.get('/years'),
      api.get('/classes'),
      api.get('/subjects'),
      api.get('/teachers')
    ])

    academicYears.value = ensureArray(yearsRes.data?.data || yearsRes.data)
    classes.value = ensureArray(classesRes.data?.data || classesRes.data)
    subjects.value = ensureArray(subjectsRes.data?.data || subjectsRes.data)
    teachers.value = ensureArray(teachersRes.data?.data || teachersRes.data)

    if (academicYears.value.length > 0) {
      const activeYear = academicYears.value.find(y => y.is_active == 1 || y.is_active == true)
      selectedYearId.value = activeYear ? activeYear.id : academicYears.value[0].id
    }
  } catch (error) {
    console.error('💥 មានកំហុសក្នុងការទាញយកទិន្នន័យដំបូង៖', error)
    showToast('មិនអាចទាញយកទិន្នន័យដំបូងបានទេ', 'error')
  } finally {
    isClassesLoading.value = false
  }
}

const fetchSchedules = async () => {
  if (!selectedYearId.value || !selectedClassData.value) return
  isScheduleLoading.value = true
  try {
    const response = await api.get('/schedules', {
      params: {
        year_id: selectedYearId.value,
        class_id: selectedClassData.value.id,
        shift: selectedShift.value
      }
    })
    scheduleData.value = ensureArray(response.data?.data || response.data)
  } catch (error) {
    console.error('មិនអាចទាញទិន្នន័យកាលវិភាគបានឡើយ៖', error)
    scheduleData.value = []
    showToast('មិនអាចទាញទិន្នន័យកាលវិភាគបានទេ', 'error')
  } finally {
    isScheduleLoading.value = false
  }
}

onMounted(() => {
  fetchInitialData()
})

// ─── Create / Edit ────────────────────────────────────────────────────────────
// ✅ ហៅពេលរក្សាទុកជោគជ័យ (emit 'saved' ពី CreateScheduleModel.vue / EditeSchedule.vue)
const handleScheduleSaved = () => {
  const wasCreate = showCreateModal.value
  const wasEdit = showEditModal.value

  showCreateModal.value = false
  showEditModal.value = false

  fetchSchedules()

  if (wasCreate) {
    showToast('បង្កើតកាលវិភាគបានជោគជ័យ!', 'success')
  } else if (wasEdit) {
    showToast('កែប្រែកាលវិភាគបានជោគជ័យ!', 'success')
  }
}

// ✅ ហៅពេលរក្សាទុកបរាជ័យ (ឧ. គ្រូបង្រៀនជាន់ម៉ោង / ថ្នាក់ជាន់ម៉ោង)
// (emit 'error' ពី CreateScheduleModel.vue / EditeSchedule.vue)
// Modal នៅតែបើកចោល ដើម្បីអោយអ្នកប្រើប្រាស់អាចកែព័ត៌មានហើយសាកល្បងម្តងទៀត
const handleScheduleError = (message) => {
  showToast(message || 'ម៉ោងសិក្សានេះជាន់គ្នា សូមពិនិត្យមើលឡើងវិញ!', 'error')
}

// ─── Delete ───────────────────────────────────────────────────────────────────
const openDeleteModal = (entry) => {
  if (!entry) return
  deletingEntry.value = entry
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  isDeleteModalOpen.value = false
  deletingEntry.value = null
}

const confirmDeleteEntry = async () => {
  if (!deletingEntry.value) return
  try {
    isDeleting.value = true
    const response = await api.delete(`/schedules/${deletingEntry.value.id}`)
    scheduleData.value = scheduleData.value.filter(item => item.id !== deletingEntry.value.id)
    isDeleteModalOpen.value = false
    deletingEntry.value = null
    showToast(response.data?.message || 'បានលុបម៉ោងសិក្សាដោយជោគជ័យ!', 'success')
  } catch (error) {
    console.error('មិនអាចលុបម៉ោងសិក្សានេះបានឡើយ៖', error)
    showToast(error.response?.data?.message || 'មិនអាចលុបម៉ោងសិក្សានេះបានឡើយ!', 'error')
  } finally {
    isDeleting.value = false
  }
}

// ─── Class / year selection ───────────────────────────────────────────────────
const filteredClasses = computed(() => {
  return classes.value.filter(cls => Number(cls.academic_year_id || cls.year_id) === Number(selectedYearId.value))
})

const studentClasses = computed(() => filteredClasses.value)

watch(filterClass, (newId) => {
  if (!newId) return
  const cls = classes.value.find(c => Number(c.id) === Number(newId))
  if (cls) {
    handleSelectClass(cls)
  }
})

const handleYearChange = () => {
  selectedClassData.value = null
  filterClass.value = ''
  scheduleData.value = []
}

const handleSelectClass = (cls) => {
  selectedClassData.value = cls
  fetchSchedules()
}

const getScheduleEntry = (day, time) => {
  if (!scheduleData.value || scheduleData.value.length === 0) return null;

  return scheduleData.value.find(entry => {
    const dbDay = String(entry.day).trim();
    const tableDay = String(day).trim();

    const dbTime = String(entry.time).trim().substring(0, 5);
    const tableTime = String(time).trim().substring(0, 5);

    return dbDay === tableDay && dbTime === tableTime;
  });
}

const getSubjectTheme = (name) => {
  if (name?.includes('គណិត')) return 'bg-indigo-50/80 text-indigo-700 border-indigo-200'
  if (name?.includes('ខ្មែរ')) return 'bg-amber-50/80 text-amber-700 border-amber-200'
  if (name?.includes('រូប')) return 'bg-purple-50/80 text-purple-700 border-purple-200'
  return 'bg-slate-50 text-slate-700 border-slate-200'
}

const openCreateModal = (day, time) => {
  modalFormPayload.value = {
    id: null,
    year_id: Number(selectedYearId.value),
    class_id: Number(selectedClassData.value?.id),
    shift: selectedShift.value,
    day: day,
    time: time,
    subject_id: '',
    teacher_id: '',
    room: selectedClassData.value?.room || selectedClassData.value?.name || 'ទូទៅ'
  }
  showCreateModal.value = true
}

const openEditModal = (entry) => {
  modalFormPayload.value = {
    id: entry.id,
    year_id: Number(entry.year_id),
    class_id: Number(entry.class_id),
    shift: entry.shift,
    day: entry.day,
    time: entry.time,
    subject_id: entry.subject_id,
    teacher_id: entry.teacher_id,
    room: entry.room
  }
  showEditModal.value = true
}

// ─── Export Excel ─────────────────────────────────────────────────────────────
const THIN_BORDER = { style: 'thin', color: { rgb: '000000' } }
const ALL_BORDERS = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER }

const kingdomStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 15 }, alignment: { horizontal: 'center', vertical: 'center' } }
const mottoStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 }, alignment: { horizontal: 'center', vertical: 'center' } }
const officeStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 }, alignment: { horizontal: 'left', vertical: 'center' } }
const titleStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 14 }, alignment: { horizontal: 'center', vertical: 'center' } }
const tableHeaderStyle = { font: { name: 'Khmer OS Battambang', bold: true, sz: 11 }, alignment: { horizontal: 'center', vertical: 'center', wrapText: true }, border: ALL_BORDERS, fill: { fgColor: { rgb: 'F1F5F9' } } }
const tableCellStyle = { font: { name: 'Khmer OS Battambang', sz: 10 }, alignment: { horizontal: 'center', vertical: 'center', wrapText: true }, border: ALL_BORDERS }
const dateLineStyle = { font: { name: 'Khmer OS Battambang', sz: 11 }, alignment: { horizontal: 'right', vertical: 'center' } }
const moulCenterStyle = { font: { name: 'Khmer OS Muol Light', sz: 11 }, alignment: { horizontal: 'center', vertical: 'center' } }
const moulRightStyle = { font: { name: 'Khmer OS Muol Light', sz: 11 }, alignment: { horizontal: 'right', vertical: 'center', indent: 2 } }
const teacherNameStyle = { font: { name: 'Khmer OS Battambang', bold: true, sz: 11 }, alignment: { horizontal: 'right', vertical: 'center', indent: 2 } }

const styleCell = (ws, r, c, style) => {
  const addr = XLSX.utils.encode_cell({ r, c })
  if (!ws[addr]) ws[addr] = { t: 's', v: '' }
  ws[addr].s = style
}

const exportToExcel = () => {
  if (!selectedClassData.value) {
    showToast('សូមជ្រើសរើសថ្នាក់ជាមុនសិន!', 'error')
    return
  }

  const tableHeader = ['ម៉ោងសិក្សា', ...days]
  const colCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine1.value],
    [ministryLine2.value],
    [`កាលវិភាគសិក្សាថ្នាក់ទី៖ ${selectedClassData.value.grade_level}${selectedClassData.value.name}`],
    [],
  ]

  const tableRows = timeSlots.map(slot => {
    const row = [slot.time]
    days.forEach(day => {
      const entry = getScheduleEntry(day, slot.time)
      if (entry) {
        const subName = entry.subject?.name || '-'
        const tName = entry.teacher?.name_kh || 'មិនមានគ្រូ'
        row.push(`${subName}\n(${tName})`)
      } else {
        row.push('---')
      }
    })
    return row
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

  const dateRow1Idx = pushRow(['', '', '', '', '', 'ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............'])
  const dateRow2Idx = pushRow(['', '', '', '', '', 'នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'])
  const titleRowIdx = pushRow(['បានឃើញ និងឯកភាព', '', '', '', '', 'គ្រូបន្ទប់ថ្នាក់'])
  const principalRowIdx = pushRow(['នាយកសាលា', '', '', '', '', ''])
  pushRow([])
  pushRow([])
  const nameRowIdx = pushRow(['', '', '', '', '', homeroomTeacherName.value])

  mergeQueue.push({ r: dateRow1Idx, c1: 4, c2: 6 })
  mergeQueue.push({ r: dateRow2Idx, c1: 4, c2: 6 })
  mergeQueue.push({ r: titleRowIdx, c1: 0, c2: 2 })
  mergeQueue.push({ r: titleRowIdx, c1: 4, c2: 6 })
  mergeQueue.push({ r: principalRowIdx, c1: 0, c2: 2 })
  mergeQueue.push({ r: nameRowIdx, c1: 4, c2: 6 })

  styleQueue.push({ r: dateRow1Idx, c: 4, style: dateLineStyle })
  styleQueue.push({ r: dateRow2Idx, c: 4, style: dateLineStyle })
  styleQueue.push({ r: titleRowIdx, c: 0, style: moulCenterStyle })
  styleQueue.push({ r: titleRowIdx, c: 4, style: moulRightStyle })
  styleQueue.push({ r: principalRowIdx, c: 0, style: moulCenterStyle })
  styleQueue.push({ r: nameRowIdx, c: 4, style: teacherNameStyle })

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
      styleCell(ws, r, c, tableCellStyle)
    }
  }

  styleQueue.forEach(({ r, c, style }) => styleCell(ws, r, c, style))

  ws['!cols'] = [
    { wch: 16 }, { wch: 18 }, { wch: 18 }, { wch: 18 }, { wch: 18 }, { wch: 18 }, { wch: 18 }
  ]

  // Set height for data rows so text wraps nicely
  const rowsConfig = []
  for (let r = 0; r <= tableEndRowIdx + 10; r++) {
    if (r >= tableStartRowIdx && r <= tableEndRowIdx) {
      rowsConfig[r] = { hpt: 38 } // ៣៨ ພិចសែល សម្រាប់ ២ បន្ទាត់
    } else {
      rowsConfig[r] = { hpt: 24 }
    }
  }
  ws['!rows'] = rowsConfig

  ws['!pageSetup'] = { orientation: 'landscape', fitToWidth: 1, fitToHeight: 0 }
  ws['!printOptions'] = { horizontalCentered: true }
  ws['!margins'] = { left: 0.5, right: 0.5, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'កាលវិភាគសិក្សា')
  XLSX.writeFile(wb, `កាលវិភាគ_ថ្នាក់ទី_${selectedClassData.value.grade_level}${selectedClassData.value.name}.xlsx`)
}

// ─── Export PDF (តាមទម្រង់ចាស់ដំបូង៖ គម្លាតតូច ធានាកុំឱ្យលោតចូល Page 2) ───
const importPdf = async () => {
  if (!selectedClassData.value) {
    showToast('សូមជ្រើសរើសថ្នាក់ជាមុនសិន!', 'error');
    return;
  }
  if (!printArea.value) return;

  // កំណត់ Margin តូច [8, 8, 8, 8] ដើម្បីសល់លំហរច្រើនក្នុង ១ ទំព័រ
  const opt = {
    margin: [8, 8, 8, 8],
    filename: `កាលវិភាគ_ថ្នាក់ទី_${selectedClassData.value.grade_level}${selectedClassData.value.name}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff', scrollY: 0 },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
  };

  if (document.fonts && document.fonts.ready) {
    await document.fonts.ready;
  }

  printArea.value.style.display = 'block';
  try {
    isScheduleLoading.value = true;
    await html2pdf().set(opt).from(printArea.value).save();
    showToast('ទាញយក PDF បានជោគជ័យ!', 'success');
  } catch (err) {
    console.error("កំហុសក្នុងការបង្កើត PDF:", err);
    showToast('មានបញ្ហាក្នុងការបង្កើត PDF', 'error');
  } finally {
    printArea.value.style.display = 'none';
    isScheduleLoading.value = false;
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

@media print {
  .no-print {
    display: none !important;
  }
  .bg-slate-50\/50 {
    background: white !important;
  }
}

.custom-scrollbar::-webkit-scrollbar {
  height: 5px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

table {
  table-layout: fixed;
}

td {
  height: 80px;
  vertical-align: top;
}

.group:hover {
  background-color: #f8fafc;
}

#scheduleTable * {
  color-interpolation-filters: sRGB;
}

#scheduleTable {
  background-color: white !important;
}

/* --- Hidden PDF Export Template Styles (A4 Landscape 275mm width) --- */
.pdf-export-area {
  display: none;
  width: 275mm;
  margin: 0 auto;
  padding: 8px 12px 14px;
  background: #ffffff;
  color: #000000;
  font-family: 'Battambang', sans-serif;
  box-sizing: border-box;
}

.doc-header {
  text-align: center;
  margin-bottom: 6px;
}

.kingdom-title {
  font-family: 'Moul', serif;
  font-size: 13.5px;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.kingdom-motto {
  font-family: 'Moul', serif;
  font-weight: 500;
  font-size: 13.5px;
  margin: 2px 0 6px;
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
  font-size: 10px;
  line-height: 1;
}

.doc-office {
  font-family: 'Moul', serif, Arial, Helvetica, sans-serif;
  font-weight: 500;
  font-size: 12.5px;
  margin: 8px 0 10px;
}

.doc-office p {
  font-family: 'Moul', serif;
  font-size: 12.5px;
  line-height: 1.6;
  margin: 2px 0;
}

.doc-title {
  font-family: 'Moul', Arial, sans-serif;
  font-weight: 500;
  font-size: 14.5px;
  text-align: center;
  margin: 6px 0 12px;
}

/* បង្រួម Padding តារាង ដើម្បីកុំឱ្យកម្ពស់សរុបលោតចូល Page ទី ២ */
.pdf-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
  text-align: center;
}

.pdf-table th,
.pdf-table td {
  border: 1px solid #000000;
  padding: 4px 2px;
  vertical-align: middle;
}

.pdf-table th {
  font-weight: 700;
  text-align: center;
  background: #ffffff;
  font-size: 11.5px;
  height: 28px;
}

.pdf-table td {
  height: 14.5mm; /* កំណត់កម្ពស់ជួរដេកនីមួយៗឱ្យល្មមស្អាត */
}

/* រៀបចំកន្លែងហត្ថលេខា ឆ្វេង (នាយកសាលា) និង ស្ដាំ (គ្រូបន្ទប់ថ្នាក់) */
.doc-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-top: 15px;
  font-size: 12px;
  line-height: 1.7;
}

.signature-left,
.signature-right {
  width: 40%;
  text-align: center;
}

.moul {
  font-family: 'Moul', serif;
  font-size: 12px;
  margin-top: 3px;
}

.teacher-name {
  margin-top: 35px;
  font-weight: bold;
}

/* --- Keep Old UI Transitions --- */
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
  transform: translateX(-50%) translateY(-120%);
  opacity: 0;
}

.toast-slide-enter-to,
.toast-slide-leave-from {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}
</style>