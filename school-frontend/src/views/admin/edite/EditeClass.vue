<!-- EditClassModal.vue -->
<template>
  <Transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-xl shadow-xl overflow-hidden animate-in zoom-in duration-200 flex flex-col max-h-[90vh]">
        
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-xl font-bold text-slate-800 font-[Battambang] flex items-center gap-2">
            <GraduationCap class="w-6 h-6 text-indigo-600" />
            កែសម្រួលព័ត៌មានថ្នាក់រៀន
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>
  
        <form @submit.prevent="submitForm" class="p-6 space-y-4 font-[Battambang] overflow-y-auto custom-scrollbar flex-1">
            
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះថ្នាក់រៀន <span class="text-rose-500">*</span></label>
            <input v-model="form.name" type="text" placeholder="ឧទាហរណ៍៖ A, B,..." :class="inputClass" required />
          </div>
  
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">កម្រិតថ្នាក់ <span class="text-rose-500">*</span></label>
              <select v-model="form.grade_level" :class="inputClass" class="cursor-pointer" required>
                <option :value="7">ថ្នាក់ទី ៧</option>
                <option :value="8">ថ្នាក់ទី ៨</option>
                <option :value="9">ថ្នាក់ទី ៩</option>
              </select>
            </div>
  
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឆ្នាំសិក្សា <span class="text-rose-500">*</span></label>
              <select v-model="form.year_id" :class="inputClass" class="cursor-pointer" required>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.year_name || year.name }}
                </option>
              </select>
            </div>
          </div>
  
          <!-- Custom Teacher Dropdown -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">គ្រូបន្ទុកថ្នាក់ (បើមាន)</label>
            
            <div class="relative">
              <button
                ref="teacherButtonRef"
                type="button"
                @click="toggleTeacherDropdown"
                :class="inputClass + ' flex items-center justify-between text-left w-full'"
              >
                <span class="truncate">
                  {{ selectedTeacherName || '-- ជ្រើសរើសគ្រូបន្ទុកថ្នាក់ --' }}
                </span>
                <ChevronDown class="w-4 h-4 text-slate-400 shrink-0" />
              </button>

              <Teleport to="body">
                <Transition name="dropdown">
                  <div 
                    v-if="showTeacherDropdown"
                    ref="dropdownRef"
                    class="fixed z-[9999] bg-white border border-slate-200 rounded-xl shadow-2xl py-1 max-h-[140px] overflow-y-auto custom-scrollbar min-w-[260px]"
                    :style="{ 
                      top: dropdownPosition.top + 'px', 
                      left: dropdownPosition.left + 'px',
                      width: dropdownPosition.width + 'px'
                    }"
                  >
                    <!-- No teacher option -->
                    <div 
                      @click="selectTeacher(null)"
                      class="px-4 py-2.5 text-sm hover:bg-slate-100 cursor-pointer flex items-center gap-2 text-slate-500"
                      :class="{ 'bg-slate-100 text-slate-700': !form.teacher_id }"
                    >
                      -- ជ្រើសរើសគ្រូបន្ទុកថ្នាក់ --
                    </div>
                    
                    <div 
                      v-for="teacher in teachers" 
                      :key="teacher.id"
                      @click="selectTeacher(teacher)"
                      class="px-4 py-2.5 text-sm hover:bg-indigo-50 cursor-pointer flex items-center gap-2 transition-colors"
                      :class="{ 
                        'bg-indigo-50 text-indigo-700 font-medium': form.teacher_id === teacher.id 
                      }"
                    >
                      {{ teacher.name_kh || teacher.name }}
                    </div>
                  </div>
                </Transition>
              </Teleport>
            </div>
          </div>
  
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាពថ្នាក់ <span class="text-rose-500">*</span></label>
            <select v-model="form.is_active" :class="inputClass" class="cursor-pointer">
              <option :value="true">បើកដំណើរការ</option>
              <option :value="false">បិទបណ្តោះអាសន្ន</option>
            </select>
          </div>
  
          <div v-if="errorMessage" class="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2 animate-shake">
            <AlertCircle class="w-4 h-4 shrink-0" />
            <span>{{ errorMessage }}</span>
          </div>
        </form>
  
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50 font-[Battambang]">
          <button type="button" @click="closeModal" class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
            បោះបង់
          </button>
          <button @click="submitForm" :disabled="loading" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 disabled:opacity-70 flex items-center gap-2">
            <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការកែប្រែ' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue'
