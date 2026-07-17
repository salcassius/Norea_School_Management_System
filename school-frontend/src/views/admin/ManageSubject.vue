<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងមុខវិជ្ជា</h1>
        <p class="text-sm text-slate-500 mt-1">កំណត់ និងគ្រប់គ្រងឈ្មោះមុខវិជ្ជាសិក្សាទាំងអស់</p>
      </div>

      <button @click="openCreateModal"
        class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
        <Plus class="w-5 h-5" />
        បន្ថែមមុខវិជ្ជាថ្មី
      </button>
    </div>

    <!-- Stats Card -->
    <div class="mb-8">
      <div
        class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 max-w-xs transition-all hover:border-indigo-300">
        <div class="p-3 rounded-xl bg-indigo-50">
          <BookOpen class="w-6 h-6 text-indigo-600" />
        </div>
        <div>
          <p class="text-[14px] text-slate-500 font-medium uppercase tracking-wider">មុខវិជ្ជាសរុប</p>
          <p class="text-lg font-bold text-slate-800">{{ subjects.length }} មុខ</p>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

      <!-- Search Input -->
      <div class="p-4 border-b border-slate-100 flex justify-between bg-slate-50/30">
        <div class="relative max-w-sm w-full">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះមុខវិជ្ជា..."
            class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
        </div>
      </div>

      <div class="overflow-x-auto">
        <div v-if="isLoading" class="p-10 text-center text-slate-500 text-sm italic">កំពុងទាញទិន្នន័យ...</div>
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider w-20">#</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ឈ្មោះមុខវិជ្ជា</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ពិន្ទុអតិបរមា</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(subject, index) in filteredSubjects" :key="subject.id"
              class="hover:bg-slate-50/80 transition-colors">
              <td class="px-6 py-4 text-sm font-bold text-slate-500">{{ index + 1 }}</td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100">
                    <BookOpen class="w-5 h-5 text-indigo-600" />
                  </div>
                  <span class="text-sm font-bold text-slate-700 uppercase tracking-tight">{{ subject.name }}</span>
                </div>
              </td>

              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[13px] font-bold border border-amber-100">
                  {{ subject.max_score }} ពិន្ទុ
                </span>
              </td>

              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="handleEdit(subject)"
                    class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all shadow-sm active:scale-90">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="openDeleteModal(subject)"
                    class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all shadow-sm active:scale-90">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredSubjects.length === 0">
              <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-sm">មិនមានទិន្នន័យឡើយ</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modals -->
    <SubjectCreateModal v-if="showCreateModal" v-model="showCreateModal" :loading="isLoading"
      @refresh="handleCreateSubject" @error="handleCreateError" />

    <EditeSubject v-if="showEditModal" v-model="showEditModal" :subject-data="selectedSubject" :loading="isLoading"
      @refresh="handleUpdateSubject" />

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

              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបមុខវិជ្ជា</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingSubject?.name }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteSubject" :disabled="isDeleting"
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
import { Plus, Search, BookOpen, Edit3, Trash2, CheckCircle2, XCircle, X, Loader2 } from 'lucide-vue-next'
import api from '../../services/authService'
import SubjectCreateModal from '../admin/create/CreateSubjectModel.vue'
import EditeSubject from '../admin/edite/EditeSubject.vue'

// --- State ---
const subjects = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedSubject = ref(null)
const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const deletingSubject = ref(null)

const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

// --- Toast ---
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// --- Fetch ---
const fetchSubjects = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/subjects')
    subjects.value = res.data || []
  } catch (error) {
    console.error('Fetch error:', error)
    showToast('មិនអាចទាញទិន្នន័យបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

// --- Create ---
const openCreateModal = () => { showCreateModal.value = true }

// --- Edit ---
const handleEdit = (subject) => {
  selectedSubject.value = subject
  showEditModal.value = true
}

// --- Delete Modal ---
const openDeleteModal = (subject) => {
  deletingSubject.value = subject
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  isDeleteModalOpen.value = false
  deletingSubject.value = null
}

// --- Create Subject Handler ---
// NOTE: previously this called an undefined closeModal() function, which
// would throw a ReferenceError every time a subject was created, and it
// fetched the list twice. Fixed to close the modal correctly and fetch once.
const handleCreateSubject = async () => {
  try {
    isLoading.value = true
    showCreateModal.value = false
    await fetchSubjects()
    showToast('បង្កើតមុខវិជ្ជាបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចបង្កើតមុខវិជ្ជាបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

// --- Create Subject Error Handler (bubbled up from the modal, if it emits one) ---
const handleCreateError = (message) => {
  showToast(message || 'មិនអាចបង្កើតមុខវិជ្ជាបានទេ!', 'error')
}

// --- Edit Subject Handler ---
// NOTE: previously fetched the list twice in a row. Fixed to fetch once.
const handleUpdateSubject = async () => {
  try {
    isLoading.value = true
    showEditModal.value = false
    await fetchSubjects()
    showToast('កែប្រែមុខវិជ្ជាបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចកែប្រែមុខវិជ្ជាបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

const confirmDeleteSubject = async () => {
  if (!deletingSubject.value) return
  try {
    isDeleting.value = true
    await api.delete(`/subjects/${deletingSubject.value.id}`)
    isDeleteModalOpen.value = false
    deletingSubject.value = null
    await fetchSubjects()
    showToast('បានលុបមុខវិជ្ជាដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast('មិនអាចលុបបានទេ: ' + (error.response?.data?.message || 'Error'), 'error')
  } finally {
    isDeleting.value = false
  }
}

// --- Computed ---
const filteredSubjects = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  return subjects.value.filter(s => s.name?.toLowerCase().includes(query))
})

onMounted(fetchSubjects)
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