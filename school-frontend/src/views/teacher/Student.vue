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
      <table class="w-full text-left">
        <thead class="bg-slate-50/50 border-b border-slate-100">
          <tr>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ល.រ</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ឈ្មោះសិស្ស</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ភេទ</th>
            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ស្ថានភាព</th>
            <!-- <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">សកម្មភាព</th> -->
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="(student, index) in filteredStudents" :key="student.id" class="hover:bg-slate-50 transition-colors">
            <td class="px-6 py-4 text-sm text-slate-600">{{ index + 1 }}</td>
            <td class="px-6 py-4">
              <div class="font-bold text-slate-800">{{ student.name_kh }}</div>
              <div class="text-[11px] text-slate-400 uppercase">{{ student.student_id_card }}</div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600">{{ student.gender === 'male' ? 'ប្រុស' : 'ស្រី' }}</td>
            <td class="px-6 py-4">
              <span :class="student.status == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'" 
                    class="px-2 py-1 rounded-lg text-[10px] font-bold">
                {{ student.status == 1 ? 'កំពុងរៀន' : 'ឈប់រៀន' }}
              </span>
            </td>
            <!-- <td class="px-6 py-4 text-right">
              <button @click="openEditModal(student)" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs">
                កែសម្រួល
              </button>
            </td> -->
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