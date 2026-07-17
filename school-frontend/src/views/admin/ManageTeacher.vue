<template>
  <div class="min-h-screen bg-slate-50/50 p-6 font-[Battambang] text-slate-700">
    <!-- Title & Add Button -->
    <div class="mb-8 flex flex-col gap-4">
      <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងគ្រូបង្រៀន</h1>
          <p class="mt-1 text-sm text-slate-500">
            គ្រប់គ្រងព័ត៌មាន និងឯកទេសរបស់លោកគ្រូ អ្នកគ្រូក្នុងប្រព័ន្ធ
          </p>
        </div>

        <button
          class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 font-medium text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 active:scale-95 md:w-auto"
          @click="showCreateModal = true"
        >
          <Plus class="h-5 w-5" />
          បន្ថែមគ្រូបង្រៀនថ្មី
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="stat in teacherStats"
        :key="stat.label"
        :class="[
          'flex cursor-pointer items-center gap-4 rounded-2xl border p-5 shadow-sm transition-all active:scale-95',
          activeFilter === stat.filterValue
            ? 'border-indigo-500 bg-white ring-4 ring-indigo-50'
            : 'border-slate-300 bg-white hover:border-indigo-300',
        ]"
        @click="handleStatClick(stat.filterType, stat.filterValue)"
      >
        <div :class="['rounded-xl p-3', stat.bgColor]">
          <component :is="stat.icon" :class="['h-6 w-6', stat.iconColor]" />
        </div>
        <div>
          <p class="text-[14px] font-medium uppercase tracking-wider text-slate-500">
            {{ stat.label }}
          </p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Export Buttons -->
    <div class="mb-4 flex flex-wrap items-center justify-end gap-2">
      <button
        :disabled="filteredTeachers.length === 0"
        class="rounded-xl border border-green-600 bg-white px-5 py-2.5 font-bold text-green-700 transition-all hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
        @click="exportToExcel"
      >
        <span>ទាញយក Excel</span>
      </button>

      <button
        :disabled="filteredTeachers.length === 0"
        class="rounded-xl border border-red-600 bg-white px-5 py-2.5 font-bold text-red-700 transition-all hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
        @click="exportToPDF"
      >
        <span>ទាញយក PDF</span>
      </button>
    </div>

    <!-- Main Table Container -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <!-- Toolbar -->
      <div
        class="flex flex-col items-center justify-between gap-4 border-b border-slate-100 bg-white p-4 sm:flex-row"
      >
        <div class="w-full sm:w-auto">
          <div class="relative w-full sm:max-w-sm">
            <Search
              class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="ស្វែងរកតាមឈ្មោះ ឬលេខទូរស័ព្ទ..."
              class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm transition-all focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            />
          </div>
        </div>

        <div class="flex w-full items-center gap-3 sm:w-auto">
          <div class="relative flex-1 sm:flex-none">
            <select
              v-model="selectedRole"
              class="w-full cursor-pointer appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 sm:w-48"
            >
              <option value="all">គ្រូបង្រៀនទាំងអស់</option>
              <option value="teacher">នៅបង្រៀន</option>
              <option value="student">ឈប់បង្រៀន/ព្យួរ</option>
            </select>
            <div
              class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2"
            >
              <svg
                class="h-4 w-4 text-slate-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Table View -->
      <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
        <div v-if="isLoading" class="p-10 text-center text-sm text-slate-500">
          កំពុងទាញទិន្នន័យ...
        </div>

        <table v-else class="min-w-[1750px] w-full border-collapse text-left">
          <thead
            class="sticky top-0 z-10 bg-slate-50 text-[14px] font-bold uppercase text-slate-600"
          >
            <tr>
              <th
                class="px-6 py-4 text-[14px] font-bold uppercase tracking-wider text-slate-700"
              >
                ល.រ
              </th>
              <th class="px-6 py-4">គ្រូបង្រៀន</th>
              <th class="px-6 py-4">ភេទ</th>
              <th class="px-6 py-4">មុខតំណែង</th>
              <th class="px-6 py-4">ឯកទេស</th>
              <th class="px-6 py-4">ទំនាក់ទំនង</th>
              <th class="px-6 py-4">ថ្ងៃចូលបម្រើការងារ</th>
              <th class="px-6 py-4">ថ្ងៃខែឆ្នាំកំណើត</th>
              <th class="px-6 py-4">ទីកន្លែងកំណើត</th>
              <th class="px-6 py-4">អាសយដ្ឋានបច្ចុប្បន្ន</th>
              <th class="px-6 py-4 text-center">ស្ថានភាព</th>
              <th class="sticky right-0 z-20 bg-slate-50 px-6 py-4 text-right">
                សកម្មភាព
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(teacher, index) in filteredTeachers"
              :key="teacher.id"
              class="hover:bg-slate-50/50"
            >
              <td class="px-8 py-3 text-sm text-slate-500">{{ index + 1 }}</td>

              <!-- Teacher -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm"
                  >
                    <img
                      v-if="teacher.photo && !brokenPhotos.has(teacher.id)"
                      :src="getPhotoUrl(teacher.photo)"
                      class="h-full w-full object-cover"
                      alt="Teacher"
                      @error="onPhotoError(teacher.id)"
                    />
                    <UserRound
                      v-else-if="!teacher.photo"
                      class="h-5 w-5 text-slate-400"
                    />
                    <span
                      v-else
                      class="text-sm font-bold uppercase text-indigo-600"
                    >
                      {{ (teacher.name_kh || 'T').charAt(0) }}
                    </span>
                  </div>

                  <div>
                    <p class="text-[17px] font-bold leading-tight text-slate-700">
                      {{ teacher.name_kh || '-' }}
                    </p>
                    <p
                      class="mt-1 text-[13px] uppercase tracking-tighter text-slate-500"
                    >
                      {{ teacher.name_en || '-' }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- Gender -->
              <td class="px-6 py-4">
                <p class="text-[15px] text-slate-600">
                  {{ getGenderKh(teacher.gender) }}
                </p>
              </td>

              <!-- Position -->
              <td class="px-6 py-4">
                <span
                  class="inline-flex whitespace-nowrap rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-[13px] font-semibold text-indigo-700"
                >
                  {{ getPositionKh(teacher.position) }}
                </span>
              </td>

              <!-- Specialty -->
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="subject in getSpecialties(teacher.specialty).slice(0, 2)"
                    :key="subject"
                    class="rounded border border-slate-200 bg-slate-100 px-2 py-0.5 text-[14px] font-medium text-slate-600"
                  >
                    {{ subject }}
                  </span>
                  <span
                    v-if="getSpecialties(teacher.specialty).length > 2"
                    class="ml-1 self-center text-[10px] font-bold text-slate-400"
                  >
                    +{{ getSpecialties(teacher.specialty).length - 2 }}
                  </span>
                  <span
                    v-if="getSpecialties(teacher.specialty).length === 0"
                    class="text-[12px] italic text-slate-300"
                  >
                    គ្មាន
                  </span>
                </div>
              </td>

              <!-- Contact -->
              <td class="px-6 py-4">
                <p class="text-sm font-medium text-slate-700">
                  {{ teacher.phone || 'N/A' }}
                </p>
                <p class="max-w-[170px] truncate text-[14px] text-slate-500">
                  {{ teacher.email || teacher.user?.email || 'គ្មានគណនី' }}
                </p>
              </td>

              <td class="px-6 py-4 text-sm text-slate-600">
                {{ formatDisplayDate(teacher.hire_date) }}
              </td>

              <td class="px-6 py-4 text-sm text-slate-600">
                {{ formatDisplayDate(teacher.dob) }}
              </td>

              <!-- Place of birth -->
              <td class="px-6 py-4">
                <div class="max-w-[200px] text-[15px] leading-relaxed text-slate-600">
                  {{ formatPlaceOfBirth(teacher) }}
                </div>
              </td>

              <!-- Current address -->
              <td class="px-6 py-4">
                <div class="max-w-[200px] text-[15px] leading-relaxed text-slate-600">
                  {{ formatCurrentAddress(teacher) }}
                </div>
              </td>

              <!-- Status -->
              <td class="px-6 py-4 text-center">
                <span
                  :class="[
                    'whitespace-nowrap rounded-full border px-3 py-1 text-[12px] font-bold uppercase tracking-wider',
                    teacher.status == 1
                      ? 'border-emerald-100 bg-emerald-50 text-emerald-600'
                      : 'border-rose-100 bg-rose-50 text-rose-600',
                  ]"
                >
                  {{ teacher.status == 1 ? 'នៅបង្រៀន' : 'ឈប់បង្រៀន/ផ្អាក' }}
                </span>
              </td>

              <!-- Actions -->
              <td
                class="sticky right-0 z-10 min-w-[120px] bg-white px-6 py-4 text-right"
              >
                <div
                  class="flex flex-shrink-0 items-center justify-end gap-2 whitespace-nowrap"
                >
                  <button
                    class="rounded-lg bg-blue-600 p-2 text-white shadow-sm transition-all hover:bg-blue-700 active:scale-90"
                    title="កែប្រែ"
                    @click="openEditModal(teacher)"
                  >
                    <Edit3 class="h-4 w-4" />
                  </button>

                  <button
                    class="rounded-lg bg-rose-600 p-2 text-white shadow-sm transition-all hover:bg-rose-700 active:scale-90"
                    title="លុប"
                    @click="openDeleteModal(teacher)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredTeachers.length === 0">
              <td colspan="12" class="px-6 py-12 text-center text-sm text-slate-400">
                មិនមានទិន្នន័យគ្រូបង្រៀនឡើយ
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modals -->
    <TeacherCreateModal
      v-if="showCreateModal"
      v-model="showCreateModal"
      :loading="isLoading"
      @refresh="handleCreateTeacher"
    />

    <EditeTeacher
      v-if="isEditModalOpen"
      v-model="isEditModalOpen"
      :teacher-data="selectedTeacher"
      :loading="isLoading"
      @refresh="handleUpdateTeacher"
    />

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div
          v-if="toast.show"
          :class="[
            'fixed right-5 top-5 z-[100] flex min-w-[260px] max-w-xs items-center gap-3 rounded-2xl border px-4 py-3 font-[Battambang] shadow-lg',
            toast.type === 'success'
              ? 'border-emerald-200 bg-white text-emerald-700'
              : 'border-rose-200 bg-white text-rose-600',
          ]"
        >
          <div
            :class="[
              'flex h-8 w-8 shrink-0 items-center justify-center rounded-full',
              toast.type === 'success' ? 'bg-emerald-50' : 'bg-rose-50',
            ]"
          >
            <CheckCircle2
              v-if="toast.type === 'success'"
              class="h-4 w-4 text-emerald-500"
            />
            <XCircle v-else class="h-4 w-4 text-rose-500" />
          </div>
          <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
          <button
            class="text-slate-300 transition-colors hover:text-slate-500"
            @click="toast.show = false"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="isDeleteModalOpen"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(15, 15, 20, 0.5)"
          @click.self="closeDeleteModal"
        >
          <Transition name="modal-scale">
            <div
              v-if="isDeleteModalOpen"
              class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 text-center font-[Battambang]"
            >
              <div
                class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-100"
              >
                <Trash2 class="h-7 w-7 text-rose-500" />
              </div>

              <p class="mb-2 text-[17px] font-bold text-slate-800">លុបគ្រូបង្រៀន</p>
              <p class="mb-1 text-sm leading-relaxed text-slate-600">
                តើអ្នកពិតជាចង់លុបគ្រូបង្រៀន
                <span class="font-bold text-blue-700">
                  {{ deletingTeacher?.name_kh }}
                </span>
                មែនទេ?
              </p>
              <p class="mb-7 text-sm text-slate-400">
                សកម្មភាពនេះមិនអាចដកវិញបានទេ។
              </p>

              <div class="flex gap-3">
                <button
                  :disabled="isDeleting"
                  class="flex-1 rounded-xl border border-slate-200 bg-slate-100 py-2.5 text-sm font-medium text-slate-600 transition-all hover:bg-slate-200 active:scale-95 disabled:opacity-50"
                  @click="closeDeleteModal"
                >
                  បោះបង់
                </button>
                <button
                  :disabled="isDeleting"
                  class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-500 py-2.5 text-sm font-medium text-white transition-all hover:bg-rose-600 active:scale-95 disabled:opacity-60"
                  @click="confirmDeleteTeacher"
                >
                  <Loader2 v-if="isDeleting" class="h-4 w-4 animate-spin" />
                  <Trash2 v-else class="h-4 w-4" />
                  {{ isDeleting ? 'កំពុងលុប...' : 'លុបចោល' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- Hidden Official Letterhead Export Area -->
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

      <h2 class="doc-title">បញ្ជីឈ្មោះគ្រូបង្រៀន</h2>

      <!-- Full teacher data table for landscape PDF export -->
      <table class="pdf-table">
        <thead>
          <tr>
            <th style="width: 3%">ល.រ</th>
            <th style="width: 10%">ឈ្មោះ (ខ្មែរ)</th>
            <th style="width: 10%">ឈ្មោះឡាតាំង</th>
            <th style="width: 5%">ភេទ</th>
            <th style="width: 10%">មុខតំណែង</th>
            <th style="width: 11%">មុខវិជ្ជាជំនាញ</th>
            <th style="width: 9%">ទូរស័ព្ទ</th>
            <th style="width: 8%">ថ្ងៃចូលធ្វើការ</th>
            <th style="width: 8%">ថ្ងៃខែឆ្នាំកំណើត</th>
            <th style="width: 10%">ទីកន្លែងកំណើត</th>
            <th style="width: 10%">អាសយដ្ឋានបច្ចុប្បន្ន</th>
            <th style="width: 6%">ស្ថានភាព</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(teacher, index) in filteredTeachers"
            :key="'pdf-' + (teacher.id || index)"
          >
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td class="pdf-text-left pdf-name-cell">
              {{ teacher.name_kh || '-' }}
            </td>
            <td class="pdf-text-left">{{ teacher.name_en || '-' }}</td>
            <td>{{ getGenderKh(teacher.gender) }}</td>
            <td class="pdf-text-left">{{ getPositionKh(teacher.position) }}</td>
            <td class="pdf-text-left">{{ formatSpecialty(teacher.specialty) }}</td>
            <td>{{ teacher.phone || '-' }}</td>
            <td>{{ formatDisplayDate(teacher.hire_date, '-') }}</td>
            <td>{{ formatDisplayDate(teacher.dob, '-') }}</td>
            <td class="pdf-text-left">{{ formatPlaceOfBirth(teacher) }}</td>
            <td class="pdf-text-left">{{ formatCurrentAddress(teacher) }}</td>
            <td>{{ getStatusKh(teacher.status) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបចំនួនគ្រូបង្រៀន៖ {{ toKhmerNumber(filteredTeachers.length) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp; ស្រី៖ {{ toKhmerNumber(totalFemale) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp; កំពុងបង្រៀន៖ {{ toKhmerNumber(totalActive) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp; ឈប់បង្រៀន៖ {{ toKhmerNumber(totalInactive) }} នាក់
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
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import teacherService from '../../services/teacherService'
import {
  Plus,
  Search,
  Edit3,
  Trash2,
  UserRound,
  GraduationCap,
  Users,
  UserCheck,
  UserMinus,
  CheckCircle2,
  XCircle,
  X,
  Loader2,
} from 'lucide-vue-next'

// Export libraries
import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'

// Modals
import TeacherCreateModal from '../admin/create/CreateTeacherModel.vue'
import EditeTeacher from './edite/EditeTeacher.vue'

// --- State ---
const teachers = ref([])
const isLoading = ref(true)
const printArea = ref(null)
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedGender = ref('')
const selectedRole = ref('all')
const activeFilter = ref('')

const showCreateModal = ref(false)
const isEditModalOpen = ref(false)
const selectedTeacher = ref(null)
const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const deletingTeacher = ref(null)

const brokenPhotos = ref(new Set())
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

// អាចកែសម្រួលឈ្មោះស្ថាប័ន / សាលារបស់អ្នកនៅទីនេះ
const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

// Position labels must match the values used by CreateTeacher and EditTeacher.
const positionLabels = {
  director: 'នាយក',
  principal: 'នាយក',
  deputy_director: 'នាយករង',
  deputy_principal: 'នាយករង',
  academic_director: 'ប្រធានផ្នែកសិក្សា',
  head_teacher: 'ប្រធានក្រុមបច្ចេកទេស',
  teacher: 'គ្រូបង្រៀន',
  secretary: 'លេខាធិការ',
  accountant: 'គណនេយ្យករ',
  administrative_officer: 'មន្ត្រីរដ្ឋបាល',
  librarian: 'បណ្ណារក្ស',
  it_officer: 'មន្ត្រីព័ត៌មានវិទ្យា (IT)',
  laboratory_assistant: 'អ្នកទទួលបន្ទុកបន្ទប់ពិសោធន៍',
  student_counselor: 'អ្នកប្រឹក្សាសិស្ស',
  school_nurse: 'គិលានុបដ្ឋាក/គិលានុបដ្ឋាយិកា',
  security_guard: 'សន្តិសុខ',
  cleaner: 'បុគ្គលិកអនាម័យ',
}

// --- Toast ---
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)

  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => {
    toast.value.show = false
  }, 3500)
}

const API_BASE_URL = (
  import.meta.env.VITE_API_BASE_URL ||
  import.meta.env.VITE_API_URL ||
  'http://localhost:8000'
).replace(/\/+$/, '')

const getPhotoUrl = (path) => {
  if (!path) return null
  if (/^https?:\/\//i.test(path)) return path

  const cleanPath = String(path).replace(/^\/?storage\/?/, '')
  return `${API_BASE_URL}/storage/${cleanPath}`
}

const onPhotoError = (teacherId) => {
  brokenPhotos.value.add(teacherId)
}

// --- Helpers ---
const unwrapArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

// The earliest-created teacher stays on top.
const sortByCreatedAsc = (list) => {
  return [...list].sort((a, b) => {
    const aCreatedTime = a.created_at ? new Date(a.created_at).getTime() : NaN
    const bCreatedTime = b.created_at ? new Date(b.created_at).getTime() : NaN
    const aTime = Number.isFinite(aCreatedTime) ? aCreatedTime : Number(a.id) || 0
    const bTime = Number.isFinite(bCreatedTime) ? bCreatedTime : Number(b.id) || 0
    return aTime - bTime
  })
}

const getGenderKh = (gender) => {
  if (!gender) return '-'

  const value = String(gender).toLowerCase()
  if (value === 'male' || value === 'm' || value === 'ប្រុស') return 'ប្រុស'
  if (value === 'female' || value === 'f' || value === 'ស្រី') return 'ស្រី'
  return '-'
}

const getPositionKh = (position) => {
  if (!position) return '-'

  const value = String(position).trim()
  return positionLabels[value.toLowerCase()] || value
}

const getStatusKh = (status) => {
  return status == 1 ? 'នៅបង្រៀន' : 'ឈប់បង្រៀន/ផ្អាក'
}

const getSpecialties = (data) => {
  if (Array.isArray(data)) return data
  if (!data) return []

  if (typeof data === 'string') {
    try {
      const parsed = JSON.parse(data)
      if (Array.isArray(parsed)) return parsed
      if (typeof parsed === 'string' && parsed.trim()) return [parsed.trim()]
    } catch {
      // Fall through to comma-separated parsing below.
    }

    return data
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean)
  }

  return []
}

const formatSpecialty = (specialty) => {
  const list = getSpecialties(specialty)
  return list.length > 0 ? list.join(', ') : '-'
}

const formatDisplayDate = (value, fallback = 'N/A') => {
  if (!value) return fallback

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return fallback
  return date.toLocaleDateString('km-KH')
}

const formatPlaceOfBirth = (teacher) => {
  return (
    [
      teacher.pob_village,
      teacher.pob_commune || teacher.pob_sangkat,
      teacher.pob_district || teacher.pob_khan,
      teacher.pob_province || teacher.pob_city,
    ]
      .filter(Boolean)
      .join(', ') || '-'
  )
}

const formatCurrentAddress = (teacher) => {
  return (
    [teacher.village, teacher.commune, teacher.district, teacher.province]
      .filter(Boolean)
      .join(', ') || '-'
  )
}

const toKhmerNumber = (num) => {
  if (num === null || num === undefined || num === '') return ''

  const map = {
    0: '០',
    1: '១',
    2: '២',
    3: '៣',
    4: '៤',
    5: '៥',
    6: '៦',
    7: '៧',
    8: '៨',
    9: '៩',
  }

  return String(num).replace(/[0-9]/g, (digit) => map[digit])
}

const sanitizeFileName = (name) => name.replace(/[\\/:*?"<>|]/g, '-').trim()

// --- Fetch ---
const fetchTeachers = async () => {
  isLoading.value = true

  try {
    const response = await teacherService.getTeachers()
    teachers.value = sortByCreatedAsc(unwrapArray(response))
    brokenPhotos.value = new Set()
  } catch (error) {
    console.error('Fetch Error:', error)
    showToast('មិនអាចទាញទិន្នន័យបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

// --- Filters ---
const handleStatClick = (type, value) => {
  if (type === 'gender') {
    selectedGender.value = selectedGender.value === value ? '' : value
    activeFilter.value = selectedGender.value
  } else if (type === 'status') {
    selectedStatus.value = selectedStatus.value === value ? '' : value
    activeFilter.value = selectedStatus.value
  } else {
    resetFilters()
  }
}

const resetFilters = () => {
  selectedGender.value = ''
  selectedStatus.value = ''
  activeFilter.value = ''
  searchQuery.value = ''
  selectedRole.value = 'all'
}

// --- Computed ---
const filteredTeachers = computed(() => {
  return teachers.value.filter((teacher) => {
    const query = searchQuery.value.toLowerCase().trim()
    const searchablePosition = getPositionKh(teacher.position).toLowerCase()

    const matchesSearch =
      !query ||
      teacher.name_kh?.toLowerCase().includes(query) ||
      teacher.name_en?.toLowerCase().includes(query) ||
      teacher.phone?.includes(query) ||
      searchablePosition.includes(query)

    const matchesStatus =
      selectedStatus.value === '' || teacher.status == selectedStatus.value

    const teacherGender = String(teacher.gender || '').toLowerCase()
    const matchesGender =
      selectedGender.value === '' ||
      (selectedGender.value === 'female' &&
        ['female', 'f', 'ស្រី'].includes(teacherGender))

    const matchesRole =
      selectedRole.value === 'all' ||
      (selectedRole.value === 'teacher' && teacher.status == 1) ||
      (selectedRole.value === 'student' && teacher.status == 0)

    return matchesSearch && matchesStatus && matchesGender && matchesRole
  })
})

const totalFemale = computed(() => {
  return filteredTeachers.value.filter((teacher) => {
    const gender = String(teacher.gender || '').toLowerCase()
    return ['female', 'f', 'ស្រី'].includes(gender)
  }).length
})

const totalActive = computed(
  () => filteredTeachers.value.filter((teacher) => teacher.status == 1).length,
)

const totalInactive = computed(
  () => filteredTeachers.value.filter((teacher) => teacher.status != 1).length,
)

const teacherStats = computed(() => {
  const list = teachers.value || []

  return [
    {
      label: 'គ្រូសរុប',
      value: `${list.length} នាក់`,
      icon: GraduationCap,
      bgColor: 'bg-indigo-50',
      iconColor: 'text-indigo-600',
      filterType: '',
      filterValue: '',
    },
    {
      label: 'គ្រូសកម្ម',
      value: `${list.filter((teacher) => teacher.status == 1).length} នាក់`,
      icon: UserCheck,
      bgColor: 'bg-emerald-50',
      iconColor: 'text-emerald-600',
      filterType: 'status',
      filterValue: '1',
    },
    {
      label: 'គ្រូស្រី',
      value: `${
        list.filter((teacher) =>
          ['female', 'f', 'ស្រី'].includes(
            String(teacher.gender || '').toLowerCase(),
          ),
        ).length
      } នាក់`,
      icon: Users,
      bgColor: 'bg-rose-50',
      iconColor: 'text-rose-600',
      filterType: 'gender',
      filterValue: 'female',
    },
    {
      label: 'គ្រូផ្អាក',
      value: `${list.filter((teacher) => teacher.status == 0).length} នាក់`,
      icon: UserMinus,
      bgColor: 'bg-slate-100',
      iconColor: 'text-slate-500',
      filterType: 'status',
      filterValue: '0',
    },
  ]
})

// --- Modal Handlers ---
const openEditModal = (teacher) => {
  selectedTeacher.value = teacher
  isEditModalOpen.value = true
}

const openDeleteModal = (teacher) => {
  deletingTeacher.value = teacher
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return

  isDeleteModalOpen.value = false
  deletingTeacher.value = null
}

const handleCreateTeacher = async () => {
  try {
    showCreateModal.value = false
    await fetchTeachers()
    showToast('បង្កើតគ្រូបង្រៀនបានជោគជ័យ!', 'success')
  } catch (error) {
    showToast(
      error.response?.data?.message || 'មិនអាចបង្កើតគ្រូបង្រៀនបានទេ',
      'error',
    )
  }
}

const handleUpdateTeacher = async () => {
  try {
    isEditModalOpen.value = false
    selectedTeacher.value = null
    await fetchTeachers()
    showToast('កែប្រែគ្រូបង្រៀនបានជោគជ័យ!', 'success')
  } catch (error) {
    showToast(
      error.response?.data?.message || 'មិនអាចកែប្រែគ្រូបង្រៀនបានទេ!',
      'error',
    )
  }
}

const confirmDeleteTeacher = async () => {
  if (!deletingTeacher.value) return

  try {
    isDeleting.value = true
    await teacherService.deleteTeacher(deletingTeacher.value.id)
    isDeleteModalOpen.value = false
    deletingTeacher.value = null
    await fetchTeachers()
    showToast('បានលុបឈ្មោះគ្រូដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast(
      `មិនអាចលុបគ្រូនេះបានទេ: ${
        error.response?.data?.message || 'Error'
      }`,
      'error',
    )
  } finally {
    isDeleting.value = false
  }
}

// --- Excel Styles ---
const THIN_BORDER = { style: 'thin', color: { rgb: '000000' } }
const ALL_BORDERS = {
  top: THIN_BORDER,
  bottom: THIN_BORDER,
  left: THIN_BORDER,
  right: THIN_BORDER,
}

const kingdomStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 15 },
  alignment: { horizontal: 'center', vertical: 'center' },
}

const mottoStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 },
  alignment: { horizontal: 'center', vertical: 'center' },
}

const officeStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 12 },
  alignment: { horizontal: 'left', vertical: 'center' },
}

const titleStyle = {
  font: { name: 'Khmer OS Muol Light', bold: true, sz: 14 },
  alignment: { horizontal: 'center', vertical: 'center' },
}

const tableHeaderStyle = {
  font: { name: 'Khmer OS Battambang', bold: true, sz: 11 },
  alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
  border: ALL_BORDERS,
  fill: { fgColor: { rgb: 'F1F5F9' } },
}

const tableCellStyle = {
  font: { name: 'Khmer OS Battambang', sz: 11 },
  alignment: { horizontal: 'center', vertical: 'center' },
  border: ALL_BORDERS,
}

const tableCellLeftStyle = {
  font: { name: 'Khmer OS Battambang', sz: 10 },
  alignment: {
    horizontal: 'left',
    vertical: 'center',
    wrapText: true,
  },
  border: ALL_BORDERS,
}

const totalStyle = {
  font: { name: 'Khmer OS Battambang', bold: true, sz: 11 },
  alignment: { horizontal: 'left', vertical: 'center' },
}

const dateLineStyle = {
  font: { name: 'Khmer OS Battambang', sz: 11 },
  alignment: { horizontal: 'right', vertical: 'center' },
}

const dateMoulRightStyle = {
  font: { name: 'Khmer OS Muol Light', sz: 11 },
  alignment: { horizontal: 'right', vertical: 'center' },
}

const styleCell = (worksheet, row, column, style) => {
  const address = XLSX.utils.encode_cell({ r: row, c: column })
  if (!worksheet[address]) worksheet[address] = { t: 's', v: '' }
  worksheet[address].s = style
}

// --- Export: Excel ---
const exportToExcel = () => {
  if (filteredTeachers.value.length === 0) return

  // Export every important teacher field in landscape orientation.
  const tableHeader = [
    'ល.រ',
    'ឈ្មោះ (ខ្មែរ)',
    'ឈ្មោះឡាតាំង',
    'ភេទ',
    'មុខតំណែង',
    'មុខវិជ្ជាជំនាញ',
    'ទូរស័ព្ទ',
    'ថ្ងៃចូលធ្វើការ',
    'ថ្ងៃខែឆ្នាំកំណើត',
    'ទីកន្លែងកំណើត',
    'អាសយដ្ឋានបច្ចុប្បន្ន',
    'ស្ថានភាព',
  ]
  const columnCount = tableHeader.length

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine1.value],
    [ministryLine2.value],
    ['បញ្ជីឈ្មោះគ្រូបង្រៀន'],
    [],
  ]

  const tableRows = filteredTeachers.value.map((teacher, index) => [
    toKhmerNumber(index + 1),
    teacher.name_kh || '-',
    teacher.name_en || '-',
    getGenderKh(teacher.gender),
    getPositionKh(teacher.position),
    formatSpecialty(teacher.specialty),
    teacher.phone || '-',
    formatDisplayDate(teacher.hire_date, '-'),
    formatDisplayDate(teacher.dob, '-'),
    formatPlaceOfBirth(teacher),
    formatCurrentAddress(teacher),
    getStatusKh(teacher.status),
  ])

  let cursor = headerRows.length + 1 + tableRows.length
  const extraRows = []
  const styleQueue = []
  const mergeQueue = []

  const pushRow = (rowValues) => {
    extraRows.push(rowValues)
    const row = cursor
    cursor += 1
    return row
  }

  pushRow([])

  const summaryRowIndex = pushRow([
    `សរុបចំនួនគ្រូបង្រៀន៖ ${toKhmerNumber(
      filteredTeachers.value.length,
    )} នាក់    ស្រី៖ ${toKhmerNumber(
      totalFemale.value,
    )} នាក់    កំពុងបង្រៀន៖ ${toKhmerNumber(
      totalActive.value,
    )} នាក់    ឈប់បង្រៀន៖ ${toKhmerNumber(totalInactive.value)} នាក់`,
  ])

  styleQueue.push({ r: summaryRowIndex, c: 0, style: totalStyle })
  mergeQueue.push({ r: summaryRowIndex, c1: 0, c2: columnCount - 1 })

  pushRow([])

  // Put the signature block on the right side of the wide landscape sheet.
  const signatureStartColumn = columnCount - 6
  const createSignatureRow = (text) => {
    const row = Array(signatureStartColumn).fill('')
    row.push(text)
    return row
  }

  const dateRow1Index = pushRow(
    createSignatureRow(
      'ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............',
    ),
  )
  const dateRow2Index = pushRow(
    createSignatureRow('នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'),
  )
  const teacherTitleIndex = pushRow(createSignatureRow('នាយកសាលា'))

  mergeQueue.push({
    r: dateRow1Index,
    c1: signatureStartColumn,
    c2: columnCount - 1,
  })
  mergeQueue.push({
    r: dateRow2Index,
    c1: signatureStartColumn,
    c2: columnCount - 1,
  })
  mergeQueue.push({
    r: teacherTitleIndex,
    c1: signatureStartColumn,
    c2: columnCount - 1,
  })

  styleQueue.push({
    r: dateRow1Index,
    c: signatureStartColumn,
    style: dateLineStyle,
  })
  styleQueue.push({
    r: dateRow2Index,
    c: signatureStartColumn,
    style: dateLineStyle,
  })
  styleQueue.push({
    r: teacherTitleIndex,
    c: signatureStartColumn,
    style: dateMoulRightStyle,
  })

  const worksheetData = [
    ...headerRows,
    tableHeader,
    ...tableRows,
    ...extraRows,
  ]
  const worksheet = XLSX.utils.aoa_to_sheet(worksheetData)

  const tableHeaderRowIndex = headerRows.length
  const tableStartRowIndex = tableHeaderRowIndex + 1
  const tableEndRowIndex = tableStartRowIndex + tableRows.length - 1

  worksheet['!merges'] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: columnCount - 1 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: columnCount - 1 } },
    { s: { r: 3, c: 0 }, e: { r: 3, c: columnCount - 1 } },
    { s: { r: 4, c: 0 }, e: { r: 4, c: columnCount - 1 } },
    { s: { r: 5, c: 0 }, e: { r: 5, c: columnCount - 1 } },
    ...mergeQueue.map((merge) => ({
      s: { r: merge.r, c: merge.c1 },
      e: { r: merge.r, c: merge.c2 },
    })),
  ]

  styleCell(worksheet, 0, 0, kingdomStyle)
  styleCell(worksheet, 1, 0, mottoStyle)
  styleCell(worksheet, 3, 0, officeStyle)
  styleCell(worksheet, 4, 0, officeStyle)
  styleCell(worksheet, 5, 0, titleStyle)

  for (let column = 0; column < columnCount; column += 1) {
    styleCell(worksheet, tableHeaderRowIndex, column, tableHeaderStyle)
  }

  // Left-align text-heavy fields; center short fields and dates.
  const leftAlignedColumns = [1, 2, 4, 5, 9, 10]

  for (let row = tableStartRowIndex; row <= tableEndRowIndex; row += 1) {
    for (let column = 0; column < columnCount; column += 1) {
      const style = leftAlignedColumns.includes(column)
        ? tableCellLeftStyle
        : tableCellStyle
      styleCell(worksheet, row, column, style)
    }
  }

  styleQueue.forEach(({ r, c, style }) => {
    styleCell(worksheet, r, c, style)
  })

  worksheet['!cols'] = [
    { wch: 7 },  // ល.រ
    { wch: 22 }, // ឈ្មោះខ្មែរ
    { wch: 22 }, // ឈ្មោះឡាតាំង
    { wch: 10 }, // ភេទ
    { wch: 24 }, // មុខតំណែង
    { wch: 28 }, // ជំនាញ
    { wch: 16 }, // ទូរស័ព្ទ
    { wch: 17 }, // ថ្ងៃចូលធ្វើការ
    { wch: 17 }, // ថ្ងៃកំណើត
    { wch: 36 }, // ទីកន្លែងកំណើត
    { wch: 36 }, // អាសយដ្ឋានបច្ចុប្បន្ន
    { wch: 20 }, // ស្ថានភាព
  ]

  const worksheetRows = []
  worksheetRows[0] = { hpt: 26 }
  worksheetRows[tableHeaderRowIndex] = { hpt: 34 }
  for (let row = tableStartRowIndex; row <= tableEndRowIndex; row += 1) {
    worksheetRows[row] = { hpt: 32 }
  }
  worksheet['!rows'] = worksheetRows

  worksheet['!pageSetup'] = {
    orientation: 'landscape',
    paperSize: 9,
    fitToWidth: 1,
    fitToHeight: 0,
  }
  worksheet['!printOptions'] = {
    horizontalCentered: true,
    verticalCentered: false,
  }
  worksheet['!margins'] = {
    left: 0.25,
    right: 0.25,
    top: 0.5,
    bottom: 0.5,
    header: 0.2,
    footer: 0.2,
  }

  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'គ្រូបង្រៀន')
  XLSX.writeFile(
    workbook,
    `${sanitizeFileName('បញ្ជីឈ្មោះគ្រូបង្រៀន')}.xlsx`,
  )
}

