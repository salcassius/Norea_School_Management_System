<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងសិស្សានុសិស្ស</h1>
        <p class="text-sm text-slate-500 mt-1">បញ្ជីឈ្មោះសិស្សក្នុងថ្នាក់ដែលអ្នកទទួលខុសត្រូវ</p>
      </div>
      <div class="relative max-w-sm w-full md:w-64">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input v-model="searchQuery" type="text" placeholder="ស្វែងរកឈ្មោះសិស្ស..."
          class="w-full bg-white border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-sm outline-none focus:border-indigo-500 transition-all" />
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="w-full text-left border-collapse min-w-[1200px]">
            <thead>
              <tr class="bg-slate-50 text-[14px] font-bold text-slate-600 uppercase tracking-wider">
                <th class="px-4 py-3 w-12 sticky left-0 bg-slate-50 z-10">ល.រ</th>
                <th class="px-4 py-3 w-40 sticky left-12 bg-slate-50 z-10">ឈ្មោះសិស្ស</th>
                <th class="px-4 py-3">អត្តលេខ</th>
                <th class="px-4 py-3">ភេទ</th>
                <th class="px-4 py-3">ថ្ងៃកំណើត</th>
                <th class="px-6 py-4 text-[14px] font-bold text-slate-600 uppercase tracking-wider">អាសយដ្ឋានបច្ចុប្បន្ន</th>
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
                <td class="px-4 py-4 font-mono font-bold text-[14px] text-indigo-600 uppercase">{{ std.student_id_card }}</td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">{{ std.gender === 'male' ? 'ប្រុស' : 'ស្រី' }}</td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">{{ std.date_of_birth ? new Date(std.date_of_birth).toLocaleDateString('km-KH') : 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">
                <div class="text-[14px]">
                  {{ std.village }}, {{ std.commune }}, {{ std.district }}, {{ std.province }}
                </div>
              </td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">{{ std.phone }}</td>
                <td class="px-4 py-4 text-slate-700 text-[14px]">
                  <div>{{ std.guardian_dad_name }}</div>
                  <div class="text-[13px] text-slate-700">{{ std.guardian_dad_phone }}</div>
                </td>
                <td class="px-4 py-4 text-[14px] text-slate-700">
                  <div>{{ std.guardian_mom_name }}</div>
                  <div class="text-[13px] text-slate-700">{{ std.guardian_mom_phone }}</div>
                </td>
              </tr>
            </tbody>
          </table>
    </div>

    <!-- <EditStudent 
      v-model="isEditModalOpen" 
      :student-data="selectedStudent" 
      @refresh="fetchStudents" 
    /> -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Search } from 'lucide-vue-next'
import api from '../../services/authService'


const searchQuery = ref('')
const students = ref([])
const isEditModalOpen = ref(false)
const selectedStudent = ref(null)

const fetchStudents = async () => {
  try {
    const response = await api.get('/my-students');
    students.value = response.data.data;
  } catch (error) {
    console.error("កំហុសក្នុងការទាញយកសិស្ស:", error);
  }
}

const openEditModal = (student) => {
  selectedStudent.value = student
  isEditModalOpen.value = true
}

onMounted(fetchStudents)

const filteredStudents = computed(() => {
  if (!students.value) return [];
  return students.value.filter(s =>
    s.name_kh?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    s.student_id_card?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})
</script>