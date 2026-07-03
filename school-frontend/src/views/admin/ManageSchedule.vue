<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">

    <!-- <div class="max-w-7xl mx-auto mb-6 no-print">
      <div
        class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
            <School class="w-5 h-5" />
          </div>
          <div>
            <label
              class="block text-xs font-bold text-slate-400 uppercase tracking-wider">ឆ្នាំសិក្សាដែលត្រូវរៀបចំ</label>
            <select v-model="selectedYearId" @change="handleYearChange"
              class="mt-0.5 bg-transparent font-bold text-slate-800 outline-none cursor-pointer text-sm focus:text-indigo-600">
              <option v-for="year in academicYears" :key="year.id" :value="year.id">
                ឆ្នាំសិក្សា៖ {{ year.year_name }} {{ (year.is_active == 1 || year.is_active == true) ? '(បច្ចុប្បន្ន)' :
                  '' }}
              </option>
            </select>
          </div>
        </div>
        <div class="text-xs text-slate-400 font-medium sm:text-right">
          * រាល់ទិន្នន័យកាលវិភាគ និងថ្នាក់រៀននឹងបង្ហាញទៅតាមឆ្នាំសិក្សាដែលបានជ្រើសរើស
        </div>
      </div>
    </div> -->

    <div v-if="!isClassSelected" class="max-w-7xl mx-auto">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800 font-[battambang]">គ្រប់គ្រងកាលវិភាគសិក្សា</h1>
        <p class="text-sm text-slate-500 mt-1">សូមជ្រើសរើសថ្នាក់រៀន ដើម្បីមើល ឬរៀបចំកាលវិភាគសិក្សា</p>
      </div>

      <div v-if="filteredClasses.length === 0"
        class="p-12 text-center bg-white rounded-2xl border border-slate-200/80 shadow-sm">
        <p class="text-slate-400 font-medium">មិនមានទិន្នន័យថ្នាក់រៀនសម្រាប់ឆ្នាំសិក្សាដែលបានជ្រើសរើសឡើយ</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="cls in filteredClasses" :key="cls.id" @click="handleSelectClass(cls)"
          class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-500 hover:shadow-md transition-all cursor-pointer group relative overflow-hidden">
          <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:opacity-5 transition-all duration-500">
            <School class="w-24 h-24" />
          </div>
          <div
            class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600 mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
            <LayoutDashboard class="w-5 h-5" />
          </div>
          <h3 class="text-lg font-bold text-slate-800">ថ្នាក់ទី​​៖​ {{ cls.grade_level }} {{ cls.name }}</h3>
          <div class="mt-5 flex items-center justify-between">
            <span
              :class="['px-2.5 py-0.5 rounded-full text-[13px] font-bold uppercase border', (cls.is_active == 1 || cls.is_active == true) ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100']">
              {{ (cls.is_active == 1 || cls.is_active == true) ? 'បើកដំណើរការ' : 'បឹទបណ្តោះអាសន្ន' }}
            </span>
            <div
              class="w-7 h-7 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all">
              <ChevronRight class="w-4 h-4" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="space-y-6 animate-in fade-in duration-300 max-w-7xl mx-auto">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <button @click="isClassSelected = false"
            class="p-2.5 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm group no-print">
            <ArrowLeft class="w-4 h-4 text-slate-600 group-hover:text-indigo-600" />
          </button>
          <div>
            <h1 class="text-xl font-bold text-slate-800 font-[Khmer_OS_Muol_Light]">
              កាលវិភាគថ្នាក់ទី៖ {{ selectedClassData?.grade_level }} {{ selectedClassData?.name }}
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">រៀបចំ និងតាមដានម៉ោងសិក្សាប្រចាំសប្តាហ៍</p>
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 no-print">
          <button @click="importPdf"
            class="flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-xl text-sm font-semibold transition-all shadow-sm active:scale-95">
            <FileText class="w-4 h-4 text-rose-500" /> ទាញយកជា PDF
          </button>
        </div>
      </div>

      <!-- <div class="bg-white p-4 rounded-2xl border border-slate-200/60 shadow-sm flex items-center gap-4 no-print">
        <div class="flex items-center gap-2">
          <span class="text-sm font-semibold text-slate-600">វេនសិក្សា:</span>
          <select v-model="selectedShift" @change="fetchSchedules"
            class="bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-3 text-sm font-semibold outline-none focus:border-indigo-500 cursor-pointer">
            <option value="morning">ពេលព្រឹក</option>
            <option value="afternoon">ពេលរសៀល</option>
          </select>
        </div>
      </div> -->

      <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div v-if="isLoading" class="p-12 text-center text-slate-400 font-medium">
          កំពុងទាញយកទិន្នន័យកាលវិភាគ...
        </div>
        <div v-else id="scheduleTable" class="overflow-x-auto custom-scrollbar">
          <table class="w-full min-w-[1200px] border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100">
                <th
                  class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center w-36 border-r border-slate-100">
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
                        <button @click.stop="deleteEntry(getScheduleEntry(day, slot.time).id)"
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

    <CreateSchedule v-if="showCreateModal" :initData="modalFormPayload" :subjects="subjects" :teachers="teachers"
      @close="showCreateModal = false" @saved="handleScheduleSaved" />
    <EditSchedule v-if="showEditModal" :entryData="modalFormPayload" :subjects="subjects" :teachers="teachers"
      @close="showEditModal = false" @saved="handleScheduleSaved" />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { FileText, MapPin, ChevronRight, ArrowLeft, School, LayoutDashboard, Edit3, Trash2, PlusCircle } from 'lucide-vue-next'

