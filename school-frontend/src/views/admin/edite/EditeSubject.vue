<template>
  <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-in zoom-in duration-200 font-[Battambang]">
      
      <div class="p-5 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shadow-sm border border-amber-100/50">
            <Edit3 class="w-5 h-5 text-blue-500" />
          </div>
          <div>
            <h2 class="text-base font-bold text-slate-800">កែសម្រួលមុខវិជ្ជា</h2>
            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Edit Subject Details</p>
          </div>
        </div>
        <button @click="closeModal" class="p-1.5 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-slate-600">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-5 space-y-4">
        
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-600 ml-0.5">ឈ្មោះមុខវិជ្ជា <span class="text-rose-500">*</span></label>
          <div class="relative">
            <Type class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <input 
              v-model="form.name"
              type="text" 
              placeholder="ឧទាហរណ៍៖ គណិតវិទ្យា"
              required
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all placeholder:text-slate-400"
            />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-600 ml-0.5">ថ្នាក់រៀន <span class="text-rose-500">*</span></label>
          <div class="relative">
            <School class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select 
              v-model="form.class_id"
              required
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none appearance-none cursor-pointer transition-all"
            >
              <option value="" disabled>ជ្រើសរើសថ្នាក់ (ឧទាហរណ៍: 9A, 12B...)</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                ថ្នាក់ទី {{ cls.grade_level }}{{ cls.name }}
              </option>
            </select>
            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-600 ml-0.5">គ្រូបង្រៀន (មិនបាច់ដាក់ក៏បាន)</label>
          <div class="relative">
            <UserCheck class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select 
              v-model="form.teacher_id"
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none appearance-none cursor-pointer transition-all"
            >
              <option value="">ជ្រើសរើសគ្រូបង្រៀន (Optional)</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.name_kh || teacher.name }}
              </option>
            </select>
            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <div v-if="errorMessage" class="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2 animate-shake">
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ errorMessage }}</span>
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
import { ref, onMounted, watch } from 'vue'
import { X, Edit3, Type, School, ChevronDown, UserCheck, Save, AlertCircle } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({
  modelValue: Boolean,
  subjectData: Object
})

const emit = defineEmits(['update:modelValue', 'refresh'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const classes = ref([])  
const teachers = ref([]) 

const form = ref({
  name: '',
  class_id: '',
  teacher_id: ''
})

const assignFormData = () => {
  if (props.subjectData) {
    errorMessage.value = null 
    form.value = {
      name: props.subjectData.name || '',
      class_id: props.subjectData.class_id || '',
      teacher_id: props.subjectData.teacher_id || '' 
    }
  }
}

const fetchOptionsData = async () => {
  try {
    const classRes = await api.get('/classes')   
    classes.value = classRes.data.data || classRes.data

    const teacherRes = await api.get('/teachers')
    teachers.value = teacherRes.data.data || teacherRes.data
  } catch (error) {
    console.error('Error fetching modal options:', error)
  }
}

watch(() => props.subjectData, assignFormData, { immediate: true })

const closeModal = () => {
  emit('update:modelValue', false)
}

const handleSubmit = async () => {
  if (!form.value.name || !form.value.class_id) {
    errorMessage.value = 'សូមបំពេញព័ត៌មានចាំបាច់ឱ្យគ្រប់គ្រាន់!'
    return
  }

  isSubmitting.value = true
  errorMessage.value = null
  
  try {
    const payload = {
      name: form.value.name,
      class_id: form.value.class_id,
      teacher_id: form.value.teacher_id || null
    }

    await api.put(`/subjects/${props.subjectData.id}`, payload) 
    
    emit('refresh') 
    closeModal() 
  } catch (error) {
    console.error('Update Subject Error:', error)
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors;
      errorMessage.value = "ទិន្នន័យមិនត្រឹមត្រូវ៖ " + Object.values(errors).flat().join(", ");
    } else {
      errorMessage.value = error.response?.data?.message || 'មានបញ្ហាក្នុងការកែសម្រួលទិន្នន័យ!';
    }
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchOptionsData()
  assignFormData()
})
</script>

<style scoped>
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

.animate-shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>