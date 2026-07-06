<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងអ្នកប្រើប្រាស់</h1>
        <p class="text-sm text-slate-500 mt-1">គ្រប់គ្រងសិទ្ធិ និងព័ត៌មានលម្អិតរបស់អ្នកប្រើប្រាស់ក្នុងប្រព័ន្ធ</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button @click="openModal"
          class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          បន្ថែមអ្នកប្រើប្រាស់
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.id" @click="filterByStats(stat.id)" :class="[
        'p-5 rounded-2xl border transition-all cursor-pointer shadow-sm flex items-center gap-4 active:scale-95',
        selectedRole === stat.id
          ? 'bg-white border-indigo-500 ring-4 ring-indigo-50'
          : 'bg-white border-slate-300 hover:border-indigo-300'
      ]">
        <div :class="['p-3 rounded-xl', stat.bgClass]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconClass]" />
        </div>
        <div>
          <p class="text-[14px] text-slate-500 font-medium">{{ stat.label }}</p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
      <button @click="exportToExcel"
        class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
         <span>ទាញយក Excel</span>
      </button>

      <button @click="exportToPDF"
        class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
        <span>ទាញយក PDF</span>
      </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

      <!-- Toolbar -->
    <div class="bg-white p-4 border-b border-slate-100 flex flex-col sm:flex-row items-center gap-4">
  <div class="w-full sm:w-auto">
    <div class="relative w-full sm:max-w-xs">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
      <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ..."
        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" />
    </div>
  </div>

  <div class="flex items-center gap-3 w-full sm:w-auto">
    <div class="relative flex-1 sm:flex-none">
      <select v-model="selectedRole"
        class="w-full sm:w-40 bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none cursor-pointer">
        <option value="all">គ្រប់តួនាទី</option>
        <option value="teacher">គ្រូបង្រៀន</option>
        <option value="student">សិស្សានុសិស្ស</option>
        <option value="admin">អ្នកគ្រប់គ្រង</option>
      </select>
      <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>
  </div>
</div>
      <!-- Table -->
      <div class="overflow-x-auto">
        <div v-if="isLoading" class="p-10 text-center text-slate-500">កំពុងទាញទិន្នន័យ...</div>
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">អ្នកប្រើប្រាស់</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">តួនាទី</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ថ្ងៃចូលរួម</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    {{ user.name?.charAt(0) || 'U' }}
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 leading-tight">{{ user.name }}</p>
                    <p class="text-[14px] text-slate-600 mt-1">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600 capitalize">{{ user.role }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm border',
                  (user.status === 'Active' || user.status == 1)
                    ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                    : 'bg-rose-50 text-rose-600 border-rose-100'
                ]">
                  {{ (user.status === 'Active' || user.status == 1) ? 'សកម្ម' : 'អសកម្ម' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-500">{{ user.joinedDate }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(user)"
                    class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all shadow-sm">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="openDeleteModal(user)"
                    class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all shadow-sm">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm">
                មិនមានទិន្នន័យឡើយ
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <CreateUserModel :isOpen="isModalOpen" :loading="isLoading" @close="closeModal" @submit="handleCreateUser" />

    <!-- Edit Modal -->
    <editeUser :isOpen="isEditModalOpen" :user="editingUser" :loading="isLoading" @close="isEditModalOpen = false"
      @submit="handleUpdateUser" />

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
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(15, 15, 20, 0.5);" @click.self="closeDeleteModal">
          <Transition name="modal-scale">
            <div v-if="isDeleteModalOpen"
              class="bg-white rounded-2xl border border-slate-200 w-full max-w-sm p-8 text-center font-[battambang]">
              <!-- Icon -->
              <div
                class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center mx-auto mb-5 font-[Battambang]">
                <Trash2 class="w-7 h-7 text-rose-500 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]" />
              </div>

              <!-- Title & Message -->
              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបអ្នកប្រើប្រាស់</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingUser?.name }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <!-- Buttons -->
              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDelete" :disabled="isDeleting"
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
  Users, UserCheck, UserPlus, Search, Plus,
  Edit3, Trash2, Loader2, CheckCircle2, XCircle, X
} from 'lucide-vue-next'
import userService from '../../services/userService'
import CreateUserModel from './create/CreateUserModel.vue'
import editeUser from '../admin/edite/EditeUser.vue'

// ─── State ────────────────────────────────────────────────────────────────────

const searchQuery = ref('')
const users = ref([])
const isLoading = ref(false)
const selectedRole = ref('all')
const isModalOpen = ref(false)
const isEditModalOpen = ref(false)
const editingUser = ref(null)

