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

    <!-- Export Buttons -->
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
      <button @click="exportToExcel" :disabled="filteredUsers.length === 0"
        class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
        <span>ទាញយក Excel</span>
      </button>

      <button @click="exportToPDF" :disabled="filteredUsers.length === 0"
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
              <!-- បានបន្ថែមជួរឈរ ពាក្យសម្ងាត់ -->
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ពាក្យសម្ងាត់</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ថ្ងៃចូលរួម</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    {{ user.name?.charAt(0) || 'U' }}
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 leading-tight">{{ user.name }}</p>
                    <p class="text-[14px] text-slate-600 mt-1">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600 capitalize">{{ user.role }}</td>
              <!-- បង្ហាញទិន្នន័យ ពាក្យសម្ងាត់ -->
              <td class="px-6 py-4 text-sm font-mono font-bold text-slate-700">
                {{ user.plain_password || user.password || '••••••••' }}
              </td>
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
              <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-sm">
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
              <div class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center mx-auto mb-5 font-[Battambang]">
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

    <!-- ========================================================================= -->
    <!-- Hidden Official Letterhead Export Area (For PDF Export - Includes Password) -->
    <!-- ========================================================================= -->
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

      <h2 class="doc-title">បញ្ជីព័ត៌មានគណនីអ្នកប្រើប្រាស់</h2>

      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width:6%">ល.រ</th>
            <th style="width:24%">ឈ្មោះអ្នកប្រើប្រាស់</th>
            <th style="width:25%">អ៊ីមែល</th>
            <th style="width:12%">តួនាទី</th>
            <th style="width:15%">ពាក្យសម្ងាត់</th>
            <th style="width:10%">ស្ថានភាព</th>
            <th style="width:8%">ថ្ងៃចូលរួម</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in filteredUsers" :key="'pdf-' + (user.id || index)">
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td style="text-align:left; font-weight:bold;">{{ user.name || '-' }}</td>
            <td style="text-align:left;">{{ user.email || '-' }}</td>
            <td style="text-transform: uppercase;">{{ user.role || 'user' }}</td>
            <td style="font-weight:bold; font-family: monospace;">{{ user.plain_password || user.password || '••••••••' }}</td>
            <td>{{ (user.status === 'Active' || user.status == 1) ? 'សកម្ម' : 'អសកម្ម' }}</td>
            <td>{{ user.joinedDate }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបគណនីទាំងអស់៖ {{ toKhmerNumber(filteredUsers.length) }} គណនី 
          &nbsp;&nbsp;&nbsp;&nbsp;
          គ្រូបង្រៀន៖ {{ toKhmerNumber(filteredUsers.filter(u => u.role?.toLowerCase() === 'teacher').length) }} នាក់ 
          &nbsp;&nbsp;&nbsp;&nbsp;
          សិស្ស៖ {{ toKhmerNumber(filteredUsers.filter(u => u.role?.toLowerCase() === 'student').length) }} នាក់
        </strong>
      </div>

      <div class="doc-footer">
        <div class="signature-right">
          <p>ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............</p>
          <p>នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........</p>
          <p class="moul">នាយកសាលា</p>
        </div>
      </div>
    </div>

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

// Export Libraries
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'

// ─── State ────────────────────────────────────────────────────────────────────

const searchQuery = ref('')
const users = ref([])
const isLoading = ref(false)
const selectedRole = ref('all')
const isModalOpen = ref(false)
const isEditModalOpen = ref(false)
const editingUser = ref(null)
const printArea = ref(null)

// Delete modal state
const isDeleteModalOpen = ref(false)
const deletingUser = ref(null)
const isDeleting = ref(false)




const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

// Toast state
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

const toKhmerNumber = (num) => {
  if (num === null || num === undefined || num === '') return ''
  const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' }
  return String(num).replace(/[0-9]/g, (s) => map[s])
}

const sanitizeFileName = (name) => name.replace(/[\\/:*?"<>|]/g, '-').trim()

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

    // Sort Oldest -> Newest
    rawData.sort((a, b) => {
      return new Date(a.created_at) - new Date(b.created_at)
    })

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

// ─── Export Excel & PDF ────────────────────────────────────────────────────────

const THIN_BORDER = { style: 'thin', color: { rgb: '000000' } }
const ALL_BORDERS = { top: THIN_BORDER, bottom: THIN_BORDER, left: THIN_BORDER, right: THIN_BORDER }

const kingdomStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 15 }, alignment: { horizontal: 'center', vertical: 'center' } }
const mottoStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 }, alignment: { horizontal: 'center', vertical: 'center' } }
const officeStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 }, alignment: { horizontal: 'left', vertical: 'center' } }
const titleStyle = { font: { name: 'Khmer OS Muol Light', bold: true, sz: 14 }, alignment: { horizontal: 'center', vertical: 'center' } }
const tableHeaderStyle = { font: { name: 'Khmer OS Battambang', bold: true, sz: 11 }, alignment: { horizontal: 'center', vertical: 'center', wrapText: true }, border: ALL_BORDERS, fill: { fgColor: { rgb: 'F1F5F9' } } }
const tableCellStyle = { font: { name: 'Khmer OS Battambang', sz: 11 }, alignment: { horizontal: 'center', vertical: 'center' }, border: ALL_BORDERS }
const tableCellLeftStyle = { font: { name: 'Khmer OS Battambang', sz: 11 }, alignment: { horizontal: 'left', vertical: 'center' }, border: ALL_BORDERS }
const totalStyle = { font: { name: 'Khmer OS Battambang', bold: true, sz: 11 }, alignment: { horizontal: 'left', vertical: 'center' } }
const dateLineStyle = { font: { name: 'Khmer OS Battambang', sz: 11 }, alignment: { horizontal: 'right', vertical: 'center' } }
const dateMoulRightStyle = { font: { name: 'Khmer OS Muol Light', sz: 11 }, alignment: { horizontal: 'right', vertical: 'center' } }

