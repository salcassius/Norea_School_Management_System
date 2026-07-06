<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">
    <div class="max-w-7xl mx-auto space-y-6">

      <!-- Header + class picker: always visible, never swapped out -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 font-[battambang]">គ្រប់គ្រងកាលវិភាគសិក្សា</h1>
          <p class="text-sm text-slate-500 mt-1">សូមជ្រើសរើសថ្នាក់រៀន ដើម្បីមើល ឬរៀបចំកាលវិភាគសិក្សា</p>
        </div>
      </div>

      <div v-if="studentClasses.length === 0"
        class="p-8 text-center bg-white rounded-2xl border border-slate-200/80 shadow-sm max-w-64">
        <p class="text-slate-400 font-medium text-sm">មិនមានទិន្នន័យថ្នាក់រៀនសម្រាប់ឆ្នាំសិក្សាដែលបានជ្រើសរើសឡើយ</p>
      </div>

      <div v-else class="w-full md:w-64">
        <label class="text-[1៥px] font-bold text-slate-600 ml-1">សូមជ្រើសរើសថ្នាក់</label>
        <select v-model="filterClass"
          class="w-full mt-1 bg-slate-50 border border-slate-300 rounded-lg py-2 px-3 text-[14px] outline-none focus:border-indigo-500">
          <option value="" disabled>ជ្រើសរើសថ្នាក់</option>
          <option v-for="cls in studentClasses" :key="cls.id" :value="cls.id">{{ cls.grade_level }}{{
            cls.name }}</option>
        </select>
      </div>

      <!-- Schedule section: appears inline below once a class is picked, no view-swap -->
      <div v-if="selectedClassData" class="space-y-4 animate-in fade-in duration-300">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">

          <div>
            <h2 class="text-lg font-bold text-slate-800 font-[Khmer_OS_Muol_Light]">
              កាលវិភាគថ្នាក់ទី៖ {{ selectedClassData?.grade_level }}{{ selectedClassData?.name }}
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">រៀបចំ និងតាមដានម៉ោងសិក្សាប្រចាំសប្តាហ៍</p>
          </div>

          <div class="flex items-center gap-3">
            <button v-if="selectedClassData" @click="importPdf"
              class="flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-xl text-sm font-semibold transition-all shadow-sm active:scale-95">
              <FileText class="w-4 h-4 text-rose-500" /> ទាញយកជា PDF
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
                    class="p-4 text-xs font-bold text-slate-600 uppercase tracking-wider text-center w-36 border-r border-slate-100">
                    ម៉ោងសិក្សា</th>
                  <th v-for="day in days" :key="day" class="p-4 text-sm font-bold text-slate-700 text-center">{{ day }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(slot, idx) in timeSlots" :key="idx" class="group">
                  <td class="p-4 border-r border-slate-100 text-center bg-slate-50/50">
                    <span
                      class="text-xs font-bold text-indigo-600 bg-indigo-50/60 border border-indigo-100 px-2.5 py-1 rounded-lg">{{
                        slot.time }}</span>
                  </td>
                  <td v-for="day in days" :key="day" class="p-2.5 relative min-h-[110px] align-top">

                    <div v-if="getScheduleEntry(day, slot.time)"
                      :class="['p-3 h-[85px] rounded-xl border shadow-sm w-full group/card transition-all w-fit min-w-[160px]', getSubjectTheme(getScheduleEntry(day, slot.time).subject?.name)]">

                      <div class="flex justify-between items-start gap-1">
                        <p class="text-[14px] font-bold truncate">
                          {{ getScheduleEntry(day, slot.time).subject?.name || '---' }}
                        </p>

                        <div class="flex gap-0.5 opacity-0 group-hover/card:opacity-100 transition-opacity no-print">
                          <button @click.stop="openEditModal(getScheduleEntry(day, slot.time))"
                            class="text-indigo-600 hover:bg-indigo-100 p-0.5 rounded">
                            <Edit3 class="w-3 h-3" />
                          </button>
                          <button @click.stop="openDeleteModal(getScheduleEntry(day, slot.time))"
                            class="text-rose-600 hover:bg-rose-100 p-0.5 rounded">
                            <Trash2 class="w-3 h-3" />
                          </button>
                        </div>
                      </div>

                      <p class="text-[14px] text-slate-500 font-medium truncate mt-0.5">
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

    <CreateSchedule v-if="showCreateModal" :initData="modalFormPayload" :subjects="subjects" :teachers="teachers"
      @close="showCreateModal = false" @saved="handleScheduleSaved" />
    <EditSchedule v-if="showEditModal" :entryData="modalFormPayload" :subjects="subjects" :teachers="teachers"
      @close="showEditModal = false" @saved="handleScheduleSaved" />

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

              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបម៉ោងសិក្សា</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingEntry?.subject?.name || 'ម៉ោងសិក្សានេះ' }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteEntry" :disabled="isDeleting"
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
import { ref, computed, watch, onMounted } from 'vue'
import { FileText, MapPin, ChevronRight, ArrowLeft, School, LayoutDashboard, Edit3, Trash2, PlusCircle, Loader2, CheckCircle2, XCircle, X } from 'lucide-vue-next'

