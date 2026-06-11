<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងមុខវិជ្ជា</h1>
        <p class="text-sm text-slate-500 mt-1">កំណត់ និងគ្រប់គ្រងមុខវិជ្ជាសិក្សាទាំងអស់ក្នុងប្រព័ន្ធ</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button @click="openCreateModal"
          class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
          <Plus class="w-5 h-5" />
          បន្ថែមមុខវិជ្ជាថ្មី
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 font-[Battambang]">
      <div v-for="stat in subjectStats" :key="stat.label" 
        class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 transition-all hover:border-indigo-300">
        <div :class="['p-3 rounded-xl', stat.bgColor]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
        </div>
        <div>
          <p class="text-[14px] text-slate-500 font-medium uppercase tracking-wider">{{ stat.label }}</p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row gap-4 justify-between bg-slate-50/30">
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
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">មុខវិជ្ជា</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">គ្រូបង្រៀន</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider">ថ្នាក់រៀន</th>
              <th class="px-6 py-4 text-[14px] font-bold text-slate-500 uppercase tracking-wider text-right">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="subject in filteredSubjects" :key="subject.id" class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100">
                    <BookOpen class="w-5 h-5 text-indigo-600" />
                  </div>
                  <span class="text-sm font-bold text-slate-700 uppercase tracking-tight">{{ subject.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                  <div v-if="subject.teacher" class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                  {{ subject.teacher?.name_kh || subject.teacher?.name || 'មិនទាន់មាន' }}
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="text-xs font-medium px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                  {{ subject.class?.grade_level }}{{ subject.class?.name || 'N/A' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="handleEdit(subject)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="handleDelete(subject.id)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
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

    <EditeSubject 
      v-if="showEditModal" 
      v-model="showEditModal" 
      :subject-data="selectedSubject" 
      @refresh="fetchSubjects" 
    />

    <SubjectCreateModal 
      v-if="showCreateModal" 
      v-model="showCreateModal" 
      @refresh="fetchSubjects" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Plus, Search, BookOpen, Edit3, Trash2, Layers, Clock } from 'lucide-vue-next'
import api from '../../services/authService'
import SubjectCreateModal from '../admin/create/CreateSubjectModel.vue'
import EditeSubject from '../admin/edite/EditeSubject.vue'

const subjects = ref([])
const isLoading = ref(true)
const searchQuery = ref('')

const showCreateModal = ref(false)
const showEditModal = ref(false)     // 💡 បន្ថែម State សម្រាប់បើក/បិទផ្ទាំង Edit
const selectedSubject = ref(null)    // 💡 បន្ថែម State សម្រាប់ទុកទិន្នន័យជួរដែលត្រូវកែប្រែ

const fetchSubjects = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/subjects')
    subjects.value = res.data || []
  } catch (error) {
    console.error('Fetch error:', error)
  } finally {
    isLoading.value = false
  }
}

const openCreateModal = () => {
  showCreateModal.value = true
}

// 💡 បំពេញ Logic ផ្ញើទិន្នន័យទៅកាន់ Modal កែសម្រួល
const handleEdit = (subject) => {
  selectedSubject.value = subject
  showEditModal.value = true
}

const handleDelete = async (id) => {
  if (confirm('តើអ្នកប្រាកដថាចង់លុបមុខវិជ្ជានេះមែនទេ?')) {
    try {
      await api.delete(`/subjects/${id}`)
      await fetchSubjects()
    } catch (error) {
      alert('មិនអាចលុបបានទេ!')
    }
  }
}

const filteredSubjects = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  return subjects.value.filter(s => s.name?.toLowerCase().includes(query))
})

const subjectStats = computed(() => [
  {
    label: 'មុខវិជ្ជាសរុប',
    value: subjects.value.length + ' មុខ',
    icon: BookOpen,
    bgColor: 'bg-indigo-50',
    iconColor: 'text-indigo-600'
  },
  {
    label: 'គ្រូបង្រៀនសរុប',
    value: [...new Set(subjects.value.map(s => s.teacher_id))].filter(id => id).length + ' នាក់',
    icon: Layers,
    bgColor: 'bg-emerald-50',
    iconColor: 'text-emerald-600'
  },
  {
    label: 'ថ្នាក់រៀនសរុប',
    value: [...new Set(subjects.value.map(s => s.class_id))].filter(id => id).length + ' ថ្នាក់',
    icon: Clock,
    bgColor: 'bg-orange-50',
    iconColor: 'text-orange-600'
  }
])

onMounted(fetchSubjects)
</script>