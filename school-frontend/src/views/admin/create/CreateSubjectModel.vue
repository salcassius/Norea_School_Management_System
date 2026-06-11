<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300 font-[Battambang]">
      
      <div class="p-6 pb-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shadow-sm">
            <BookOpen class="w-6 h-6 text-indigo-600" />
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-800">បន្ថែមមុខវិជ្ជាថ្មី</h2>
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Add New Subject</p>
          </div>
        </div>
        <button @click="$emit('update:modelValue', false)" class="p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400 hover:text-slate-600">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-6 space-y-5">
        
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">ឈ្មោះមុខវិជ្ជា</label>
          <div class="relative">
            <Type class="absolute left-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <input 
              v-model="form.name"
              type="text" 
              placeholder="ឧទាហរណ៍៖ គណិតវិទ្យា"
              required
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 pl-12 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all"
            />
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">ថ្នាក់រៀន</label>
          <div class="relative">
            <School class="absolute left-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select 
              v-model="form.class_id"
              required
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 pl-12 pr-10 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none appearance-none cursor-pointer transition-all"
            >
              <option value="" disabled>ជ្រើសរើសថ្នាក់ (ឧទាហរណ៍: 9A, 12B...)</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.grade_level }}{{ cls.name }}
              </option>
            </select>
            <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">គ្រូបង្រៀន (មិនបាច់ដាក់ក៏បាន)</label>
          <div class="relative">
            <UserCheck class="absolute left-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select 
              v-model="form.teacher_id"
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-3 pl-12 pr-10 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none appearance-none cursor-pointer transition-all"
            >
              <option value="">ជ្រើសរើសគ្រូបង្រៀន (Optional)</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.name_kh || teacher.name }} </option>
            </select>
            <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
          <button type="button" @click="closeModal" 
            class="px-5 py-2.5 text-slate-600 hover:bg-slate-200 rounded-xl font-medium transition-all text-sm">
            បោះបង់
          </button>
          <button @click="submitForm" :disabled="loading"
            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all font-bold text-sm flex items-center gap-2 disabled:opacity-50">
            <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { X, BookOpen, Type, School, ChevronDown, UserCheck } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue', 'refresh'])

const isSubmitting = ref(false)
const classes = ref([])  
const teachers = ref([]) // បន្ថែម State សម្រាប់រក្សាទុកបញ្ជីឈ្មោះគ្រូ

const form = ref({
  name: '',
  class_id: '',
  teacher_id: '' // បង្កើតជា String ទទេជាតម្លៃលំនាំដើម
})

const fetchData = async () => {
  try {
    // ១. ទាញយកទិន្នន័យថ្នាក់
    const classRes = await api.get('/classes')   
    classes.value = classRes.data.data || classRes.data

    // ២. ទាញយកទិន្នន័យគ្រូបង្រៀនពី API
    const teacherRes = await api.get('/teachers')
    teachers.value = teacherRes.data.data || teacherRes.data
  } catch (error) {
    console.error('Error fetching modal data:', error)
  }
}

const handleSubmit = async () => {
  if (!form.value.name || !form.value.class_id) {
    alert('សូមបំពេញព័ត៌មានឱ្យគ្រប់គ្រាន់!')
    return
  }

  isSubmitting.value = true
  try {
    // បង្កើត Object ទិន្នន័យដែលត្រូវផ្ញើទៅ API
    const payload = {
      name: form.value.name,
      class_id: form.value.class_id,
      // ប្រសិនបើមិនបានជ្រើសរើសគ្រូទេ ឱ្យផ្ញើទៅជា null
      teacher_id: form.value.teacher_id || null 
    }

    await api.post('/subjects', payload) 
    emit('refresh')
    emit('update:modelValue', false)
    
    // Reset Form
    form.value = { name: '', class_id: '', teacher_id: '' }
  } catch (error) {
    alert(error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក!')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(fetchData)
</script>

<style scoped>
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}
</style>