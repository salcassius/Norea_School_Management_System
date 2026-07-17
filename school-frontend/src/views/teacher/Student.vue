<template>
  <div class="font-[Battambang] p-1 sm:p-2 md:p-2 min-h-screen bg-slate-50/50">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 md:mb-8">
      <div>
        <h1 class="text-lg sm:text-[22px] font-bold text-slate-800">គ្រប់គ្រងសិស្សានុសិស្ស</h1>
        <p class="text-sm text-slate-500 mt-1">បញ្ជីឈ្មោះសិស្សក្នុងថ្នាក់ដែលអ្នកទទួលខុសត្រូវ</p>
      </div>

      <div class="relative max-w-sm w-full md:w-64">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="ស្វែងរកឈ្មោះសិស្ស..."
          class="w-full bg-white border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm outline-none focus:border-indigo-500 transition-all"
        />
      </div>
    </div>

    <!-- Mobile hint: swipe to see more columns -->
    <p class="md:hidden text-xs text-slate-400 mb-2">👉 អូសទៅឆ្វេង-ស្តាំ ដើម្បីមើលព័ត៌មានបន្ថែម</p>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div v-if="loading" class="text-center py-10 text-slate-400">
        កំពុងផ្ទុកទិន្នន័យ...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[2000px]">
          <thead>
            <tr class="bg-slate-100 text-[13px] sm:text-[14px] font-semibold text-slate-600 uppercase tracking-wider">
              <!-- Sticky Left -->
              <th class="px-5 py-4 w-14">ល.រ</th>
              <th class="px-5 py-4 w-40">ឈ្មោះសិស្ស</th>

              <!-- Normal Columns -->
              <th class="px-5 py-4">អត្តលេខ</th>
              <th class="px-5 py-4">ភេទ</th>
              <th class="px-5 py-4">ថ្ងៃកំណើត</th>
              <th class="px-5 py-4">មកពីបឋមសិក្សា</th>
              <th class="px-5 py-4">ទីកន្លែងកំណើត</th>
              <th class="px-6 py-4">អាសយដ្ឋានបច្ចុប្បន្ន</th>
              <th class="px-5 py-4">លេខទូរស័ព្ទ</th>

              <!-- ឪពុក -->
              <th class="px-4 py-4">ឪពុក</th>
              <th class="px-4 py-4">មុខរបរ</th>
              <th class="px-4 py-4">ទូរស័ព្ទ</th>

              <!-- ម្តាយ -->
              <th class="px-4 py-4">ម្តាយ</th>
              <th class="px-4 py-4">មុខរបរ</th>
              <th class="px-4 py-4">ទូរស័ព្ទ</th>

              <!-- Sticky Right -->
              <!-- <th class="px-5 py-4 text-center sticky right-0 bg-slate-100 z-30">សកម្មភាព</th> -->
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100 text-sm">
            <tr v-for="(std, index) in filteredStudents" :key="std.id" class="hover:bg-indigo-50/30">
              <!-- ល.រ -->
              <td class="px-5 py-4 font-bold text-slate-700 bg-white z-10 ">{{ index + 1 }}</td>

              <!-- ឈ្មោះសិស្ស -->
              <td class="px-2 py-4 bg-white z-10">
                <div class="font-bold text-slate-800">{{ std.name_kh }}</div>
                <div class="text-xs text-slate-500 uppercase">{{ std.name_en }}</div>
              </td>

              <td class="px-5 py-4 font-mono font-bold text-indigo-600">{{ std.student_id_card }}</td>
              <td class="px-5 py-4">{{ std.gender === 'male' ? 'ប្រុស' : 'ស្រី' }}</td>
              <td class="px-5 py-4">
                {{ std.date_of_birth ? new Date(std.date_of_birth).toLocaleDateString('km-KH') : '-' }}
              </td>
              <td class="px-5 py-4">{{ std.pri_school || '-' }}</td>

              <!-- ទីកន្លែងកំណើត -->
              <td class="px-5 py-4 text-slate-600">
                {{ [std.pob_village, std.pob_commune, std.pob_district, std.pob_province].filter(Boolean).join(', ') || '-' }}
              </td>

              <!-- អាសយដ្ឋានបច្ចុប្បន្ន -->
              <td class="px-6 py-4 text-slate-600">
                {{ [std.village, std.commune, std.district, std.province].filter(Boolean).join(', ') || '-' }}
              </td>

              <td class="px-5 py-4">{{ std.phone || '-' }}</td>

              <!-- ឪពុក -->
              <td class="px-4 py-4">{{ std.guardian_dad_name || '-' }}</td>
              <td class="px-4 py-4">{{ std.guardian_dad_job || '-' }}</td>
              <td class="px-4 py-4">{{ std.guardian_dad_phone || '-' }}</td>

              <!-- ម្តាយ -->
              <td class="px-4 py-4">{{ std.guardian_mom_name || '-' }}</td>
              <td class="px-4 py-4">{{ std.guardian_mom_job || '-' }}</td>
              <td class="px-4 py-4">{{ std.guardian_mom_phone || '-' }}</td>

              <!-- Action -->
              <!-- <td class="px-5 py-4 text-center sticky right-0 bg-white z-10">
                <button
                  @click="openEditModal(std)"
                  class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-90 shadow-sm"
                  title="កែប្រែ">
                  <Edit3 class="w-4 h-4" />
                </button>
              </td> -->
            </tr>

            <tr v-if="filteredStudents.length === 0">
              <td colspan="16" class="px-4 py-10 text-center text-slate-400">
                មិនមានទិន្នន័យសិស្សត្រូវនឹងលក្ខខណ្ឌស្វែងរកទេ
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Edit Modal -->
    <EditeStudent
      v-model="isEditModalOpen"
      :student-data="selectedStudent"
      @refresh="fetchStudents"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Search, Edit3 } from 'lucide-vue-next'
import api from '../../services/authService'
import EditeStudent from '../teacher/editStudent/EditStudent.vue'

const searchQuery = ref('')
const students = ref([])
const loading = ref(true)
const isEditModalOpen = ref(false)
const selectedStudent = ref(null)

const fetchStudents = async () => {
  try {
    loading.value = true
    const res = await api.get('/my-students')
    students.value = res.data.data || []
  } catch (error) {
    console.error("Error fetching students:", error)
  } finally {
    loading.value = false
  }
}

const openEditModal = (student) => {
  selectedStudent.value = student
  isEditModalOpen.value = true
}

const filteredStudents = computed(() => {
  if (!students.value) return []
  const q = searchQuery.value.toLowerCase().trim()
  return students.value.filter(s =>
    s.name_kh?.toLowerCase().includes(q) ||
    s.name_en?.toLowerCase().includes(q) ||
    s.student_id_card?.toLowerCase().includes(q)
  )
})

onMounted(fetchStudents)
</script>