const styleCell = (ws, r, c, style) => {
  const addr = XLSX.utils.encode_cell({ r, c })
  if (!ws[addr]) ws[addr] = { t: 's', v: '' }
  ws[addr].s = style
}

const exportToExcel = () => {
  if (filteredUsers.value.length === 0) return

  const tableHeader = ['ល.រ', 'ឈ្មោះអ្នកប្រើប្រាស់', 'អ៊ីមែល', 'តួនាទី', 'ពាក្យសម្ងាត់', 'ស្ថានភាព', 'ថ្ងៃចូលរួម']
  const colCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine1.value],
    [ministryLine2.value],
    ['បញ្ជីព័ត៌មានគណនីអ្នកប្រើប្រាស់'],
    [],
  ]

  const tableRows = filteredUsers.value.map((u, index) => {
    return [
      toKhmerNumber(index + 1),
      u.name || '',
      u.email || '',
      (u.role || 'user').toUpperCase(),
      u.plain_password || u.password || '••••••••',
      (u.status === 'Active' || u.status == 1) ? 'សកម្ម' : 'អសកម្ម',
      u.joinedDate
    ]
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

  const summaryRowIdx = pushRow([
    `សរុបគណនីទាំងអស់៖ ${toKhmerNumber(filteredUsers.value.length)} គណនី    គ្រូបង្រៀន៖ ${toKhmerNumber(filteredUsers.value.filter(u => u.role?.toLowerCase() === 'teacher').length)} នាក់    សិស្ស៖ ${toKhmerNumber(filteredUsers.value.filter(u => u.role?.toLowerCase() === 'student').length)} នាក់`
  ])
  styleQueue.push({ r: summaryRowIdx, c: 0, style: totalStyle })
  mergeQueue.push({ r: summaryRowIdx, c1: 0, c2: colCount - 1 })

  pushRow([])

  const dateRow1Idx = pushRow(['', '', '', '', 'ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............'])
  const dateRow2Idx = pushRow(['', '', '', '', 'នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'])
  const teacherTitleIdx = pushRow(['', '', '', '', 'នាយកសាលា'])

  mergeQueue.push({ r: dateRow1Idx, c1: 4, c2: colCount - 1 })
  mergeQueue.push({ r: dateRow2Idx, c1: 4, c2: colCount - 1 })
  mergeQueue.push({ r: teacherTitleIdx, c1: 4, c2: colCount - 1 })
  styleQueue.push({ r: dateRow1Idx, c: 4, style: dateLineStyle })
  styleQueue.push({ r: dateRow2Idx, c: 4, style: dateLineStyle })
  styleQueue.push({ r: teacherTitleIdx, c: 4, style: dateMoulRightStyle })

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
      // តម្រឹមឆ្វេងសម្រាប់ ឈ្មោះ (1) និង អ៊ីមែល (2)
      const isLeftAlign = [1, 2].includes(c)
      styleCell(ws, r, c, isLeftAlign ? tableCellLeftStyle : tableCellStyle)
    }
  }

  styleQueue.forEach(({ r, c, style }) => styleCell(ws, r, c, style))

  ws['!cols'] = [
    { wch: 6 }, { wch: 25 }, { wch: 28 }, { wch: 14 }, { wch: 20 },
    { wch: 14 }, { wch: 16 }
  ]

  ws['!rows'] = [{ hpt: 26 }]

  ws['!pageSetup'] = { orientation: 'portrait', fitToWidth: 1, fitToHeight: 0 }
  ws['!printOptions'] = { horizontalCentered: true }
  ws['!margins'] = { left: 0.5, right: 0.5, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'គណនីអ្នកប្រើប្រាស់')
  XLSX.writeFile(wb, `${sanitizeFileName('បញ្ជីគណនីអ្នកប្រើប្រាស់')}.xlsx`)
}

