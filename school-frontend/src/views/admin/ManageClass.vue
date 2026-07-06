<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50 text-slate-700">

    <!-- ===== LIST VIEW ===== -->
    <div v-if="!activeDetailClass">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងថ្នាក់រៀន</h1>
          <p class="text-sm text-slate-500 mt-1">រៀបចំបញ្ជីថ្នាក់ បែងចែកគ្រូបង្រៀន និងតាមដានចំនួនសិស្ស</p>
        </div>
        <button @click="openCreateModal"
          class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          បង្កើតថ្នាក់ថ្មី
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div v-for="item in summaryStats" :key="item.label"
          class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 transition-all hover:border-indigo-300">
          <div :class="['p-3 rounded-xl', item.iconBg]">
            <component :is="item.icon" class="w-6 h-6" :class="item.iconColor" />
          </div>
          <div>
            <p class="text-[14px] text-slate-500 font-medium uppercase tracking-wider">{{ item.label }}</p>
            <p class="text-lg font-bold text-slate-800">{{ item.value }}</p>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row gap-4 justify-between bg-slate-50/30">
          <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <div class="relative max-w-sm w-full">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input v-model="searchQuery" type="text" placeholder="ស្វែងរកតាមឈ្មោះថ្នាក់ ឬគ្រូ..."
                class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
            </div>
            <div class="relative min-w-[180px]">
              <select v-model="filters.academic_year_id" @change="fetchClasses"
                class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer">
                <option value="">គ្រប់ឆ្នាំសិក្សាទាំងអស់</option>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.year_name || year.name }}
                </option>
              </select>
              <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <div v-if="loading" class="p-10 text-center text-slate-500 text-sm italic">កំពុងទាញទិន្នន័យ...</div>
          <table v-else class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50/50">
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ឈ្មោះថ្នាក់</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">គ្រូទទួលបន្ទុក</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">សិស្សសរុប</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ស្ថានភាព</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="cls in filteredClasses" :key="cls.id" @click="viewDetail(cls)"
                class="hover:bg-slate-50/80 transition-colors group cursor-pointer">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100">
                      <Layout class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-700 leading-tight">ថ្នាក់ទី {{ cls.grade_level }}{{ cls.name }}</p>
                      <p class="text-[12px] text-slate-600 mt-0.5 uppercase">{{ cls.year?.year_name || cls.year?.name || 'N/A' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 text-sm text-slate-600">
                    <UserCheck class="w-4 h-4 text-slate-400" />
                    {{ cls.teacher ? cls.teacher.name_kh : 'មិនទាន់មាន' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-1sm">
                    <span class="font-bold text-slate-700">{{ cls.students_count || cls.student_count || 0 }}</span>
                    <span class="text-slate-600 text-xs ml-1">នាក់</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="[
                    'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                    cls.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
                  ]">
                    {{ cls.is_active ? 'សកម្ម' : 'មិនសកម្ម' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right" @click.stop>
                  <div class="flex items-center justify-end gap-2">
                    <button @click="viewDetail(cls)"
                      class="p-2 text-white bg-slate-600 hover:bg-slate-700 rounded-lg transition-all shadow-sm active:scale-90" title="View Detail">
                      <ChevronRight class="w-4 h-4" />
                    </button>
                    <button @click="editClass(cls)"
                      class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all shadow-sm active:scale-90" title="Edit">
                      <Edit3 class="w-4 h-4" />
                    </button>
                    <button @click="openDeleteModal(cls)"
                      class="p-2 text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-all shadow-sm active:scale-90" title="Delete">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredClasses.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm italic">មិនឃើញមានថ្នាក់រៀនទេ</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ===== DETAIL VIEW ===== -->
    <div v-else class="animate-in fade-in duration-200">
      <button @click="activeDetailClass = null"
        class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-800 mb-4 transition-colors">
        ← ត្រឡប់ទៅបញ្ជីថ្នាក់វិញ
      </button>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
          <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-md uppercase">ព័ត៌មានលម្អិតថ្នាក់</span>
          <h2 class="text-2xl font-bold text-slate-800 mt-2">ថ្នាក់ទី៖ {{ activeDetailClass.grade_level }}{{ activeDetailClass.name }}</h2>
          <p class="text-sm text-slate-500 mt-1">
            គ្រូបន្ទុក៖ <span class="font-bold text-slate-700">{{ activeDetailClass.teacher?.name_kh || 'មិនទាន់មាន' }}</span> |
            ឆ្នាំសិក្សា៖ <span class="font-bold text-slate-700">{{ activeDetailClass.year?.year_name || activeDetailClass.year?.name || 'N/A' }}</span>
          </p>
        </div>
        <button @click="isAddStudentModalOpen = true"
          class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-100 transition-all active:scale-95">
          <Plus class="w-4 h-4" /> បញ្ចូលសិស្សទៅក្នុងថ្នាក់
        </button>
      </div>

      <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="font-bold text-sm text-slate-600">👥 សិស្សសរុប៖ {{ classStudents.length }} នាក់</div>
          <div class="flex items-center gap-2">
            <button @click="downloadExcel"
              class="bg-white border border-green-600 text-green-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
              ទាញយក Excel
            </button>
            <button @click="downloadPDF"
              class="bg-white border border-red-600 text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
              ទាញយក​ PDF
            </button>
          </div>
        </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        

        <div class="overflow-x-auto w-full">
          <table class="w-full text-left border-collapse min-w-[1200px]">
            <thead>
              <tr class="bg-slate-50 text-[14px] font-bold text-slate-600 uppercase tracking-wider">
                <th class="px-4 py-3 w-12 sticky left-0 bg-slate-50 z-10">ល.រ</th>
                <th class="px-4 py-3 w-40 sticky left-12 bg-slate-50 z-10">ឈ្មោះសិស្ស</th>
                <th class="px-4 py-3">អត្តលេខ</th>
                <th class="px-4 py-3">ភេទ</th>
                <th class="px-4 py-3">ថ្ងៃកំណើត</th>
                <th class="px-4 py-3">លេខទូរស័ព្ទ</th>
                <th class="px-4 py-3">ឪពុក (ឈ្មោះ/លេខ)</th>
                <th class="px-4 py-3">ម្តាយ (ឈ្មោះ/លេខ)</th>
                <th class="px-4 py-3 text-center sticky right-0 bg-slate-50 z-20">សកម្មភាព</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
              <tr v-for="(std, index) in classStudents" :key="std.id" class="hover:bg-indigo-50/30 transition-colors">
                <td class="px-4 py-4 font-bold text-slate-700 bg-white sticky left-0 z-10">{{ index + 1 }}</td>
                <td class="px-4 py-4 bg-white sticky left-10 z-10 border-r border-slate-100">
                  <div class="text-[14px] font-bold text-slate-800">{{ std.name_kh }}</div>
                  <div class="text-[14px] text-slate-600 uppercase">{{ std.name_en }}</div>
                </td>
                <td class="px-4 py-4 font-mono text-[14px] uppercase">{{ std.student_id_card }}</td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">{{ std.gender === 'male' ? 'ប្រុស' : 'ស្រី' }}</td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">{{ std.date_of_birth ? new Date(std.date_of_birth).toLocaleDateString('km-KH') : 'N/A' }}</td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">{{ std.phone }}</td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">
                  <div>{{ std.guardian_dad_name }}</div>
                  <div class="text-[13px] text-slate-700">{{ std.guardian_dad_phone }}</div>
                </td>
                <td class="px-4 py-4 text-[14px] text-slate-700">
                  <div>{{ std.guardian_mom_name }}</div>
                  <div class="text-[13px] text-slate-700">{{ std.guardian_mom_phone }}</div>
                </td>
                <td class="px-4 py-4 text-center sticky right-0 bg-white z-10 border-l border-slate-100">
                  <button @click="openRemoveStudentModal(std)"
                    class="text-[12px] font-bold text-rose-500 hover:text-white hover:bg-rose-500 px-3 py-1.5 rounded-lg border border-rose-200 transition-all whitespace-nowrap">
                    ដកចេញ
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

<div v-if="isAddStudentModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background: rgba(15, 15, 20, 0.5);" 
         @click.self="isAddStudentModalOpen = false">
      
      <Transition name="modal-scale">
        <div v-if="isAddStudentModalOpen"
             class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg p-8 shadow-2xl font-[Battambang]">
          
          <h3 class="text-xl font-bold text-slate-800 mb-2">ស្វែងរកសិស្សបញ្ចូលទៅក្នុងថ្នាក់</h3>
          <p class="text-sm text-slate-500 mb-6">ប្អូនអាចវាយបញ្ចូលឈ្មោះ ឬលេខកូដសិស្ស ដើម្បីស្វែងរកសិស្ស</p>

          <div class="relative mb-6">
            <Search class="absolute left-3 top-3.5 w-5 h-5 text-slate-400" />
            <input v-model="studentSearchQuery" 
                   @input="searchStudentsToAssign" 
                   type="text"
                   placeholder="វាយឈ្មោះខ្មែរ ឬ កូដសិស្ស..."
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all" />
          </div>

          <div class="max-h-60 overflow-y-auto border border-slate-100 rounded-xl bg-slate-50/50 mb-6 divide-y divide-slate-100">
            <div v-if="searchingStudents" class="p-4 text-center text-sm text-slate-400 italic">កំពុងស្វែងរក...</div>
            
            <div v-else v-for="student in foundStudents" :key="student.id"
                 class="p-4 flex items-center justify-between hover:bg-white transition-colors">
              <div>
                <p class="text-sm font-bold text-slate-700">{{ student.name_kh || student.name }}</p>
                <p class="text-xs font-mono text-indigo-500 mt-0.5">{{ student.student_id_card || 'គ្មានកូដ' }}</p>
              </div>
              <button @click="assignStudentToClass(student.id)"
                      class="px-4 py-2 text-sm font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all active:scale-95">
                + បញ្ចូល
              </button>
            </div>

            <div v-if="foundStudents.length === 0 && studentSearchQuery.trim() !== ''"
                 class="p-4 text-center text-sm text-slate-400">
              រកមិនឃើញសិស្សម្នាក់នេះឡើយ
            </div>
          </div>

          <button @click="isAddStudentModalOpen = false"
                  class="w-full py-3 rounded-xl text-sm font-bold text-blue-600 bg-slate-100 hover:bg-slate-200 transition-all">
            បិទផ្ទាំង
          </button>
        </div>
      </Transition>
    </div>
    
    
      
   

    <!-- ===== MODAL: Create Class ===== -->
    <CreateClassModel :is-open="isCreateModalOpen" @close="closeCreateModal" @refresh="handleClassCreated" />

    <!-- ===== MODAL: Detail Student ===== -->
    <DetailStudent :is-open="showModal" :student="selectedStudent" @close="showModal = false" />

    <!-- ===== MODAL: Edit Class ===== -->
    <EditeClass v-model="isEditModalOpen" :classData="selectedClass" @refresh="fetchClasses" />

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

    <!-- Delete Class Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isDeleteModalOpen"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(15, 15, 20, 0.5);"
          @click.self="closeDeleteModal">
          <Transition name="modal-scale">
            <div v-if="isDeleteModalOpen"
              class="bg-white rounded-2xl border border-slate-200 w-full max-w-sm p-8 text-center font-[Battambang]">
              <div class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center mx-auto mb-5">
                <Trash2 class="w-7 h-7 text-rose-500 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]" />
              </div>
              <p class="text-[17px] font-bold text-slate-800 mb-2">លុបថ្នាក់រៀន</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់លុបថ្នាក់
                <span class="font-bold text-blue-700">ទី{{ deletingClass?.grade_level }}{{ deletingClass?.name }}</span>
                មែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>
              <div class="flex gap-3">
                <button @click="closeDeleteModal" :disabled="isDeleting"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmDeleteClass" :disabled="isDeleting"
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

    <!-- Remove Student from Class Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isRemoveStudentModalOpen"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          style="background: rgba(15, 15, 20, 0.5);"
          @click.self="closeRemoveStudentModal">
          <Transition name="modal-scale">
            <div v-if="isRemoveStudentModalOpen"
              class="bg-white rounded-2xl border border-slate-200 w-full max-w-sm p-8 text-center font-[Battambang]">
              <div class="w-16 h-16 rounded-full bg-red-200 flex items-center justify-center mx-auto mb-5">
                <Trash2 class="w-7 h-7 text-rose-500 drop-shadow-[0_0_8px_rgba(244,63,94,0.6)]" />
              </div>
              <p class="text-[17px] font-bold text-slate-800 mb-2">ដកសិស្សចេញពីថ្នាក់</p>
              <p class="text-sm text-slate-600 leading-relaxed mb-1">
                តើអ្នកពិតជាចង់ដក
                <span class="font-bold text-blue-700">{{ removingStudent?.name_kh }}</span>
                ចេញពីថ្នាក់នេះមែនទេ?
              </p>
              <p class="text-sm text-slate-400 mb-7">សកម្មភាពនេះមិនអាចដកវិញបានទេ។</p>
              <div class="flex gap-3">
                <button @click="closeRemoveStudentModal" :disabled="isRemoving"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all active:scale-95 disabled:opacity-50">
                  បោះបង់
                </button>
                <button @click="confirmRemoveStudent" :disabled="isRemoving"
                  class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white bg-rose-500 hover:bg-rose-600 flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-60">
                  <Loader2 v-if="isRemoving" class="w-4 h-4 animate-spin" />
                  <Trash2 v-else class="w-4 h-4" />
                  {{ isRemoving ? 'កំពុងដក...' : 'លុបចោល' }}
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
import { ref, reactive, computed, onMounted } from 'vue'
import {
  Plus, Search, Edit3, Trash2, UserCheck, Users, BookOpen,
  Layout, ChevronRight, ChevronDown,
  CheckCircle2, XCircle, X, Loader2
} from 'lucide-vue-next'
import api from '../../services/authService'
import CreateClassModel from '../admin/create/CreateClassModel.vue'
import EditeClass from '../admin/edite/EditeClass.vue'
import DetailStudent from '../admin/detail/DetailStudent.vue'

import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import 'jspdf-autotable'

// --- State ---
const searchQuery = ref('')
const loading = ref(false)
const academicYears = ref([])
const classes = ref([])

const isCreateModalOpen = ref(false)
const isEditModalOpen = ref(false)
const selectedClass = ref(null)

const showModal = ref(false)
const selectedStudent = ref(null)

const activeDetailClass = ref(null)
const classStudents = ref([])
const isAddStudentModalOpen = ref(false)
const studentSearchQuery = ref('')
const foundStudents = ref([])
const searchingStudents = ref(false)

const filters = reactive({ academic_year_id: '' })

// Delete Class Modal
const isDeleteModalOpen = ref(false)
const isDeleting = ref(false)
const deletingClass = ref(null)

// Remove Student Modal
const isRemoveStudentModalOpen = ref(false)
const isRemoving = ref(false)
const removingStudent = ref(null)

// Toast
const toast = ref({ show: false, message: '', type: 'success' })
let toastTimer = null

// --- Toast ---
const showToast = (message, type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { show: true, message, type }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// --- Computed ---
const summaryStats = computed(() => [
  { label: 'ថ្នាក់រៀនសរុប', value: classes.value.length, icon: Layout, iconBg: 'bg-indigo-50', iconColor: 'text-indigo-600' },
  { label: 'សិស្សសរុប', value: classes.value.reduce((acc, curr) => acc + (parseInt(curr.students_count || curr.student_count) || 0), 0), icon: Users, iconBg: 'bg-emerald-50', iconColor: 'text-emerald-600' },
  { label: 'ថ្នាក់សកម្ម', value: classes.value.filter(c => c.is_active).length, icon: BookOpen, iconBg: 'bg-amber-50', iconColor: 'text-amber-600' },
])

const filteredClasses = computed(() => {
  if (!classes.value) return []
  return classes.value.filter(cls => {
    const className = (cls.name || '').toLowerCase()
    const teacherName = (cls.teacher?.name_kh || '').toLowerCase()
    const yearName = (cls.year?.year_name || cls.year?.name || '').toLowerCase()
    const searchTerm = (searchQuery.value || '').toLowerCase()
    return className.includes(searchTerm) || teacherName.includes(searchTerm) || yearName.includes(searchTerm)
  })
})

// --- Fetch ---
const fetchInitialData = async () => {
  try {
    const response = await api.get('/years')
    academicYears.value = response.data
  } catch (error) {
    console.error('Error loading years:', error)
  }
}

const fetchClasses = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.academic_year_id) params.academic_year_id = filters.academic_year_id
    const response = await api.get('/classes', { params })
    classes.value = response.data
    if (activeDetailClass.value) {
      const updatedCls = classes.value.find(c => c.id === activeDetailClass.value.id)
      if (updatedCls) activeDetailClass.value = updatedCls
    }
  } catch (error) {
    console.error('Error loading classes:', error)
  } finally {
    loading.value = false
  }
}

const fetchStudentsByClass = async (classId) => {
  try {
    const response = await api.get(`/classes/${classId}/students`)
    if (response.data && Array.isArray(response.data)) {
      classStudents.value = response.data
    } else if (response.data && Array.isArray(response.data.data)) {
      classStudents.value = response.data.data
    } else {
      classStudents.value = []
    }
  } catch (error) {
    console.error('Error fetching class students:', error)
    classStudents.value = []
  }
}

// --- Detail View ---
const viewDetail = async (cls) => {
  activeDetailClass.value = cls
  fetchStudentsByClass(cls.id)
}

// --- Search Students ---
const searchStudentsToAssign = async () => {
  if (!studentSearchQuery.value.trim()) {
    foundStudents.value = []
    return
  }
  searchingStudents.value = true
  try {
    const response = await api.get('/students', { params: { search: studentSearchQuery.value } })
    const allStudents = Array.isArray(response.data) ? response.data : (response.data?.data || [])
    foundStudents.value = allStudents.filter(std => !classStudents.value.some(s => s.id === std.id))
  } catch (error) {
    console.error('Error searching students:', error)
  } finally {
    searchingStudents.value = false
  }
}

// --- Assign Student ---
const assignStudentToClass = async (studentId) => {
  try {
    await api.post(`/classes/${activeDetailClass.value.id}/assign-student`, { student_id: studentId })
    studentSearchQuery.value = ''
    foundStudents.value = []
    isAddStudentModalOpen.value = false
    await fetchStudentsByClass(activeDetailClass.value.id)
    await fetchClasses()
    showToast('បញ្ចូលសិស្សជោគជ័យ!', 'success')
  } catch (error) {
    console.error('Error assigning student:', error)
    showToast('មានបញ្ហាក្នុងការបញ្ចូលសិស្សទៅក្នុងថ្នាក់នេះ!', 'error')
  }
}

// --- Remove Student Modal ---
const openRemoveStudentModal = (student) => {
  removingStudent.value = student
  isRemoveStudentModalOpen.value = true
}

const closeRemoveStudentModal = () => {
  if (isRemoving.value) return
  isRemoveStudentModalOpen.value = false
  removingStudent.value = null
}

const confirmRemoveStudent = async () => {
  if (!removingStudent.value) return
  const classId = activeDetailClass.value.id
  try {
    isRemoving.value = true
    await api.delete(`/classes/${classId}/students/${removingStudent.value.id}`)
    isRemoveStudentModalOpen.value = false
    removingStudent.value = null
    await fetchStudentsByClass(classId)
    showToast('បានដកសិស្សចេញពីថ្នាក់ដោយជោគជ័យ!', 'success')
  } catch (error) {
    console.error('Error removing student:', error)
    showToast('មិនអាចដកសិស្សចេញបាន!', 'error')
  } finally {
    isRemoving.value = false
  }
}

// --- Delete Class Modal ---
const openDeleteModal = (cls) => {
  deletingClass.value = cls
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  isDeleteModalOpen.value = false
  deletingClass.value = null
}

const confirmDeleteClass = async () => {
  if (!deletingClass.value) return
  try {
    isDeleting.value = true
    await api.delete(`/classes/${deletingClass.value.id}`)
    isDeleteModalOpen.value = false
    deletingClass.value = null
    await fetchClasses()
    showToast('បានលុបថ្នាក់រៀនដោយជោគជ័យ!', 'success')
  } catch (error) {
    showToast(error.response?.data?.message || 'មិនអាចលុបបានទេ!', 'error')
  } finally {
    isDeleting.value = false
  }
}

// --- Create / Edit ---
const openCreateModal = () => { isCreateModalOpen.value = true }
const closeCreateModal = () => { isCreateModalOpen.value = false }
const handleClassCreated = () => { fetchClasses(); closeCreateModal() }

const editClass = (cls) => {
  selectedClass.value = { ...cls }
  isEditModalOpen.value = true
}

// --- Export ---
const downloadExcel = () => {
  if (classStudents.value.length === 0) return
  const dataRows = classStudents.value.map((std, idx) => ({
    'ល.រ': idx + 1,
    'លេខកូដសិស្ស': std.student_id_card || 'N/A',
    'ឈ្មោះខ្មែរ': std.name_kh || '',
    'ឈ្មោះឡាតាំង': std.name_en || '',
    'ភេទ': std.gender === 'male' ? 'ប្រុស' : std.gender === 'female' ? 'ស្រី' : 'N/A',
  }))
  const worksheet = XLSX.utils.json_to_sheet(dataRows)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'បញ្ជីឈ្មោះសិស្ស')
  const className = `ថ្នាក់ទី_${activeDetailClass.value.grade_level}${activeDetailClass.value.name}`
  XLSX.writeFile(workbook, `បញ្ជីឈ្មោះសិស្ស_${className}.xlsx`)
}

const downloadPDF = () => {
  if (classStudents.value.length === 0) return
  const doc = new jsPDF()
  const className = `ថ្នាក់ទី៖ ${activeDetailClass.value.grade_level}${activeDetailClass.value.name}`
  const teacherName = `គ្រូទទួលបន្ទុក៖ ${activeDetailClass.value.teacher?.name_kh || 'មិនទាន់មាន'}`
  const academicYear = `ឆ្នាំសិក្សា៖ ${activeDetailClass.value.year?.year_name || activeDetailClass.value.year?.name || 'N/A'}`
  doc.text(`បញ្ជីឈ្មោះសិស្សសរុប - ${className}`, 14, 15)
  doc.text(`${teacherName} | ${academicYear}`, 14, 22)
  const tableHeaders = [['ល.រ', 'លេខកូដសិស្ស', 'ឈ្មោះខ្មែរ', 'ឈ្មោះឡាតាំង', 'ភេទ']]
  const tableRows = classStudents.value.map((std, idx) => [
    idx + 1,
    std.student_id_card || 'N/A',
    std.name_kh || '',
    std.name_en || '',
    std.gender === 'male' ? 'ប្រុស' : std.gender === 'female' ? 'ស្រី' : 'N/A'
  ])
  doc.autoTable({
    startY: 28,
    head: tableHeaders,
    body: tableRows,
    styles: { font: 'Courier', fontSize: 10 },
    headStyles: { fillColor: [79, 70, 229] }
  })
  doc.save(`បញ្ជីឈ្មោះសិស្ស_${activeDetailClass.value.grade_level}${activeDetailClass.value.name}.pdf`)
}

onMounted(() => {
  fetchInitialData()
  fetchClasses()
})
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