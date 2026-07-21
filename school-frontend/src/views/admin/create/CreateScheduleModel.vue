<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden flex flex-col animate-in zoom-in duration-200 font-[Battambang]">
      
      <!-- Header -->
      <div class="p-5 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center shadow-sm border border-indigo-100/50">
            <BookOpen class="w-5 h-5 text-indigo-600" />
          </div>
          <div>
            <h3 class="text-[18px] font-bold text-slate-800 tracking-tight">បន្ថែមម៉ោងសិក្សាថ្មី</h3>
            <p class="text-[12px] text-slate-500 font-medium uppercase tracking-wider mt-0.5">សូមបំពេញព័ត៌មានកាលវិភាគឱ្យបានត្រឹមត្រូវ</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-1.5 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-slate-600 cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-5 space-y-4">
        
        <!-- Inline error message -->
        <div v-if="errorMessage" class="flex items-start gap-2 bg-rose-50 border border-rose-200 text-rose-600 text-xs font-medium rounded-xl px-3.5 py-2.5">
          <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
          <span class="whitespace-pre-line">{{ errorMessage }}</span>
        </div>

        <!-- Subject Selection -->
        <div class="space-y-1.5">
          <label class="text-[14px] font-bold text-slate-600 ml-0.5">មុខវិជ្ជា <span class="text-rose-500">*</span></label>
          <div class="relative">
            <Layout class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select 
              v-model="form.subject_id" 
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer" 
              required
            >
              <option value="" disabled>--- ជ្រើសរើសមុខវិជ្ជា ---</option>
              <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
            </select>
            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <!-- Teacher Selection (Filtered by selected Subject) -->
        <div class="space-y-1.5 relative" ref="teacherDropdownRef">
          <label class="text-[14px] font-bold text-slate-600 ml-0.5">
            គ្រូបង្រៀន <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <UserRound :class="['absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 z-10', isTeacherFieldInvalid ? 'text-rose-400' : 'text-slate-400']" />
            
            <button 
              type="button" 
              @click="isTeacherDropdownOpen = !isTeacherDropdownOpen"
              :class="['w-full bg-slate-50/50 border rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-left outline-none focus:ring-4 transition-all cursor-pointer truncate',
                selectedTeacherLabel ? 'text-slate-700' : 'text-slate-400',
                isTeacherFieldInvalid ? 'border-rose-300 focus:ring-rose-500/10 focus:border-rose-500' : 'border-slate-200 focus:ring-indigo-500/10 focus:border-indigo-500']"
            >
              {{ selectedTeacherLabel || '--- ជ្រើសរើសគ្រូបង្រៀន ---' }}
            </button>

            <ChevronDown :class="['absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none transition-transform', isTeacherDropdownOpen ? 'rotate-180' : '']" />

            <!-- Dropdown Menu -->
            <div 
              v-if="isTeacherDropdownOpen"
              class="absolute z-20 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-[160px] overflow-y-auto custom-scrollbar min-w-[140px]"
            >
              <button 
                v-for="tc in filteredTeachers" 
                :key="tc.id" 
                type="button"
                @click="selectTeacher(tc)"
                class="w-full text-left px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors cursor-pointer"
              >
                {{ tc.name_kh || tc.name }} {{ tc.name_en ? `(${tc.name_en})` : '' }}
              </button>
              <div v-if="!filteredTeachers.length" class="px-3.5 py-2 text-sm text-slate-400 text-center">
                {{ form.subject_id ? 'មិនមានគ្រូបង្រៀនសម្រាប់មុខវិជ្ជានេះទេ' : 'សូមជ្រើសរើសមុខវិជ្ជាមុនសិន' }}
              </div>
            </div>
          </div>
        </div>

        <!-- Day & Time Info -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs font-semibold text-slate-500">
          <div>ថ្ងៃសិក្សា៖ <span class="text-slate-800 font-bold">{{ form.day }}</span></div>
          <div>ម៉ោងសិក្សា៖ <span class="text-slate-800 font-bold">{{ form.time }}</span></div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="$emit('close')" class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm cursor-pointer">
            បោះបង់
          </button>
          <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-sm transition-all font-semibold flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
            {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
          </button>
        </div>

      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { BookOpen, X, Layout, ChevronDown, UserRound, AlertCircle } from 'lucide-vue-next'
import api from '@/services/authService'

const props = defineProps({
  initData: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'saved', 'error'])

const form = ref({ ...props.initData })
const isSubmitting = ref(false)
const errorMessage = ref('')
const isTeacherFieldInvalid = ref(false)

const isTeacherDropdownOpen = ref(false)
const teacherDropdownRef = ref(null)

// ✅ មុខងារត្រងគ្រូបង្រៀនតាមមុខវិជ្ជាដែលបានជ្រើសរើស
const filteredTeachers = computed(() => {
  if (!form.value.subject_id) {
    return [] // បើមិនទាន់ជ្រើសរើសមុខវិជ្ជា បង្ហាញបញ្ជីទទេ (ឬអាចដាក់ return props.teachers ប្រសិនបើចង់បង្ហាញទាំងអស់)
  }

  return props.teachers.filter(tc => {
    // ពិនិត្យមើលទម្រង់ទិន្នន័យផ្សេងៗពី Backend (tc.subject_ids, tc.subjects ឬ tc.subject_id)
    if (Array.isArray(tc.subject_ids)) {
      return tc.subject_ids.includes(Number(form.value.subject_id)) || tc.subject_ids.includes(String(form.value.subject_id))
    }
    if (Array.isArray(tc.subjects)) {
      return tc.subjects.some(s => (s.id || s) == form.value.subject_id)
    }
    if (tc.subject_id !== undefined && tc.subject_id !== null) {
      return tc.subject_id == form.value.subject_id
    }
    // Fallback: ប្រសិនបើទិន្នន័យគ្រូមិនមានលក្ខខណ្ឌបញ្ជាក់មុខវិជ្ជា បង្ហាញទាំងអស់
    return true
  })
})

// ✅ សម្អាតឈ្មោះគ្រូដែលបានជ្រើសរើសពេលប្តូរមុខវិជ្ជា ហើយគ្រូនោះមិនបង្រៀនមុខវិជ្ជាថ្មី
watch(() => form.value.subject_id, () => {
  if (form.value.teacher_id) {
    const isValid = filteredTeachers.value.some(tc => tc.id === form.value.teacher_id)
    if (!isValid) {
      form.value.teacher_id = ''
    }
  }
})

// ✅ បង្ហាញឈ្មោះគ្រូដែលបានជ្រើសរើស
const selectedTeacherLabel = computed(() => {
  const tc = props.teachers.find(t => t.id === form.value.teacher_id || t.id === form.value.teacher)
  if (!tc) return ''
  return (tc.name_kh || tc.name || '') + (tc.name_en ? ` (${tc.name_en})` : '')
})

const selectTeacher = (tc) => {
  form.value.teacher_id = tc.id
  if (form.value.teacher !== undefined) {
    form.value.teacher = tc.id
  }
  isTeacherDropdownOpen.value = false
  isTeacherFieldInvalid.value = false
}

// បិទ Dropdown ពេលចុចក្រៅ
const handleClickOutside = (event) => {
  if (teacherDropdownRef.value && !teacherDropdownRef.value.contains(event.target)) {
    isTeacherDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''
  isTeacherFieldInvalid.value = false

  try {
    await api.post('/schedules', form.value)
    emit('saved')
  } catch (error) {
    console.error(error)

    if (error.response && error.response.status === 422) {
      const responseData = error.response.data
      const validationErrors = responseData?.errors

      if (validationErrors) {
        errorMessage.value = Object.values(validationErrors).flat().join('\n')
      } else if (responseData?.message) {
        errorMessage.value = responseData.message

        if (responseData.message.includes('គ្រូ')) {
          isTeacherFieldInvalid.value = true
        }
      } else {
        errorMessage.value = 'ទិន្នន័យមិនត្រឹមត្រូវ សូមពិនិត្យមើលព័ត៌មានដែលបានបំពេញ'
      }
    } else {
      errorMessage.value = error.response?.data?.message || 'មានកំហុសក្នុងការបង្កើតកាលវិភាគថ្មី!'
    }

    emit('error', errorMessage.value)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>