const exportToPDF = async () => {
  if (filteredUsers.value.length === 0) return
  if (!printArea.value) return

  const opt = {
    margin: [12, 10, 12, 10],
    filename: `${sanitizeFileName('បញ្ជីគណនីអ្នកប្រើប្រាស់')}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
  }

  if (document.fonts && document.fonts.ready) {
    await document.fonts.ready
  }

  printArea.value.style.display = 'block'
  try {
    await html2pdf().set(opt).from(printArea.value).save()
  } catch (err) {
    console.error('PDF export failed:', err)
    showToast('មានបញ្ហាក្នុងការទាញយក PDF', 'error')
  } finally {
    printArea.value.style.display = 'none'
  }
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(fetchUsers)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

/* --- Hidden PDF Export Template Styles (A4 Landscape) --- */
.pdf-export-area {
  display: none;
  width: 275mm;
  margin: 0 auto;
  padding: 10px 15px 20px;
  background: #ffffff;
  color: #000000;
  font-family: 'Battambang', sans-serif;
}

.doc-header {
  text-align: center;
  margin-bottom: 8px;
}

.kingdom-title {
  font-family: 'Moul', serif;
  font-size: 14px;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.kingdom-motto {
  font-family: 'Moul', serif;
  font-weight: 500;
  font-size: 14px;
  margin: 3px 0 8px;
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
  font-size: 11px;
  line-height: 1;
}

.doc-office {
  font-family: 'Moul', serif, Arial, Helvetica, sans-serif;
  font-weight: 500;
  font-size: 13px;
  margin: 10px 0 14px;
}

.doc-office p {
  font-family: 'Moul', serif;
  font-size: 13px;
  line-height: 1.8;
  margin: 2px 0;
}

.doc-title {
  font-family: 'Moul', Arial, sans-serif;
  font-weight: 500;
  font-size: 15px;
  text-align: center;
  margin: 8px 0 14px;
}

.pdf-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
  text-align: center;
}

.pdf-table th,
.pdf-table td {
  border: 1px solid #000000;
  padding: 6px 8px;
  vertical-align: middle;
}

.pdf-table th {
  font-weight: 700;
  text-align: center;
  background: #ffffff;
}

.doc-summary {
  margin-top: 14px;
  font-size: 13px;
  font-weight: bold;
}

.doc-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
  font-size: 13px;
  line-height: 1.8;
}

.signature-right {
  width: 35%;
  text-align: center;
}

.moul {
  font-family: 'Moul', serif;
  font-size: 13px;
  margin-top: 4px;
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