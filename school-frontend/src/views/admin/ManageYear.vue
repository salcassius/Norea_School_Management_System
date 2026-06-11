<template>
  <div class="font-[Battambang] p-6 space-y-6 bg-slate-50/50 min-h-screen text-slate-700">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 font-[Khmer_OS_Muol_Light]">គ្រប់គ្រងឆ្នាំសិក្សា</h1>
        <p class="text-sm text-slate-500 mt-0.5">កំណត់ និងគ្រប់គ្រងឆ្នាំសិក្សាសម្រាប់ប្រព័ន្ធទាំងមូល</p>
      </div>
      <button 
        @click="openCreateModal"
        class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-sm transition-all text-sm font-semibold"
      >
        <Plus class="w-4 h-4" /> បង្កើតឆ្នាំសិក្សាថ្មី
      </button>
    </div>

    <div v-if="activeYear" class="bg-indigo-50/60 border border-indigo-100 p-4 rounded-xl flex items-center gap-4 animate-in fade-in duration-300">
      <div class="bg-indigo-600 p-2.5 rounded-xl text-white shadow-sm">
        <CalendarRange class="w-5 h-5" />
      </div>
      <div>
        <p class="text-indigo-900 font-bold text-base">ឆ្នាំសិក្សាបច្ចុប្បន្ន៖ {{ activeYear.year_name }}</p>
        <p class="text-indigo-600/80 text-xs mt-0.5">រាល់ទិន្នន័យវត្តមាន ថ្នាក់រៀន និងពិន្ទុនឹងត្រូវបញ្ចូលទៅក្នុងឆ្នាំសិក្សានេះ</p>
      </div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-3">
      <div class="animate-spin rounded-full h-10 w-10 border-4 border-indigo-600 border-t-transparent"></div>
      <p class="text-slate-400 text-sm font-medium">កំពុងទាញទិន្នន័យ...</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="year in academicYears" 
        :key="year.id"
        :class="[
          'relative p-5 rounded-2xl border transition-all duration-300 bg-white group shadow-sm',
          year.is_active ? 'border-indigo-500 ring-4 ring-indigo-500/5' : 'border-slate-200 hover:border-slate-300'
        ]"
      >
        <div class="absolute top-5 right-5">
          <span 
            :class="[
              'px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wide',
              year.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200'
            ]"
          >
            {{ year.is_active ? 'កំពុងដំណើរការ' : 'ចាស់' }}
          </span>
        </div>

        <div class="space-y-4">
          <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl w-fit group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
            <CalendarDays class="w-6 h-6" />
          </div>
          
          <div>
            <h3 class="text-lg font-bold text-slate-800">ឆ្នាំសិក្សា៖ {{ year.year_name }}</h3>
            <div class="text-xs text-slate-500 mt-1.5 space-y-0.5 font-medium">
              <p><span class="text-slate-400">ចាប់ផ្តើម៖</span> {{ formatDate(year.start_date) }}</p>
              <p><span class="text-slate-400">បញ្ចប់៖</span> {{ formatDate(year.end_date) }}</p>
            </div>
          </div>

          <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between gap-3">
            <button 
              v-if="!year.is_active"
              @click="handleSetActive(year.id)"
              class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-tight"
            >
              កំណត់ជាឆ្នាំបច្ចុប្បន្ន
            </button>
            <span v-else class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">ឆ្នាំបច្ចុប្បន្ន</span>

            <div class="flex items-center gap-0.5">
              <button @click="openEditModal(year)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition-all" title="កែសម្រួល">
                <Edit3 class="w-4 h-4" />
              </button>
              <button @click="deleteYear(year.id)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-50 rounded-lg transition-all" title="លុប">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <YearModal 
      :isOpen="isCreateOpen" 
      @close="closeCreateModal" 
      @success="fetchYears" 
    />

    <EditeYear 
      :isOpen="isEditOpen" 
      :editData="selectedYearData"
      @close="closeEditModal" 
      @success="fetchYears" 
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Plus, CalendarRange, CalendarDays, Edit3, Trash2 } from 'lucide-vue-next'
import api from '../../services/authService'
import YearModal from '../admin/create/CreateYearModel.vue' 
import EditeYear from './edite/EditeYear.vue'

const academicYears = ref([])
const isLoading = ref(false)

// បំបែក State បើក/បិទ Modal ជាពីរដើម្បីកុំឱ្យជាន់គ្នា
const isCreateOpen = ref(false)
const isEditOpen = ref(false)
const selectedYearData = ref(null)

const activeYear = computed(() => academicYears.value.find(y => y.is_active))

// មុខងារទាញយកទិន្នន័យឆ្នាំសិក្សា
const fetchYears = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/years')
    academicYears.value = response.data
  } catch (error) {
    console.error("Error fetching years:", error)
  } finally {
    isLoading.value = false
  }
}

// មុខងារកំណត់ឆ្នាំសិក្សាសកម្ម
const handleSetActive = async (id) => {
  try {
    await api.post(`/years/${id}/set-active`)
    await fetchYears()
  } catch (error) {
    alert("ការកំណត់បរាជ័យ!")
  }
}

// មុខងារលុបឆ្នាំសិក្សា
const deleteYear = async (id) => {
  if (!confirm("តើអ្នកពិតជាចង់លុបឆ្នាំសិក្សានេះមែនទេ?")) return
  try {
    await api.delete(`/years/${id}`)
    academicYears.value = academicYears.value.filter(y => y.id !== id)
  } catch (error) {
    alert("មិនអាចលុបបានទេ! ឆ្នាំសិក្សានេះអាចកំពុងមានទិន្នន័យថ្នាក់រៀនផ្សារភ្ជាប់។")
  }
}

// គ្រប់គ្រង Create Modal
const openCreateModal = () => {
  isCreateOpen.value = true
}
const closeCreateModal = () => {
  isCreateOpen.value = false
}

// គ្រប់គ្រង Edit Modal
const openEditModal = (year) => {
  selectedYearData.value = { ...year } // ចម្លងទិន្នន័យបណ្តោះអាសន្ន
  isEditOpen.value = true
}
const closeEditModal = () => {
  isEditOpen.value = false
  selectedYearData.value = null
}

// មុខងារជំនួយសម្រាប់កាត់ទម្រង់កាលបរិច្ឆេទ
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return dateStr.split('T')[0]
}

onMounted(fetchYears)
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
</style>