// --- Export: PDF ---
const exportToPDF = async () => {
  if (filteredTeachers.value.length === 0 || !printArea.value) return

  const options = {
    margin: [8, 8, 8, 8],
    filename: `${sanitizeFileName('បញ្ជីឈ្មោះគ្រូបង្រៀន')}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      scrollX: 0,
      scrollY: 0,
    },
    pagebreak: {
      mode: ['css', 'legacy'],
      avoid: ['tr'],
    },
    jsPDF: {
      unit: 'mm',
      format: 'a4',
      orientation: 'landscape',
    },
  }

  if (document.fonts?.ready) {
    await document.fonts.ready
  }

  printArea.value.style.display = 'block'

  try {
    await html2pdf().set(options).from(printArea.value).save()
  } catch (error) {
    console.error('PDF export failed:', error)
    showToast('មានបញ្ហាក្នុងការទាញយក PDF', 'error')
  } finally {
    printArea.value.style.display = 'none'
  }
}

onMounted(fetchTeachers)

onBeforeUnmount(() => {
  if (toastTimer) clearTimeout(toastTimer)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

/* --- Hidden PDF Export Template Styles --- */
.pdf-export-area {
  display: none;
  box-sizing: border-box;
  width: 277mm;
  margin: 0 auto;
  padding: 10px 12px 18px;
  background: #ffffff;
  color: #000000;
  font-family: 'Battambang', sans-serif;
}

.doc-header {
  text-align: center;
  margin-bottom: 10px;
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
  margin: 3px 0 10px;
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
  margin: 14px 0 18px;
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
  margin: 10px 0 16px;
}

.pdf-table {
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
  font-size: 8.5px;
  line-height: 1.45;
  text-align: center;
}

.pdf-table thead {
  display: table-header-group;
}

.pdf-table tr {
  break-inside: avoid;
  page-break-inside: avoid;
}

.pdf-table th,
.pdf-table td {
  border: 1px solid #000000;
  padding: 4px 3px;
  vertical-align: middle;
  overflow-wrap: anywhere;
  word-break: break-word;
}

.pdf-table th {
  font-weight: 700;
  text-align: center;
  background: #ffffff;
}

.pdf-text-left {
  text-align: left;
}

.pdf-name-cell {
  font-weight: 700;
}

.doc-summary {
  margin-top: 15px;
  font-size: 13px;
  font-weight: bold;
}

.doc-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 25px;
  font-size: 13px;
  line-height: 1.8;
}

.signature-right {
  width: 50%;
  text-align: center;
}

.moul {
  font-family: 'Moul', serif;
  font-size: 13px;
  margin-top: 4px;
}

/* --- General UI Transitions & Scrollbar --- */
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
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
  transition:
    transform 0.2s ease,
    opacity 0.2s ease;
}

.modal-scale-enter-from,
.modal-scale-leave-to {
  transform: scale(0.95);
  opacity: 0;
}

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition:
    transform 0.3s ease,
    opacity 0.3s ease;
}

.toast-slide-enter-from,
.toast-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