import { X, GraduationCap, AlertCircle, ChevronDown } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({
  modelValue: Boolean,
  classData: Object
})

const emit = defineEmits(['update:modelValue', 'refresh', 'error'])

const loading = ref(false)
const errorMessage = ref(null)
const academicYears = ref([])
const teachers = ref([])

const inputClass = "w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-[Battambang]"

const form = ref({
  id: null,
  name: '',
  year_id: '',
  grade_level: 7,
  is_active: true,
  teacher_id: null
})

// Custom dropdown state
const showTeacherDropdown = ref(false)
const teacherButtonRef = ref(null)
const dropdownRef = ref(null)
const dropdownPosition = ref({ top: 0, left: 0, width: 0 })

const selectedTeacherName = computed(() => {
  if (!form.value.teacher_id) return null
  const teacher = teachers.value.find(t => t.id === form.value.teacher_id)
  return teacher ? (teacher.name_kh || teacher.name) : null
})

const fetchDropdownData = async () => {
  try {
    const [yearsResponse, teachersResponse] = await Promise.all([
      api.get('/years'),
      api.get('/teachers')
    ])
    academicYears.value = yearsResponse.data || []
    teachers.value = teachersResponse.data || []
  } catch (error) {
    console.error('បរាជ័យក្នុងការទាញទិន្នន័យជំនួយ:', error)
  }
}

watch(() => props.classData, (newData) => {
  if (newData) {
    errorMessage.value = null
    form.value = {
      id: newData.id || null,
      name: newData.name || '',
      year_id: newData.year_id || '',
      grade_level: newData.grade_level ? parseInt(newData.grade_level) : 7,
      is_active: newData.is_active === undefined ? true : Boolean(newData.is_active),
      teacher_id: newData.teacher_id || null
    }
  }
}, { immediate: true, deep: true })

const closeModal = () => {
  showTeacherDropdown.value = false
  emit('update:modelValue', false)
}

const calculateDropdownPosition = () => {
  if (!teacherButtonRef.value) return
  
  const rect = teacherButtonRef.value.getBoundingClientRect()
  dropdownPosition.value = {
    top: rect.bottom + window.scrollY + 4,
    left: rect.left + window.scrollX,
    width: rect.width
  }
}

const toggleTeacherDropdown = () => {
  if (!showTeacherDropdown.value) {
    calculateDropdownPosition()
  }
  showTeacherDropdown.value = !showTeacherDropdown.value
}

const selectTeacher = (teacher) => {
  form.value.teacher_id = teacher ? teacher.id : null
  showTeacherDropdown.value = false
}

const handleClickOutside = (e) => {
  if (!showTeacherDropdown.value) return
  
  const clickedButton = teacherButtonRef.value && teacherButtonRef.value.contains(e.target)
  const clickedDropdown = dropdownRef.value && dropdownRef.value.contains(e.target)
  
  if (!clickedButton && !clickedDropdown) {
    showTeacherDropdown.value = false
  }
}

const submitForm = async () => {
  loading.value = true
  errorMessage.value = null

  try {
    await api.put(`/classes/${form.value.id}`, {
      name: form.value.name,
      year_id: form.value.year_id,
      grade_level: form.value.grade_level,
      is_active: form.value.is_active,
      teacher_id: form.value.teacher_id
    })

    emit('refresh')
    closeModal()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុកការកែប្រែ'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDropdownData()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.animate-shake { animation: shake 0.4s both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}

/* Custom dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>