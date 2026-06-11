<template>
  <div class="font-[Battambang] p-6 min-h-screen bg-slate-50/50">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">គ្រប់គ្រងការប្រឡង</h1>
        <p class="text-sm text-slate-500 mt-1">រៀបចំកាលវិភាគ បញ្ជីឈ្មោះ និងលទ្ធផលប្រឡង</p>
      </div>

      <button @click="showCreateModal = true"
        class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-indigo-200 active:scale-95">
        <Plus class="w-5 h-5" />
        បង្កើតការប្រឡងថ្មី
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in examStats" :key="stat.label" 
        class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div :class="['p-3 rounded-xl', stat.bgColor]">
          <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
        </div>
        <div>
          <p class="text-[12px] text-slate-400 font-bold uppercase tracking-wider">{{ stat.label }}</p>
          <p class="text-lg font-bold text-slate-800">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row gap-4 justify-between bg-slate-50/30">
        <div class="relative max-w-sm w-full">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input v-model="searchQuery" type="text" placeholder="ស្វែងរកឈ្មោះការប្រឡង..."
            class="w-full bg-white border border-slate-200 rounded-lg py-2 pl-10 pr-4 text-sm focus:border-indigo-500 outline-none" />
        </div>
      </div>

      <div class="overflow-x-auto">
        <div v-if="isLoading" class="p-10 text-center text-sm text-slate-500">កំពុងផ្ទុកទិន្នន័យ...</div>
        <table v-else class="w-full text-left">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ឈ្មោះការប្រឡង</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">មុខវិជ្ជា/ថ្នាក់</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">កាលបរិច្ឆេទ</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ស្ថានភាព</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">សកម្មភាព</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="exam in filteredExams" :key="exam.id" class="hover:bg-slate-50/80 transition-colors group">
              <td class="px-6 py-4 font-bold text-slate-700">{{ exam.name }}</td>
              <td class="px-6 py-4 text-sm">{{ exam.subject?.name }} ({{ exam.class?.name }})</td>
              <td class="px-6 py-4 text-sm">{{ exam.exam_date }}</td>
              <td class="px-6 py-4">
                <span :class="['px-2 py-1 rounded text-[10px] font-bold', getStatusClass(exam.status)]">
                  {{ exam.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button @click="handleDelete(exam.id)" class="text-rose-500 hover:text-rose-700 text-sm font-semibold">លុប</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <CreateExamModel 
      v-if="showCreateModal" 
      @close="showCreateModal = false" 
      @saved="handleSaved"
      :subjects="subjects"
      :classes="classes"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Plus, Search, ClipboardCheck, CheckCircle2, Clock } from 'lucide-vue-next'
import api from '@/services/authService'
import CreateExamModel from '../admin/create/CreateExamModel.vue'

// States
const exams = ref([])
const subjects = ref([])
const classes = ref([])
const searchQuery = ref('')
const isLoading = ref(false)
const showCreateModal = ref(false)

// Fetch Data
const fetchData = async () => {
  isLoading.value = true
  try {
    const [examRes, subRes, classRes] = await Promise.all([
      api.get('/exams'),
      api.get('/subjects'),
      api.get('/classes')
    ])
    exams.value = examRes.data.data
    subjects.value = subRes.data
    classes.value = classRes.data
  } catch (err) {
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const handleSaved = () => {
  showCreateModal.value = false
  fetchData()
}

const handleDelete = async (id) => {
  if (confirm('តើអ្នកពិតជាចង់លុបការប្រឡងនេះមែនទេ?')) {
    await api.delete(`/exams/${id}`)
    fetchData()
  }
}

const getStatusClass = (status) => {
  return status === 'បានបញ្ចប់' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
}

const filteredExams = computed(() => {
  return exams.value.filter(e => e.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
})

const examStats = computed(() => [
  { label: 'សរុប', value: exams.value.length + ' លើក', icon: ClipboardCheck, bgColor: 'bg-indigo-50', iconColor: 'text-indigo-600' },
  { label: 'បានបញ្ចប់', value: exams.value.filter(e => e.status === 'បានបញ្ចប់').length + ' លើក', icon: CheckCircle2, bgColor: 'bg-emerald-50', iconColor: 'text-emerald-600' },
  { label: 'ជិតមកដល់', value: exams.value.filter(e => e.status === 'ជិតមកដល់').length + ' លើក', icon: Clock, bgColor: 'bg-amber-50', iconColor: 'text-amber-600' }
])

onMounted(fetchData)
</script>