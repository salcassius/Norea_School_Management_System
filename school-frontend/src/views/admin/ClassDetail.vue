<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">
    <div class="flex items-center justify-between gap-4 mb-6">
      <div>
        <button @click="goBack"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
          </svg>
          ត្រឡប់ក្រោយ
        </button>
        <h1 class="text-2xl font-bold text-slate-800 mt-4">ព័ត៌មានលម្អិតថ្នាក់</h1>
        <p class="text-sm text-slate-500 mt-1">ទំនាក់ទំនងថ្នាក់ និងសិស្សដែលបានចុះបញ្ជី</p>
      </div>
    </div>

    <div v-if="loading" class="rounded-2xl bg-white border border-slate-200 shadow-sm p-8 text-center text-slate-500">
      កំពុងបញ្ចូលទិន្នន័យ...
    </div>

    <div v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 uppercase tracking-wide">ឈ្មោះថ្នាក់</p>
          <p class="text-xl font-bold text-slate-800 mt-3">ថ្នាក់ទី {{ classRoom?.grade_level }}{{ classRoom?.name }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 uppercase tracking-wide">បន្ទុកថ្នាក់</p>
          <p class="text-xl font-bold text-slate-800 mt-3">{{ classRoom?.teacher?.name_kh || 'មិនទាន់មាន' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <p class="text-sm text-slate-500 uppercase tracking-wide">ឆ្នាំសិក្សា</p>
          <p class="text-xl font-bold text-slate-800 mt-3">{{ classRoom?.year?.year_name || classRoom?.year?.name || 'N/A' }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-6">
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-bold text-slate-800">សិស្សក្នុងថ្នាក់</h2>
              <p class="text-sm text-slate-500 mt-1">{{ classStudents.length }} នាក់</p>
            </div>
          </div>

          <div v-if="classStudents.length === 0" class="py-10 text-center text-slate-500">
            មិនទាន់មានសិស្សនៅក្នុងថ្នាក់នេះទេ
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-600 text-[13px] uppercase tracking-wider">
                  <th class="px-4 py-3">ល.រ</th>
                  <th class="px-4 py-3">ឈ្មោះសិស្ស</th>
                  <th class="px-4 py-3">អត្តលេខ</th>
                  <th class="px-4 py-3">លេខទូរស័ព្ទ</th>
                  <th class="px-4 py-3 text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-sm">
                <tr v-for="(std, index) in sortedClassStudents" :key="std.id">
                  <td class="px-4 py-3 font-bold">{{ index + 1 }}</td>
                  <td class="px-4 py-3">{{ std.name_kh || std.name_en }}</td>
                  <td class="px-4 py-3">{{ std.student_id_card || 'N/A' }}</td>
                  <td class="px-4 py-3">{{ std.phone || 'N/A' }}</td>
                  <td class="px-4 py-3 text-center">
                    <button @click="removeStudent(std)" :disabled="removingId === std.id"
                      class="px-3 py-1.5 rounded-lg bg-rose-500 hover:bg-rose-600 text-white text-xs font-medium transition-all disabled:opacity-50 active:scale-95">
                      {{ removingId === std.id ? 'ដក...' : 'ដក' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
          <div class="flex flex-col gap-3 mb-4">
            <div>
              <h2 class="text-lg font-bold text-slate-800">សិស្សសម្រាប់បញ្ចូល</h2>
              <!-- <p class="text-sm text-slate-500 mt-1">សិស្សដែលមានលក្ខណៈដូចគ្នា និងអាចបញ្ចូលបាន</p> -->
            </div>
            <div class="relative">
              <input v-model="suggestedSearchQuery" @input="onSearchInput"
                type="text"
                placeholder="ស្វែងរកសិស្សតាមឈ្មោះ ឬអត្តលេខ..."
                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" />
            </div>
          </div>

          <div v-if="searchingSuggested" class="py-10 text-center text-slate-400 text-sm italic">
            កំពុងស្វែងរក...
          </div>

          <div v-else-if="suggestedStudents.length === 0" class="py-10 text-center text-slate-500">
            មិនមានសិស្សផ្គូផ្គងទេ
          </div>

          <div v-else class="space-y-3">
            <div v-for="student in suggestedStudents" :key="student.id"
              class="flex items-center justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50">
              <div>
                <p class="font-bold text-slate-800">{{ student.name_kh || student.name }}</p>
                <p class="text-xs text-slate-500">{{ student.student_id_card || 'N/A' }}</p>
              </div>
              <button @click="assignStudent(student)" :disabled="assigningId === student.id"
                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all disabled:opacity-50">
                {{ assigningId === student.id ? 'កំពុងបញ្ចូល...' : 'បញ្ចូល' }}
              </button>
            </div>
          </div>
        </section>
      </div>
    </div>

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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { CheckCircle2, XCircle, X } from 'lucide-vue-next'
import api from '../../services/authService'

const route = useRoute()
const router = useRouter()

// ✅ Fix: derive classId reactively from the route instead of capturing it
// once as a plain value — keeps it correct if this component is ever reused
// across a route param change without a full remount.
const classId = computed(() => route.params.id)

const loading = ref(true)
const classRoom = ref(null)
const classStudents = ref([])
const suggestedStudents = ref([])
const suggestedSearchQuery = ref('')
const searchingSuggested = ref(false)
const assigningId = ref(null)
const removingId = ref(null)

let searchDebounceTimer = null

// Toast
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// ✅ Keep the assigned-students table sorted by name for a stable, readable order
const sortedClassStudents = computed(() => {
  return [...classStudents.value].sort((a, b) => {
    const nameA = a.name_kh || a.name_en || ''
    const nameB = b.name_kh || b.name_en || ''
    return String(nameA).localeCompare(String(nameB))
  })
})

const unwrapArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const fetchClassRoom = async () => {
  try {
    const response = await api.get(`/classes/${classId.value}`)
    classRoom.value = response.data
  } catch (error) {
    console.error('Error fetching class detail:', error)
    classRoom.value = null
    showToast('មិនអាចទាញយកព័ត៌មានថ្នាក់បានទេ', 'error')
  }
}

const fetchClassStudents = async () => {
  try {
    const response = await api.get(`/classes/${classId.value}/students`)
    classStudents.value = unwrapArray(response.data)
  } catch (error) {
    console.error('Error fetching class students:', error)
    classStudents.value = []
  }
}

const fetchSuggestedStudents = async () => {
  searchingSuggested.value = true
  try {
    const searchTerm = suggestedSearchQuery.value.trim()
    const params = {}

    // 💡 If no search term: filter by class_id to show matching students
    // If search term exists: search from ALL students (not filtered by class_id)
    if (searchTerm) {
      params.search = searchTerm
    } else {
      params.class_id = classId.value
    }

    const response = await api.get('/students', { params })
    const allStudents = unwrapArray(response.data)
    const assignedIds = new Set(classStudents.value.map(std => std.id))
    suggestedStudents.value = allStudents.filter(std => !assignedIds.has(std.id))
  } catch (error) {
    console.error('Error fetching suggested students:', error)
    suggestedStudents.value = []
  } finally {
    searchingSuggested.value = false
  }
}

// ✅ Fix: debounce the search input instead of firing an API call on every
// keystroke.
const onSearchInput = () => {
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    fetchSuggestedStudents()
  }, 350)
}

const loadData = async () => {
  loading.value = true
  try {
    await fetchClassRoom()
    await fetchClassStudents()
    await fetchSuggestedStudents()
  } finally {
    loading.value = false
  }
}

// ✅ Fix: surface success/failure to the user instead of only logging to the
// console — previously a failed assignment (e.g. class already full, student
// already assigned) looked like the button simply did nothing.
const removeStudent = async (student) => {
  removingId.value = student.id
  try {
    await api.delete(`/classes/${classId.value}/students/${student.id}`)
    await fetchClassStudents()
    await fetchSuggestedStudents()
    showToast(`បានដក ${student.name_kh || student.name} ចេញពីថ្នាក់ដោយជោគជ័យ!`, 'success')
  } catch (error) {
    console.error('Error removing student:', error)
    const backendMessage = error?.response?.data?.message
    showToast(backendMessage || 'មិនអាចដកសិស្សចេញបានទេ!', 'error')
  } finally {
    removingId.value = null
  }
}

const assignStudent = async (student) => {
  assigningId.value = student.id
  try {
    await api.post(`/classes/${classId.value}/assign-student`, { student_id: student.id })
    await fetchClassStudents()
    await fetchSuggestedStudents()
    showToast(`បានបញ្ចូល ${student.name_kh || student.name} ទៅក្នុងថ្នាក់ដោយជោគជ័យ!`, 'success')
  } catch (error) {
    console.error('Error assigning student:', error)
    const backendMessage = error?.response?.data?.message
    showToast(backendMessage || 'មិនអាចបញ្ចូលសិស្សទៅក្នុងថ្នាក់នេះបានទេ!', 'error')
  } finally {
    assigningId.value = null
  }
}

const goBack = () => {
  router.push({ name: 'Classes' })
}

onMounted(() => {
  loadData()
})

onBeforeUnmount(() => {
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
  if (toastTimer) clearTimeout(toastTimer)
})
</script>

<style scoped>
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