import api from '../../services/authService'

import CreateSchedule from '../admin/create/CreateScheduleModel.vue'
import EditSchedule from '../admin/edite/EditeSchedule.vue'
import '../../utils/Battambang-Regular-normal.js';
import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable'



// --- States ---
const isClassSelected = ref(false)
const selectedClassData = ref(null)

const selectedShift = ref('morning')
const isLoading = ref(false)

const showCreateModal = ref(false)
const showEditModal = ref(false)
const modalFormPayload = ref(null)

const selectedYearId = ref(null)
const academicYears = ref([])
const classes = ref([])
const subjects = ref([])
const teachers = ref([])
const scheduleData = ref([])

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

const fetchInitialData = async () => {
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
  }
}

const fetchSchedules = async () => {
  if (!selectedYearId.value || !selectedClassData.value) return
  isLoading.value = true
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
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchInitialData()
})

const filteredClasses = computed(() => {
  return classes.value.filter(cls => Number(cls.academic_year_id || cls.year_id) === Number(selectedYearId.value))
})

const handleYearChange = () => {
  isClassSelected.value = false
  scheduleData.value = []
}

const handleSelectClass = (cls) => {
  selectedClassData.value = cls
  isClassSelected.value = true
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

const handleScheduleSaved = () => {
  showCreateModal.value = false
  showEditModal.value = false
  fetchSchedules()
}

const deleteEntry = async (id) => {
  if (confirm('តើអ្នកពិតជាចង់លុបម៉ោងសិក្សានេះមែនទេ?')) {
    try {
      const response = await api.delete(`/schedules/${id}`)
      scheduleData.value = scheduleData.value.filter(item => item.id !== id)
      alert(response.data.message || 'បានលុបដោយជោគជ័យ!')
    } catch (error) {
      alert('មិនអាចលុបម៉ោងសិក្សានេះបានឡើយ!')
    }
  }
}

const importPdf = async () => {
  const element = document.getElementById('scheduleTable'); 

  if (!element) {
    alert("រកមិនឃើញកាលវិភាគសម្រាប់ទាញយកទេ!");
    return;
  }

  try {
    // បង្ហាញសញ្ញាថាដំណើរការកំពុងចាប់ផ្តើម
    isLoading.value = true; 

    const canvas = await html2canvas(element, {
      scale: 3, // បង្កើន scale ដល់ 3 ដើម្បីឱ្យអក្សរច្បាស់ខ្លាំង
      useCORS: true,
      backgroundColor: '#ffffff',
      windowWidth: element.scrollWidth, // ថតយកទទឹងពេញរបស់តារាង
      onclone: (clonedDoc) => {
        const clonedElement = clonedDoc.getElementById('scheduleTable');
        if (clonedElement) {
          clonedElement.style.width = '100%';
          clonedElement.style.overflow = 'visible'; // បង្ហាញ content ទាំងអស់
        }
        // ដោះស្រាយបញ្ហាពណ៌ oklch
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

    // ប្រសិនបើកម្ពស់រូបភាពធំជាងក្រដាស វានឹងដាក់រូបភាពឱ្យសមនឹងទទឹង
    pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth, pdfHeight);
    
    pdf.save(`កាលវិភាគ_${selectedClassData.value?.name || 'ថ្នាក់'}.pdf`);
    
  } catch (err) {
    console.error("កំហុសក្នុងការបង្កើត PDF:", err);
    alert("មានបញ្ហាក្នុងការបង្កើត PDF");
  } finally {
    isLoading.value = false;
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
  /* ធ្វើឱ្យ column នីមួយៗស្មើគ្នា */
}

/* ធ្វើឱ្យកោសិកានីមួយៗមានកម្ពស់ថេរ */
td {
  height: 80px;
  vertical-align: top;
}

/* បន្ថែមពណ៌សិស្សនៅពេល Hover */
.group:hover {
  background-color: #f8fafc;
}

#scheduleTable * {
  color-interpolation-filters: sRGB;
}

/* ប្រសិនបើអ្នកប្រើ Tailwind ជាមួយ oklch អ្នកអាចសរសេរ override ពណ៌នៅទីនេះ */
#scheduleTable {
  background-color: white !important;
}


</style>