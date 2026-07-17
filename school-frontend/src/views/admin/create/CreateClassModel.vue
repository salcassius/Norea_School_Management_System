<!-- CreateClassModal.vue -->
<template>
  <Transition name="fade">
    <div v-if="isOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-md shadow-xl overflow-hidden animate-in zoom-in duration-200">

        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-xl font-bold text-slate-800 font-[Battambang]">បង្កើតថ្នាក់រៀនថ្មី</h3>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4 font-[Battambang]">
          
          <!-- ឈ្មោះថ្នាក់ -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះថ្នាក់ <span class="text-rose-500">*</span></label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="ឧទាហរណ៍៖ A, B,..."
              :class="inputClass"
            />
          </div>

          <!-- កម្រិតថ្នាក់ -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">កម្រិតថ្នាក់ <span class="text-rose-500">*</span></label>
            <select
              v-model="form.grade_level"
              required
              :class="inputClass + ' cursor-pointer'"
            >
              <option value="">-- ជ្រើសរើសកម្រិតថ្នាក់ --</option>
              <option v-for="g in gradeLevels" :key="g" :value="g">ថ្នាក់ទី {{ g }}</option>
            </select>
          </div>

          <!-- ឆ្នាំសិក្សា + គ្រូទទួលបន្ទុក -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឆ្នាំសិក្សា <span class="text-rose-500">*</span></label>
              <select
                v-model="form.year_id"
                required
                :class="inputClass + ' cursor-pointer'"
              >
                <option value="">-- ជ្រើសរើស --</option>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.year_name || year.name }}
                </option>
              </select>
            </div>
            
            <!-- Custom Teacher Dropdown -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">គ្រូទទួលបន្ទុក</label>
              
              <div class="relative">
                <button
                  ref="teacherButtonRef"
                  type="button"
                  @click="toggleTeacherDropdown"
                  :class="inputClass + ' flex items-center justify-between text-left w-full'"
                >
                  <span class="truncate text-sm">
                    {{ selectedTeacherName || '-- ជ្រើសរើស --' }}
                  </span>
                  <ChevronDown class="w-4 h-4 text-slate-400 shrink-0" />
                </button>

                <Teleport to="body">
                  <Transition name="dropdown">
                    <div 
                      v-if="showTeacherDropdown"
                      ref="dropdownRef"
                      class="fixed z-[9999] bg-white border border-slate-200 rounded-xl shadow-2xl py-1 max-h-[140px] overflow-y-auto custom-scrollbar min-w-[140px]"
                      :style="{ 
                        top: dropdownPosition.top + 'px', 
                        left: dropdownPosition.left + 'px',
                        width: dropdownPosition.width + 'px'
                      }"
                    >
                      <div 
                        @click="selectTeacher(null)"
                        class="px-4 py-2.5 text-sm hover:bg-slate-100 cursor-pointer text-slate-500"
                        :class="{ 'bg-slate-100': !form.teacher_id }"
                      >
                        -- ជ្រើសរើស --
                      </div>
                      <div 
                        v-for="teacher in teachers" 
                        :key="teacher.id"
                        @click="selectTeacher(teacher)"
                        class="px-4 py-2.5 text-sm hover:bg-indigo-50 cursor-pointer"
                        :class="{ 'bg-indigo-50 text-indigo-700 font-medium': form.teacher_id === teacher.id }"
                      >
                        {{ teacher.name_kh || teacher.name }}
                      </div>
                    </div>
                  </Transition>
                </Teleport>
              </div>
            </div>
          </div>

          <!-- ស្ថានភាព -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាព</label>
            <select
              v-model="form.is_active"
              :class="inputClass + ' cursor-pointer'"
            >
              <option :value="true">បើកដំណើរការ</option>
              <option :value="false">បិទបណ្តោះអាសន្ន</option>
            </select>
          </div>

          <!-- Error -->
          <p v-if="errorMessage" class="text-xs text-rose-500 font-bold">⚠ {{ errorMessage }}</p>

          <!-- Buttons -->
          <div class="flex justify-end gap-3 mt-8">
            <button
              type="button"
              @click="$emit('close')"
              class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm"
            >
              បោះបង់
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 disabled:opacity-70 flex items-center gap-2"
            >
              <span v-if="submitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ submitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
            </button>
          </div>
        </form>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue'
import { X, ChevronDown } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({
  isOpen: Boolean
})

const emit = defineEmits(['close', 'refresh', 'error'])

const submitting = ref(false)
const errorMessage = ref('')
const academicYears = ref([])
const teachers = ref([])

const gradeLevels = [7, 8, 9]

const inputClass = "w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-[Battambang]"

const form = reactive({
  name: '',
  grade_level: '',
  year_id: '',
  teacher_id: null,
  is_active: true,
})

// Custom dropdown state
const showTeacherDropdown = ref(false)
const teacherButtonRef = ref(null)
const dropdownRef = ref(null)
const dropdownPosition = ref({ top: 0, left: 0, width: 0 })

const selectedTeacherName = computed(() => {
  if (!form.teacher_id) return null
  const teacher = teachers.value.find(t => t.id === form.teacher_id)
  return teacher ? (teacher.name_kh || teacher.name) : null
})

const fetchDropdownData = async () => {
  try {
    const [yearsRes, teachersRes] = await Promise.all([
      api.get('/years'),
      api.get('/teachers'),
    ])
    academicYears.value = yearsRes.data || []
    teachers.value = teachersRes.data || []
  } catch (err) {
    console.error('Error loading dropdown data:', err)
  }
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
  form.teacher_id = teacher ? teacher.id : null
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

const handleSubmit = async () => {
  errorMessage.value = ''
  submitting.value = true
  try {
    await api.post('/classes', {
      name: form.name,
      grade_level: form.grade_level,
      year_id: form.year_id,
      teacher_id: form.teacher_id || null,
      is_active: form.is_active,
    })
    
    // Reset form
    Object.assign(form, { 
      name: '', 
      grade_level: '', 
      year_id: '', 
      teacher_id: null, 
      is_active: true 
    })
    
    emit('refresh')
    emit('close')
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'មានបញ្ហាក្នុងការបង្កើតថ្នាក់!'
  } finally {
    submitting.value = false
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