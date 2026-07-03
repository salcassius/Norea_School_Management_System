<template>
  <div class="font-[Battambang] p-6 space-y-6 bg-slate-50/50 min-h-screen text-slate-700">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 font-[battambang]">គ្រប់គ្រងឆ្នាំសិក្សា</h1>
        <p class="text-sm text-slate-500 mt-0.5">កំណត់ និងគ្រប់គ្រងឆ្នាំសិក្សាសម្រាប់ប្រព័ន្ធទាំងមូល</p>
      </div>
      <button
        @click="openCreateModal"
        class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95"
      >
        <Plus class="w-4 h-4" /> បង្កើតឆ្នាំសិក្សាថ្មី
      </button>
    </div>

    <!-- Active Year Banner -->
    <div v-if="activeYear" class="bg-indigo-50/60 border border-indigo-100 p-4 rounded-xl flex items-center gap-4">
      <div class="bg-indigo-600 p-2.5 rounded-xl text-white shadow-sm">
        <CalendarRange class="w-5 h-5" />
      </div>
      <div>
        <p class="text-indigo-900 font-bold text-base">ឆ្នាំសិក្សាបច្ចុប្បន្ន៖ {{ activeYear.year_name }}</p>
        <p class="text-indigo-600/80 text-xs mt-0.5">រាល់ទិន្នន័យវត្តមាន ថ្នាក់រៀន និងពិន្ទុនឹងត្រូវបញ្ចូលទៅក្នុងឆ្នាំសិក្សានេះ</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-3">
      <div class="animate-spin rounded-full h-10 w-10 border-4 border-indigo-600 border-t-transparent"></div>
      <p class="text-slate-400 text-sm font-medium">កំពុងទាញទិន្នន័យ...</p>
    </div>

    <!-- Year Cards -->
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
          <span :class="[
            'px-2.5 py-0.5 rounded-full text-[13px] font-bold border uppercase tracking-wide',
            year.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-600 border-red-300'
          ]">
            {{ year.is_active ? 'កំពុងដំណើរការ' : 'ឆ្នាំចាស់' }}
          </span>
        </div>

        <div class="space-y-4">
          <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl w-fit group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
            <CalendarDays class="w-6 h-6" />
          </div>

          <div>
            <h3 class="text-lg font-bold text-slate-800">ឆ្នាំសិក្សា៖ {{ year.year_name }}</h3>
            <div class="text-[13px] text-slate-500 mt-1.5 space-y-0.5 font-medium">
              <p><span class="text-slate-600">ចាប់ផ្តើម៖</span> {{ formatDate(year.start_date) }}</p>
              <p><span class="text-slate-600">បញ្ចប់៖</span> {{ formatDate(year.end_date) }}</p>
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
            <span v-else class="text-[14px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
              ឆ្នាំបច្ចុប្បន្ន
            </span>

            <div class="flex items-center gap-2">
              <button @click="openEditModal(year)" title="កែសម្រួល"
                class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all shadow-sm active:scale-90">
                <Edit3 class="w-4 h-4" />
              </button>
              <button @click="openDeleteModal(year)" title="លុប"
                class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all shadow-sm active:scale-90">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="academicYears.length === 0" class="col-span-full py-16 text-center text-slate-400 text-sm">
        មិនមានឆ្នាំសិក្សាទេ
      </div>
    </div>

    <!-- Modals -->
    <YearModal :isOpen="isCreateOpen" @close="closeCreateModal" @success="handleCreateYear" />
    <EditeYear :isOpen="isEditOpen" :editData="selectedYearData" @close="closeEditModal" @success="handleUpdateYear" />

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
              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបឆ្នាំសិក្សា</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingYear?.year_name }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <!-- Buttons -->
              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteYear" :disabled="isDeleting"
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
import { ref, computed, onMounted } from 'vue'
import {
  Plus, CalendarRange, CalendarDays, Edit3, Trash2,
  CheckCircle2, XCircle, X, Loader2
} from 'lucide-vue-next'
import api from '../../services/authService'
import YearModal from '../admin/create/CreateYearModel.vue'
import EditeYear from './edite/EditeYear.vue'

// --- State ---
const academicYears = ref([])
const isLoading = ref(false)
const isCreateOpen = ref(false)
const isEditOpen = ref(false)
const selectedYearData = ref(null)
const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const deletingYear = ref(null)


// --- Computed ---
const activeYear = computed(() => academicYears.value.find(y => y.is_active))

// --- Toast ---
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// --- Fetch ---
const fetchYears = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/years')
    academicYears.value = response.data
  } catch (error) {
    console.error('Error fetching years:', error)
    showToast('មិនអាចទាញទិន្នន័យបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

// --- Set Active ---
const handleSetActive = async (id) => {
  try {
    await api.post(`/years/${id}/set-active`)
    await fetchYears()
    showToast('បានកំណត់ឆ្នាំសិក្សាបច្ចុប្បន្នដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast('ការកំណត់បរាជ័យ!', 'error')
  }
}

// --- Create Modal ---
const openCreateModal = () => { isCreateOpen.value = true }
const closeCreateModal = () => { isCreateOpen.value = false }

const handleCreateYear = async () => {
  try {
    closeCreateModal()
    await fetchYears()
    showToast('បានបង្កើតឆ្នាំសិក្សាដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast(
      error.response?.data?.message || 'មិនអាចបង្កើតឆ្នាំសិក្សាបានទេ!',
      'error'
    )
  }
}

const handleUpdateYear = async () => {
  try {
    closeEditModal()
    await fetchYears()
    showToast('បានកែប្រែឆ្នាំសិក្សាដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast(
      error.response?.data?.message || 'មិនអាចកែប្រែឆ្នាំសិក្សាបានទេ!',
      'error'
    )
  }
}

// --- Edit Modal ---
const openEditModal = (year) => {
  selectedYearData.value = { ...year }
  isEditOpen.value = true
}
const closeEditModal = () => {
  isEditOpen.value = false
  selectedYearData.value = null
}

// --- Delete Modal ---
const openDeleteModal = (year) => {
  deletingYear.value = year
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  isDeleteModalOpen.value = false
  deletingYear.value = null
}

const confirmDeleteYear = async () => {
  if (!deletingYear.value) return
  try {
    isDeleting.value = true
    await api.delete(`/years/${deletingYear.value.id}`)
    isDeleteModalOpen.value = false
    deletingYear.value = null
    await fetchYears()
    showToast('បានលុបឆ្នាំសិក្សាដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast('មិនអាចលុបបានទេ: ' + (error.response?.data?.message || 'Error'), 'error')
  } finally {
    isDeleting.value = false
  }
}

// --- Helpers ---
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return dateStr.split('T')[0]
}

onMounted(fetchYears)
</script>

<style scoped>
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