import api from '../../services/authService'

import CreateSchedule from '../admin/create/CreateScheduleModel.vue'
import EditSchedule from '../admin/edite/EditeSchedule.vue'
import '../../utils/Battambang-Regular-normal.js';
import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable'

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

// Two separate loading flags so fetching one thing doesn't blank out the other
const isClassesLoading = ref(false)
const isScheduleLoading = ref(false)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const modalFormPayload = ref(null)

const selectedYearId = ref(null)
const filterClass = ref('') // holds the id of the class picked in the <select>
const academicYears = ref([])
const classes = ref([])
const subjects = ref([])
const teachers = ref([])
const scheduleData = ref([])

// Delete modal state
const isDeleteModalOpen = ref(false)
const deletingEntry = ref(null)
const isDeleting = ref(false)

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

// ─── Create / Edit (actual save happens inside the child modals; this just
//     reacts once they emit "saved") ───────────────────────────────────────────

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

// Options shown in the class <select>
const studentClasses = computed(() => filteredClasses.value)

// As soon as a class is picked from the dropdown, load its schedule inline — no view swap
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

// ─── Export ───────────────────────────────────────────────────────────────────

const importPdf = async () => {
  const element = document.getElementById('scheduleTable');

  if (!element) {
    showToast('រកមិនឃើញកាលវិភាគសម្រាប់ទាញយកទេ!', 'error');
    return;
  }

  try {
    isScheduleLoading.value = true;

    const canvas = await html2canvas(element, {
      scale: 3,
      useCORS: true,
      backgroundColor: '#ffffff',
      windowWidth: element.scrollWidth,
      onclone: (clonedDoc) => {
        const clonedElement = clonedDoc.getElementById('scheduleTable');
        if (clonedElement) {
          clonedElement.style.width = '100%';
          clonedElement.style.overflow = 'visible';
        }
        const items = clonedDoc.querySelectorAll('*');
        items.forEach(item => {
          const style = window.getComputedStyle(item);
          if (style.color && style.color.includes('oklch')) {
            item.style.color = '#334155';
          }
        });
      }
    });

    const imgData = canvas.toDataURL('image/png', 1.0);
    const pdf = new jsPDF('l', 'mm', 'a4');

    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pdf.internal.pageSize.getWidth() - 20;
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth, pdfHeight);

    pdf.save(`កាលវិភាគ_${selectedClassData.value?.name || 'ថ្នាក់'}.pdf`);
    showToast('ទាញយក PDF បានជោគជ័យ!', 'success');

  } catch (err) {
    console.error("កំហុសក្នុងការបង្កើត PDF:", err);
    showToast('មានបញ្ហាក្នុងការបង្កើត PDF', 'error');
  } finally {
    isScheduleLoading.value = false;
  }
};

</script>
<style scoped>
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