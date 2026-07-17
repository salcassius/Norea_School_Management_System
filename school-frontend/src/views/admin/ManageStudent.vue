<template>
  <div class="font-[Battambang] p-4 sm:p-6 min-h-screen bg-slate-50/50 text-slate-700">
    <!-- Title & Add Button -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">គ្រប់គ្រងសិស្សានុសិស្ស</h1>
        <p class="text-sm text-slate-500 mt-1">គ្រប់គ្រងទិន្នន័យ បញ្ជីឈ្មោះ និងស្ថានភាពសិក្សារបស់សិស្ស</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button @click="openCreateModal"
          class="w-full md:w-auto flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          <span>បន្ថែមសិស្សថ្មី</span>
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
      <div v-for="stat in studentStats" :key="stat.label" @click="handleStatClick(stat.filterType, stat.filterValue)"
        :class="[
          'p-5 rounded-2xl border transition-all cursor-pointer shadow-sm flex items-center gap-4 active:scale-95',
          activeFilter === stat.filterValue ? 'bg-white border-indigo-500 ring-4 ring-indigo-50' : 'bg-white border-slate-300 hover:border-indigo-300'
        ]">
        <div :class="['p-3 rounded-xl', stat.bgColor]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
        </div>
        <div>
          <p class="text-[14px] text-slate-500 font-medium uppercase tracking-wider">{{ stat.label }}</p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Export Buttons -->
    <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center justify-end gap-2 mb-4">
      <button @click="exportToExcel" :disabled="filteredStudents.length === 0"
        class="w-full sm:w-auto bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
        <span>ទាញយក Excel</span>
      </button>
      <button @click="exportToPDF" :disabled="filteredStudents.length === 0"
        class="w-full sm:w-auto bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
        <span>ទាញយក PDF</span>
      </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Toolbar -->
      <div class="bg-white p-4 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative max-w-sm w-full">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះ, ID, ឬលេខទូរស័ព្ទ..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" />
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
          <select v-model="selectedStatus"
            class="w-full sm:w-48 bg-slate-50 border border-slate-200 text-slate-600 text-sm rounded-xl px-4 py-2.5 outline-none focus:border-indigo-500 transition-all cursor-pointer">
            <option value="">ស្ថានភាពទាំងអស់</option>
            <option value="1">កំពុងរៀន</option>
            <option value="0">ឈប់រៀន/ព្យួរ</option>
          </select>
          <button v-if="activeFilter || searchQuery || selectedStatus !== ''" @click="resetFilters"
            class="text-xs text-indigo-600 hover:underline font-bold whitespace-nowrap">បង្ហាញទាំងអស់</button>
        </div>
      </div>

      <!-- Table Container with Horizontal Scroll (scroll sideways on phone, same style as Teacher.vue) -->
      <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
        <div v-if="isLoading" class="p-20 text-center flex flex-col items-center gap-3">
          <div class="w-8 h-8 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
          <span class="text-slate-500 text-sm font-medium">កំពុងទាញទិន្នន័យ...</span>
        </div>

        <table v-else class="w-full text-left border-collapse min-w-[2600px]">
          <thead class="sticky top-0 z-10 bg-slate-50/50">
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ល.រ</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">សិស្សានុសិស្ស</th>

              <!-- Normal Columns -->
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">អត្តលេខ</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ភេទ</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ថ្ងៃកំណើត</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">មកពីបឋមសិក្សា</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ទំនាក់ទំនង</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ទីកន្លែងកំណើត</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">អាសយដ្ឋានបច្ចុប្បន្ន</th>

              <!-- ឪពុក -->
              <th class="px-4 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ឪពុក</th>
              <th class="px-4 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">មុខរបរឪពុក</th>
              <th class="px-4 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ទូរស័ព្ទឪពុក</th>

              <!-- ម្តាយ -->
              <th class="px-4 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ម្តាយ</th>
              <th class="px-4 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">មុខរបរម្តាយ</th>
              <th class="px-4 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ទូរស័ព្ទម្តាយ</th>

              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>

              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right sticky right-0 bg-slate-50 z-20">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(student, index) in filteredStudents" :key="student.id" class="hover:bg-gray-50">
              <!-- ល.រ -->
              <td class="px-6 py-3 text-center text-gray-500 font-bold">{{ index + 1 }}</td>

              <!-- សិស្សានុសិស្ស -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 shadow-sm">
                    <img v-if="student.photo && !brokenPhotos.has(student.id)" :src="getPhotoUrl(student.photo)"
                      @error="onPhotoError(student.id)" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                      <UserRound class="w-5 h-5" />
                    </div>
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-700 leading-tight">{{ student.name_kh }}</p>
                    <p class="text-[12px] text-slate-600 mt-1 uppercase tracking-tight">{{ student.name_en }}</p>
                  </div>
                </div>
              </td>

              <!-- អត្តលេខ -->
              <td class="px-6 py-4 text-[13px] text-indigo-600 uppercase">{{ student.student_id_card || 'N/A' }}</td>

              <!-- ភេទ -->
              <td class="px-6 py-4 text-sm text-slate-600">{{ student.gender === 'female' ? 'ស្រី' : 'ប្រុស' }}</td>

              <!-- ថ្ងៃកំណើត -->
              <td class="px-6 py-4 text-sm text-slate-600">
                {{ student.date_of_birth ? new Date(student.date_of_birth).toLocaleDateString('km-KH') : '-' }}
              </td>

              <!-- មកពីបឋមសិក្សា -->
              <td class="px-6 py-4 text-sm text-slate-600">{{ student.pri_school || '-' }}</td>

              <!-- ទំនាក់ទំនង -->
              <td class="px-6 py-4">
                <p class="text-sm text-slate-600 font-medium">{{ student.phone || 'N/A' }}</p>
                <p class="text-[14px] text-slate-600 truncate max-w-[150px]">{{ student.email }}</p>
              </td>

              <!-- ទីកន្លែងកំណើត -->
              <td class="px-6 py-4 text-sm text-slate-600">
                <div class="text-[14px] max-w-[200px]">
                  {{ [student.pob_village, student.pob_commune, student.pob_district, student.pob_province].filter(Boolean).join(', ') || '-' }}
                </div>
              </td>

              <!-- អាសយដ្ឋានបច្ចុប្បន្ន -->
              <td class="px-6 py-4 text-sm text-slate-600">
                <div class="text-[14px] max-w-[200px]">
                  {{ [student.village, student.commune, student.district, student.province].filter(Boolean).join(', ') || '-' }}
                </div>
              </td>

              <!-- ឪពុក -->
              <td class="px-4 py-4 text-slate-700 text-[14px]">{{ student.guardian_dad_name || '-' }}</td>
              <td class="px-4 py-4 text-slate-700 text-[14px]">{{ student.guardian_dad_job || '-' }}</td>
              <td class="px-4 py-4 text-slate-700 text-[14px]">{{ student.guardian_dad_phone || '-' }}</td>

              <!-- ម្តាយ -->
              <td class="px-4 py-4 text-[14px] text-slate-700">{{ student.guardian_mom_name || '-' }}</td>
              <td class="px-4 py-4 text-[14px] text-slate-700">{{ student.guardian_mom_job || '-' }}</td>
              <td class="px-4 py-4 text-[14px] text-slate-700">{{ student.guardian_mom_phone || '-' }}</td>

              <!-- ស្ថានភាព -->
              <td class="px-6 py-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider border transition-all duration-300',
                  student.status == 1 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                ]">
                  {{ student.status == 1 ? 'កំពុងរៀន' : 'ឈប់រៀន/ព្យួរ' }}
                </span>
              </td>

              <!-- សកម្មភាព | Sticky Right -->
              <td class="px-6 py-4 text-right sticky right-0 bg-white z-10">
                <div class="flex items-center justify-end gap-2">
                  <!-- Inside the action td -->
                  <!-- <button @click="openDetailModal(student)" 
                          class="p-2 text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-90 shadow-sm"
                          title="មើលលម្អិត">
                    <Eye class="w-4 h-4" />
                  </button> -->
                  <button @click="openDetailModal(student)" 
        class="p-2 text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-90 shadow-sm"
        title="មើលលម្អិត">
  <Eye class="w-4 h-4" />