// Delete modal state
const isDeleteModalOpen = ref(false)
const deletingUser = ref(null)
const isDeleting = ref(false)

// Toast state
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// ─── Stats ────────────────────────────────────────────────────────────────────

const stats = ref([
  { id: 'all', label: 'សរុបអ្នកប្រើប្រាស់', value: 0, icon: Users, bgClass: 'bg-indigo-50', iconClass: 'text-indigo-600' },
  { id: 'teacher', label: 'គ្រូបង្រៀន', value: 0, icon: UserCheck, bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' },
  { id: 'student', label: 'សិស្សានុសិស្ស', value: 0, icon: UserPlus, bgClass: 'bg-blue-50', iconClass: 'text-blue-600' },
  { id: 'active', label: 'អ្នកកំពុងសកម្ម', value: 0, icon: UserCheck, bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' },
])

// ─── Fetch ────────────────────────────────────────────────────────────────────

const fetchUsers = async () => {
  try {
    isLoading.value = true
    const response = await userService.getUsers()
    const rawData = response.data || []

    users.value = rawData.map(u => ({
      ...u,
      joinedDate: u.created_at
        ? new Date(u.created_at).toLocaleDateString('km-KH')
        : 'N/A'
    }))

    updateStats()
  } catch (err) {
    console.error('Failed to load users:', err)
  } finally {
    isLoading.value = false
  }
}

const updateStats = () => {
  stats.value[0].value = users.value.length
  stats.value[1].value = users.value.filter(u => u.role?.toLowerCase() === 'teacher').length
  stats.value[2].value = users.value.filter(u => u.role?.toLowerCase() === 'student').length
  stats.value[3].value = users.value.filter(u => u.status == 1 || u.status === 'Active').length
}

// ─── Filter ───────────────────────────────────────────────────────────────────

const filterByStats = (id) => { selectedRole.value = id }

const filteredUsers = computed(() => {
  let result = users.value

  if (selectedRole.value !== 'all') {
    if (selectedRole.value === 'active') {
      result = result.filter(u => u.status === 'Active' || u.status == 1)
    } else {
      result = result.filter(u => u.role?.toLowerCase() === selectedRole.value)
    }
  }

  const query = searchQuery.value.toLowerCase()
  if (query) {
    result = result.filter(u =>
      u.name?.toLowerCase().includes(query) ||
      u.email?.toLowerCase().includes(query)
    )
  }

  return result
})

// ─── Create ───────────────────────────────────────────────────────────────────

const openModal = () => { isModalOpen.value = true }
const closeModal = () => { isModalOpen.value = false }

const handleCreateUser = async (formData) => {
  try {
    isLoading.value = true
    await userService.createUser(formData)
    showToast('បង្កើតអ្នកប្រើប្រាស់បានជោគជ័យ!', 'success')
    closeModal()
    await fetchUsers()
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចបង្កើតអ្នកប្រើប្រាស់បានទេ')
  } finally {
    isLoading.value = false
  }
}

// ─── Edit ─────────────────────────────────────────────────────────────────────

const openEditModal = (user) => {
  editingUser.value = { ...user }
  isEditModalOpen.value = true
}

const handleUpdateUser = async (formData) => {
  try {
    isLoading.value = true
    await userService.updateUser(editingUser.value.id, formData)
    showToast('កែប្រែអ្នកប្រើប្រាស់បានជោគជ័យ!', 'success')
    isEditModalOpen.value = false
    await fetchUsers()
  } catch (err) {
    showToast(err.response?.data?.message || 'មានបញ្ហាក្នុងការកែសម្រួល')
  } finally {
    isLoading.value = false
  }
}

// ─── Delete ───────────────────────────────────────────────────────────────────

const openDeleteModal = (user) => {
  deletingUser.value = user
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  isDeleteModalOpen.value = false
  deletingUser.value = null
}

const confirmDelete = async () => {
  try {
    isDeleting.value = true
    await userService.deleteUser(deletingUser.value.id)
    isDeleteModalOpen.value = false
    deletingUser.value = null
    await fetchUsers()
    showToast('លុបអ្នកប្រើប្រាស់បានជោគជ័យ!', 'success')
  } catch (err) {
    showToast('មិនអាចលុបបានទេ: ' + (err.response?.data?.message || 'Error'), 'error')
  } finally {
    isDeleting.value = false
  }
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(fetchUsers)
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
  transform: translateX(-50%) translateY(-120%);
  opacity: 0;
}

.toast-slide-enter-to,
.toast-slide-leave-from {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}
</style>