</button>
                  <button @click="handleEdit(student)" title="កែសម្រួល"
                    class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-90 shadow-sm">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="openDeleteModal(student)" title="លុប"
                    class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all active:scale-90 shadow-sm">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredStudents.length === 0 && !isLoading">
              <td colspan="17" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <UserRound class="w-12 h-12 text-slate-200" />
                  <p class="text-slate-400 text-sm font-medium">មិនមានទិន្នន័យសិស្សត្រូវតាមលក្ខខណ្ឌស្វែងរកឡើយ</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modals -->
    <StudentCreateModal v-if="showCreateModal" v-model="showCreateModal" @refresh="handleCreateStudent" />
    <StudentEditModal v-if="showEditModal" v-model="showEditModal" :student-data="selectedStudent"
      @refresh="handleUpdateStudent" />
      <DetailStudent 
  v-model="showDetailModal" 
  :student-id="selectedDetailId" 
/>

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.show" :class="[
          'fixed top-5 right-5 left-5 sm:left-auto z-[100] flex items-center gap-3 px-4 py-3 rounded-2xl border shadow-lg sm:min-w-[260px] sm:max-w-xs font-[Battambang]',
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
              class="bg-white rounded-2xl border border-slate-200 w-full max-w-sm p-6 sm:p-8 text-center font-[Battambang]">

              <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
                <Trash2 class="w-7 h-7 text-rose-500" />
              </div>

              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបសិស្សានុសិស្ស</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុប
                <span class="font-bold text-blue-700">{{ deletingStudent?.name_kh }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>

              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteStudent" :disabled="isDeleting"
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

    <!-- ===== Hidden Official Letterhead Export Area (For Landscape PDF) ===== -->
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

      <h2 class="doc-title">បញ្ជីព័ត៌មានសិស្សានុសិស្សទាំងអស់</h2>

      <table class="pdf-table">
        <thead>
          <tr>
            <th rowspan="2" style="width:3%">ល.រ</th>
            <th rowspan="2" style="width:5%">អត្តលេខ</th>
            <th rowspan="2" style="width:8%">ឈ្មោះ (ខ្មែរ)</th>
            <th rowspan="2" style="width:8%">ឈ្មោះឡាតាំង</th>
            <th rowspan="2" style="width:3%">ភេទ</th>
            <th rowspan="2" style="width:5%">ថ្ងៃខែឆ្នាំកំណើត</th>
            <th rowspan="2" style="width:6%">លេខទូរស័ព្ទ</th>
            <th colspan="4">ទីកន្លែងកំណើត</th>
            <th colspan="4">អាសយដ្ឋានបច្ចុប្បន្ន</th>
            <th rowspan="2" style="width:5%">មកពីសាលា</th>
            <th rowspan="2" style="width:4%">មកពីថ្នាក់ទី</th>
            <th colspan="3">ព័ត៌មានឪពុក</th>
            <th colspan="3">ព័ត៌មានម្តាយ</th>
          </tr>
          <tr>
            <th>ភូមិ</th>
            <th>ឃុំ/សង្កាត់</th>
            <th>ស្រុក/ខណ្ឌ</th>
            <th>ខេត្ត/ក្រុង</th>
            <th>ភូមិ</th>
            <th>ឃុំ/សង្កាត់</th>
            <th>ស្រុក/ខណ្ឌ</th>
            <th>ខេត្ត/ក្រុង</th>
            <th>ឈ្មោះ</th>
            <th>មុខរបរ</th>
            <th>ទូរស័ព្ទ</th>
            <th>ឈ្មោះ</th>
            <th>មុខរបរ</th>
            <th>ទូរស័ព្ទ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(student, index) in filteredStudentsSortedByClass" :key="'pdf-' + (student.id || index)">
            <td>{{ toKhmerNumber(index + 1) }}</td>
            <td>{{ student.student_id_card || '-' }}</td>
            <td style="text-align:left; font-weight:bold;">{{ student.name_kh || '-' }}</td>
            <td style="text-align:left;">{{ student.name_en || '-' }}</td>
            <td>{{ getGenderKh(student.gender) }}</td>
            <td>{{ formatDate(student.date_of_birth) }}</td>
            <td>{{ student.phone || '-' }}</td>
            <td style="text-align:left;">{{ student.pob_village || '-' }}</td>
            <td style="text-align:left;">{{ student.pob_commune || '-' }}</td>
            <td style="text-align:left;">{{ student.pob_district || '-' }}</td>
            <td style="text-align:left;">{{ student.pob_province || '-' }}</td>
            <td style="text-align:left;">{{ student.village || '-' }}</td>
            <td style="text-align:left;">{{ student.commune || '-' }}</td>
            <td style="text-align:left;">{{ student.district || '-' }}</td>
            <td style="text-align:left;">{{ student.province || '-' }}</td>
            <td style="text-align:left;">{{ student.pri_school || '-' }}</td>
            <td>{{ student.from_class || '-' }}</td>
            <td style="text-align:left; font-weight:bold;">{{ student.guardian_dad_name || '-' }}</td>
            <td style="text-align:left;">{{ student.guardian_dad_job || '-' }}</td>
            <td>{{ student.guardian_dad_phone || '-' }}</td>
            <td style="text-align:left; font-weight:bold;">{{ student.guardian_mom_name || '-' }}</td>
            <td style="text-align:left;">{{ student.guardian_mom_job || '-' }}</td>
            <td>{{ student.guardian_mom_phone || '-' }}</td>
          </tr>
        </tbody>
      </table>

      <div class="doc-summary">
        <strong>
          សរុបចំនួនសិស្ស៖ {{ toKhmerNumber(filteredStudents.length) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ស្រី៖ {{ toKhmerNumber(totalFemale) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          កំពុងរៀន៖ {{ toKhmerNumber(totalActive) }} នាក់
          &nbsp;&nbsp;&nbsp;&nbsp;
          ឈប់រៀន/ព្យួរ៖ {{ toKhmerNumber(totalInactive) }} នាក់
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
import { ref, onMounted, computed } from 'vue'
import studentService from '../../services/studentService'
import {
  Plus, Search, Edit3, Trash2, UserRound,
  Users, BookOpen, GraduationCap,
  CheckCircle2, XCircle, X, Loader2, Eye
} from 'lucide-vue-next'


import * as XLSX from 'xlsx-js-style'
import html2pdf from 'html2pdf.js'


import DetailStudent from '../admin/DetailStudent.vue'  
import StudentCreateModal from '../admin/create/CreateStudentModel.vue'
import StudentEditModal from '../admin/edite/EditeStudent.vue'

// --- State ---
const students = ref([])
const isLoading = ref(true)
const printArea = ref(null)
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedGender = ref('')
const activeFilter = ref('')

const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedStudent = ref(null)

const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const deletingStudent = ref(null)

const brokenPhotos = ref(new Set())
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

const ministryLine1 = ref('មន្ទីរអប់រំ យុវជន និងកីឡាខេត្តបាត់ដំបង')
const ministryLine2 = ref('អនុវិទ្យាល័យ នរា')

// --- Toast ---
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}


const showDetailModal = ref(false)
const selectedDetailId = ref(null)

const openDetailModal = (student) => {
  selectedDetailId.value = student.id
  showDetailModal.value = true
}

const API_BASE_URL = (
  import.meta.env.VITE_API_BASE_URL ||
  import.meta.env.VITE_API_URL ||
  'http://localhost:8000'
).replace(/\/+$/, '')

const getPhotoUrl = (path) => {
  if (!path) return null
  if (/^https?:\/\//i.test(path)) return path
  const cleanPath = path.replace(/^\/?storage\/?/, '')
  return `${API_BASE_URL}/storage/${cleanPath}`
}

const onPhotoError = (studentId) => {
  brokenPhotos.value.add(studentId)
}

// --- Helpers ---
const unwrapArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

const getGenderKh = (gender) => {
  if (!gender) return '-'
  const g = String(gender).toLowerCase()
  if (g === 'male' || g === 'm' || g === 'ប្រុស') return 'ប្រុស'
  if (g === 'female' || g === 'f' || g === 'ស្រី') return 'ស្រី'
  return '-'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('km-KH')
  } catch {
    return String(dateString)
  }
}

const toKhmerNumber = (num) => {
  if (num === null || num === undefined || num === '') return ''
  const map = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' }
  return String(num).replace(/[0-9]/g, (s) => map[s])
}

const sanitizeFileName = (name) => name.replace(/[\\/:*?"<>|]/g, '-').trim()
// --- Fetch ---
const fetchStudents = async () => {
  isLoading.value = true

  try {
    const res = await studentService.getStudents()
    const data = unwrapArray(res)

    // ថ្មីបំផុតនៅក្រោមគេ → sort តាម created_at ឡើង (oldest → newest)
    data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))

    students.value = data
    brokenPhotos.value = new Set()

  } catch (error) {
    console.error('Fetch error:', error)
    students.value = []
    showToast('មិនអាចទាញទិន្នន័យបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}
// --- Filters ---
const handleStatClick = (type, value) => {
  if (type === 'gender') {
    selectedStatus.value = ''
    selectedGender.value = selectedGender.value === value ? '' : value
    activeFilter.value = selectedGender.value
  } else if (type === 'status') {
    selectedGender.value = ''
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
}

// --- Computed ---
const filteredStudents = computed(() => {
  return (students.value || []).filter(student => {
    const search = searchQuery.value.toLowerCase().trim()
    const matchesSearch = !search ||
      (student.name_kh?.toLowerCase().includes(search)) ||
      (student.name_en?.toLowerCase().includes(search)) ||
      (student.student_id_card?.toLowerCase().includes(search)) ||
      (student.phone?.includes(search))

    const matchesStatus = selectedStatus.value === '' || student.status == selectedStatus.value
    const matchesGender = selectedGender.value === '' ||
      ['female', 'f', 'ស្រី'].includes(String(student.gender).toLowerCase()) === (selectedGender.value === 'female')

    return matchesSearch && matchesStatus && matchesGender
  })
})

const totalFemale = computed(() => {
  return filteredStudents.value.filter(s => ['female', 'f', 'ស្រី'].includes(String(s.gender).toLowerCase())).length
})

const totalActive = computed(() => filteredStudents.value.filter(s => s.status == 1).length)
const totalInactive = computed(() => filteredStudents.value.filter(s => s.status != 1).length)

// Sort filteredStudents by from_class for exports
const filteredStudentsSortedByClass = computed(() => {
  return [...filteredStudents.value].sort((a, b) => {
    const aClass = String(a.from_class || '').toLowerCase().trim()
    const bClass = String(b.from_class || '').toLowerCase().trim()
    return aClass.localeCompare(bClass)
  })
})

const studentStats = computed(() => [
  { label: 'សិស្សសរុប', value: students.value.length + ' នាក់', icon: Users, bgColor: 'bg-indigo-50', iconColor: 'text-indigo-600', filterType: 'all', filterValue: '' },
  { label: 'សិស្សស្រី', value: students.value.filter(s => ['female', 'f', 'ស្រី'].includes(String(s.gender).toLowerCase())).length + ' នាក់', icon: GraduationCap, bgColor: 'bg-rose-50', iconColor: 'text-rose-600', filterType: 'gender', filterValue: 'female' },
  { label: 'សិស្សប្រុស', value: students.value.filter(s => ['male', 'm', 'ប្រុស'].includes(String(s.gender).toLowerCase())).length + ' នាក់', icon: UserRound, bgColor: 'bg-blue-50', iconColor: 'text-blue-600', filterType: 'gender', filterValue: 'male' },
  { label: 'កំពុងរៀន', value: students.value.filter(s => s.status == 1).length + ' នាក់', icon: BookOpen, bgColor: 'bg-emerald-50', iconColor: 'text-emerald-600', filterType: 'status', filterValue: '1' }
])

// --- Create / Edit / Delete Handlers ---
const openCreateModal = () => { showCreateModal.value = true }

const handleEdit = (student) => {
  selectedStudent.value = student
  showEditModal.value = true
}

const openDeleteModal = (student) => {
  deletingStudent.value = student
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  isDeleteModalOpen.value = false
  deletingStudent.value = null
}

const handleCreateStudent = async () => {
  try {
    isLoading.value = true
    showCreateModal.value = false
    await fetchStudents()
    showToast('បង្កើតសិស្សបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចបង្កើតសិស្សបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleUpdateStudent = async () => {
  try {
    isLoading.value = true
    showEditModal.value = false
    await fetchStudents()
    showToast('កែប្រែសិស្សបានជោគជ័យ!', 'success')
  } catch (err) {
    showToast(err.response?.data?.message || 'មិនអាចកែប្រែសិស្សបានទេ!', 'error')
  } finally {
    isLoading.value = false
  }
}

const confirmDeleteStudent = async () => {
  if (!deletingStudent.value) return
  try {
    isDeleting.value = true
    await studentService.deleteStudent(deletingStudent.value.id)
    isDeleteModalOpen.value = false
    deletingStudent.value = null
    await fetchStudents()
    showToast('បានលុបទិន្នន័យសិស្សដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast('មិនអាចលុបបានទេ: ' + (error.response?.data?.message || 'Error'), 'error')
  } finally {
    isDeleting.value = false
  }
}

// ----- Styles for Excel -----
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

// ----- Export: Excel -----
const exportToExcel = () => {
  if (filteredStudents.value.length === 0) return

  const colCount = 25

  // Sort by from_class (ascending)
  const sortedByFromClass = [...filteredStudents.value].sort((a, b) => {
    const aClass = String(a.from_class || '').toLowerCase().trim()
    const bClass = String(b.from_class || '').toLowerCase().trim()
    return aClass.localeCompare(bClass)
  })

  const headerRows = [
    ['ព្រះរាជាណាចក្រកម្ពុជា'],
    ['ជាតិ សាសនា ព្រះមហាក្សត្រ'],
    [],
    [ministryLine1.value],
    [ministryLine2.value],
    ['បញ្ជីព័ត៌មានសិស្សានុសិស្សទាំងអស់'],
    [],
  ]

  const headerRow1 = [
    'ល.រ', 'អត្តលេខ', 'ឈ្មោះ (ខ្មែរ)', 'ឈ្មោះឡាតាំង', 'ភេទ', 'ថ្ងៃខែឆ្នាំកំណើត', 'លេខទូរស័ព្ទ',
    'ទីកន្លែងកំណើត', '', '', '',
    'អាសយដ្ឋានបច្ចុប្បន្ន', '', '', '',
    'មកពីសាលា', 'មកពីថ្នាក់ទី',
    'ព័ត៌មានឪពុក', '', '',
    'ព័ត៌មានម្តាយ', '', '',
    'ថ្ងៃចូលរៀន', 'ស្ថានភាព'
  ]
  const headerRow2 = [
    '', '', '', '', '', '', '',
    'ភូមិ', 'ឃុំ/សង្កាត់', 'ស្រុក/ខណ្ឌ', 'ខេត្ត/ក្រុង',
    'ភូមិ', 'ឃុំ/សង្កាត់', 'ស្រុក/ខណ្ឌ', 'ខេត្ត/ក្រុង',
    '', '',
    'ឈ្មោះ', 'មុខរបរ', 'ទូរស័ព្ទ',
    'ឈ្មោះ', 'មុខរបរ', 'ទូរស័ព្ទ',
    '', ''
  ]

  const tableRows = sortedByFromClass.map((s, index) => {
    return [
      toKhmerNumber(index + 1),
      s.student_id_card || '',
      s.name_kh || '',
      s.name_en || '',
      getGenderKh(s.gender),
      formatDate(s.date_of_birth),
      s.phone || '',
      s.pob_village || '',
      s.pob_commune || '',
      s.pob_district || '',
      s.pob_province || '',
      s.village || '',
      s.commune || '',
      s.district || '',
      s.province || '',
      s.pri_school || '',
      s.from_class || '',
      s.guardian_dad_name || '',
      s.guardian_dad_job || '',
      s.guardian_dad_phone || '',
      s.guardian_mom_name || '',
      s.guardian_mom_job || '',
      s.guardian_mom_phone || '',
      formatDate(s.enrollment_date),
      s.status == 1 ? 'កំពុងរៀន' : 'ឈប់រៀន/ព្យួរ'
    ]
  })

  let cursor = headerRows.length + 2 + tableRows.length
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
    `សរុបចំនួនសិស្ស៖ ${toKhmerNumber(sortedByFromClass.length)} នាក់    ស្រី៖ ${toKhmerNumber(sortedByFromClass.filter(s => ['female', 'f', 'ស្រី'].includes(String(s.gender).toLowerCase())).length)} នាក់    កំពុងរៀន៖ ${toKhmerNumber(sortedByFromClass.filter(s => s.status == 1).length)} នាក់    ឈប់រៀន៖ ${toKhmerNumber(sortedByFromClass.filter(s => s.status != 1).length)} នាក់`
  ])
  styleQueue.push({ r: summaryRowIdx, c: 0, style: totalStyle })
  mergeQueue.push({ r: summaryRowIdx, c1: 0, c2: colCount - 1 })

  pushRow([])

  const dateRow1Idx = pushRow(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ថ្ងៃ............. ខែ............. ឆ្នាំ............. ព.ស...............'])
  const dateRow2Idx = pushRow(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'នរា ថ្ងៃទី........ ខែ........ ឆ្នាំ២០........'])
  const teacherTitleIdx = pushRow(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'នាយកសាលា'])

  mergeQueue.push({ r: dateRow1Idx, c1: 18, c2: colCount - 1 })
  mergeQueue.push({ r: dateRow2Idx, c1: 18, c2: colCount - 1 })
  mergeQueue.push({ r: teacherTitleIdx, c1: 18, c2: colCount - 1 })
  styleQueue.push({ r: dateRow1Idx, c: 18, style: dateLineStyle })
  styleQueue.push({ r: dateRow2Idx, c: 18, style: dateLineStyle })
  styleQueue.push({ r: teacherTitleIdx, c: 18, style: dateMoulRightStyle })

  const aoa = [...headerRows, headerRow1, headerRow2, ...tableRows, ...extraRows]
  const ws = XLSX.utils.aoa_to_sheet(aoa)

  const headerRowIdx = headerRows.length
  ws['!merges'] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: colCount - 1 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: colCount - 1 } },
    { s: { r: 3, c: 0 }, e: { r: 3, c: colCount - 1 } },
    { s: { r: 4, c: 0 }, e: { r: 4, c: colCount - 1 } },
    { s: { r: 5, c: 0 }, e: { r: 5, c: colCount - 1 } },
    ...([0, 1, 2, 3, 4, 5, 6, 15, 16, 23, 24].map(c => ({ s: { r: headerRowIdx, c }, e: { r: headerRowIdx + 1, c } }))),
    { s: { r: headerRowIdx, c: 7 }, e: { r: headerRowIdx, c: 10 } },
    { s: { r: headerRowIdx, c: 11 }, e: { r: headerRowIdx, c: 14 } },
    { s: { r: headerRowIdx, c: 17 }, e: { r: headerRowIdx, c: 19 } },
    { s: { r: headerRowIdx, c: 20 }, e: { r: headerRowIdx, c: 22 } },
    ...(mergeQueue.map(m => ({ s: { r: m.r, c: m.c1 }, e: { r: m.r, c: m.c2 } }))),
  ]

  styleCell(ws, 0, 0, kingdomStyle)
  styleCell(ws, 1, 0, mottoStyle)
  styleCell(ws, 3, 0, officeStyle)
  styleCell(ws, 4, 0, officeStyle)
  styleCell(ws, 5, 0, titleStyle)

  for (let r = headerRowIdx; r <= headerRowIdx + 1; r++) {
    for (let c = 0; c < colCount; c++) {
      styleCell(ws, r, c, tableHeaderStyle)
    }
  }

  const tableStartRowIdx = headerRowIdx + 2
  const tableEndRowIdx = tableStartRowIdx + tableRows.length - 1
  for (let r = tableStartRowIdx; r <= tableEndRowIdx; r++) {
    for (let c = 0; c < colCount; c++) {
      const isLeftAlign = [2, 3, 7, 8, 9, 10, 11, 12, 13, 14, 15, 17, 18, 20, 21].includes(c)
      styleCell(ws, r, c, isLeftAlign ? tableCellLeftStyle : tableCellStyle)
    }
  }

  styleQueue.forEach(({ r, c, style }) => styleCell(ws, r, c, style))

  ws['!cols'] = [
    { wch: 6 }, { wch: 12 }, { wch: 22 }, { wch: 22 }, { wch: 8 }, { wch: 14 }, { wch: 14 },
    { wch: 16 }, { wch: 16 }, { wch: 16 }, { wch: 16 },
    { wch: 16 }, { wch: 16 }, { wch: 16 }, { wch: 16 },
    { wch: 18 }, { wch: 12 }, { wch: 20 }, { wch: 16 }, { wch: 14 },
    { wch: 20 }, { wch: 16 }, { wch: 14 }, { wch: 14 }, { wch: 14 }
  ]

  ws['!rows'] = [{ hpt: 26 }]

  ws['!pageSetup'] = { orientation: 'landscape', fitToWidth: 1, fitToHeight: 0 }
  ws['!printOptions'] = { horizontalCentered: true }
  ws['!margins'] = { left: 0.5, right: 0.5, top: 0.6, bottom: 0.6, header: 0.3, footer: 0.3 }

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'សិស្សានុសិស្ស')
  XLSX.writeFile(wb, `${sanitizeFileName('បញ្ជីព័ត៌មានសិស្សានុសិស្ស')}.xlsx`)
}

// ----- Export: PDF -----
const exportToPDF = async () => {
  if (filteredStudents.value.length === 0) return
  if (!printArea.value) return

  // Sort by from_class (ascending)
  const sortedByFromClass = [...filteredStudents.value].sort((a, b) => {
    const aClass = String(a.from_class || '').toLowerCase().trim()
    const bClass = String(b.from_class || '').toLowerCase().trim()
    return aClass.localeCompare(bClass)
  })

  const opt = {
    margin: [10, 10, 10, 10],
    filename: `${sanitizeFileName('បញ្ជីព័ត៌មានសិស្សានុសិស្ស')}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
    jsPDF: { unit: 'mm', format: 'a3', orientation: 'landscape' }
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

onMounted(fetchStudents)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Moul&display=swap');

/* --- Custom Scrollbar (same as ManageTeacher) --- */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8fafc;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* --- Hidden PDF Export Template Styles (For A3 Landscape) --- */
.pdf-export-area {
  display: none;
  width: 390mm;
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
  font-size: 15px;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.kingdom-motto {
  font-family: 'Moul', serif;
  font-weight: 500;
  font-size: 15px;
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
  font-size: 14px;
  margin: 10px 0 14px;
}

.doc-office p {
  font-family: 'Moul', serif;
  font-size: 14px;
  line-height: 1.8;
  margin: 2px 0;
}

.doc-title {
  font-family: 'Moul', Arial, sans-serif;
  font-weight: 500;
  font-size: 16px;
  text-align: center;
  margin: 8px 0 14px;
}

.pdf-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11.5px;
  text-align: center;
}

.pdf-table th,
.pdf-table td {
  border: 1px solid #000000;
  padding: 5px 6px;
  vertical-align: middle;
}

.pdf-table th {
  font-weight: 700;
  text-align: center;
  background: #ffffff;
}

.doc-summary {
  margin-top: 14px;
  font-size: 13.5px;
  font-weight: bold;
}

.doc-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
  font-size: 13.5px;
  line-height: 1.8;
}

.signature-right {
  width: 30%;
  text-align: center;
}

.moul {
  font-family: 'Moul', serif;
  font-size: 13.5px;
  margin-top: 4px;
}

/* --- Modal & Toast Transitions (same as ManageTeacher) --- */
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