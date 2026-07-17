<template>
  <Transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden font-[Battambang] max-h-[90vh] flex flex-col">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <h3 class="text-xl font-bold text-slate-800">កែសម្រួលព័ត៌មានសិស្ស</h3>
            <p class="text-xs text-slate-500">កែប្រែព័ត៌មានចាំបាច់របស់សិស្សឱ្យបានត្រឹមត្រូវ</p>
          </div>
          <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors p-2 hover:bg-rose-50 rounded-full">
            <X class="w-6 h-6" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
          
          <!-- Basic Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះ (ខ្មែរ) <span class="text-rose-500">*</span></label>
              <input v-model="form.name_kh" type="text" :class="inputClass" required />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះឡាតាំង <span class="text-rose-500">*</span></label>
              <input v-model="form.name_en" type="text" :class="inputClass" class="uppercase" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឆ្នាំសិក្សា <span class="text-rose-500">*</span></label>
              <select v-model="form.year_id" :class="inputClass" required>
                <option value="" disabled>--- ជ្រើសរើសឆ្នាំសិក្សា ---</option>
                <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.year_name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">អត្ថលេខ <span class="text-rose-500">*</span></label>
              <input v-model="form.student_id_card" type="text" :class="inputClass" class="uppercase" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ភេទ <span class="text-rose-500">*</span></label>
              <select v-model="form.gender" :class="inputClass" required>
                <option value="male">ប្រុស</option>
                <option value="female">ស្រី</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃខែឆ្នាំកំណើត <span class="text-rose-500">*</span></label>
              <input v-model="form.date_of_birth" type="date" :class="inputClass" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">មកពី</label>
              <input v-model="form.pri_school" type="text" :class="inputClass" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្នាក់ទី</label>
              <input v-model="form.from_class" type="text" :class="inputClass" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃខែឆ្នាំចូលរៀន</label>
              <input v-model="form.enrollment_date" type="date" :class="inputClass" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាព <span class="text-rose-500">*</span></label>
              <select v-model="form.status" :class="inputClass" required>
                <option value="1">កំពុងរៀន</option>
                <option value="0">ឈប់រៀន/ព្យួរ</option>
              </select>
            </div>
          </div>

          <!-- ទីកន្លែងកំណើត -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <p class="text-sm font-bold text-indigo-600 mb-3 flex items-center gap-2">
              <MapPin class="w-4 h-4 text-indigo-500" /> ទីកន្លែងកំណើត
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <input v-model="form.pob_village" type="text" :class="inputClass" placeholder="ភូមិ" />
              <input v-model="form.pob_commune" type="text" :class="inputClass" placeholder="ឃុំ" />
              <input v-model="form.pob_district" type="text" :class="inputClass" placeholder="ស្រុក/ខណ្ឌ" />
              <input v-model="form.pob_province" type="text" :class="inputClass" placeholder="ខេត្ត/ក្រុង" />
            </div>
          </div>

          <!-- អាសយដ្ឋានបច្ចុប្បន្ន -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <div class="flex justify-between items-center mb-3">
              <p class="text-sm font-bold text-emerald-600 flex items-center gap-2">
                <Navigation class="w-4 h-4 text-emerald-500" /> អាសយដ្ឋានបច្ចុប្បន្ន
              </p>
              <button type="button" @click="copyPOB" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold">
                ដូចទីកន្លែងកំណើត
              </button>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <input v-model="form.village" type="text" :class="inputClass" placeholder="ភូមិ" />
              <input v-model="form.commune" type="text" :class="inputClass" placeholder="ឃុំ" />
              <input v-model="form.district" type="text" :class="inputClass" placeholder="ស្រុក/ខណ្ឌ" />
              <input v-model="form.province" type="text" :class="inputClass" placeholder="ខេត្ត/ក្រុង" />
            </div>
          </div>

          <!-- Phone -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">លេខទូរស័ព្ទសិស្ស</label>
              <input v-model="form.phone" type="tel" :class="inputClass" />
            </div>
          </div>

          <!-- Guardian Info -->
          <div class="border-t border-slate-100 pt-6">
            <p class="text-sm font-bold text-blue-700 mb-4 flex items-center gap-2">
              <Users class="w-5 h-5 text-blue-600" /> ព័ត៌មានអាណាព្យាបាល
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-[14px] font-semibold text-slate-600 mb-1">ឈ្មោះឪពុក</label>
                <input v-model="form.guardian_dad_name" type="text" :class="inputClass" placeholder="ឈ្មោះឪពុក" />
              </div>
              <div>
                <label class="block text-[14px] font-semibold text-slate-600 mb-1">មុខរបរ</label>
                <input v-model="form.guardian_dad_job" type="text" :class="inputClass" placeholder="មុខរបរ" />
              </div>
              <div>
                <label class="block text-[14px] font-semibold text-slate-600 mb-1">លេខទូរស័ព្ទ</label>
                <input v-model="form.guardian_dad_phone" type="tel" :class="inputClass" placeholder="លេខទូរស័ព្ទ" />
              </div>

              <div>
                <label class="block text-[14px] font-semibold text-slate-600 mb-1">ឈ្មោះម្ដាយ</label>
                <input v-model="form.guardian_mom_name" type="text" :class="inputClass" placeholder="ឈ្មោះម្ដាយ" />
              </div>
              <div>
                <label class="block text-[14px] font-semibold text-slate-600 mb-1">មុខរបរ</label>
                <input v-model="form.guardian_mom_job" type="text" :class="inputClass" placeholder="មុខរបរ" />
              </div>
              <div>
                <label class="block text-[14px] font-semibold text-slate-600 mb-1">លេខទូរស័ព្ទ</label>
                <input v-model="form.guardian_mom_phone" type="tel" :class="inputClass" placeholder="លេខទូរស័ព្ទ" />
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2">
            <AlertCircle class="w-4 h-4" /> {{ errorMessage }}
          </div>
        </form>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
          <button type="button" @click="closeModal" class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
            បោះបង់
          </button>
          <button @click="handleSubmit" :disabled="isSubmitting"
            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold shadow-lg transition-all text-sm flex items-center gap-2 disabled:opacity-50">
            <span v-if="isSubmitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការកែប្រែ' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { X, Camera, User, MapPin, Navigation, Users, AlertCircle } from 'lucide-vue-next'
import studentService from '../../../services/studentService'
import api from '../../../services/authService'

const props = defineProps(['modelValue', 'studentData'])
const emit = defineEmits(['update:modelValue', 'refresh'])

const isSubmitting = ref(false)
const errorMessage = ref(null)
const photoPreview = ref(null)
const academicYears = ref([])

const inputClass = "w-full mt-1.5 border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 text-sm font-medium text-slate-700 bg-white"

const form = reactive({
  id: '',
  name_kh: '', name_en: '', student_id_card: '', gender: 'male',
  date_of_birth: '', pri_school: '', from_class: '',
  phone: '', year_id: '',
  pob_province: '', pob_district: '', pob_commune: '', pob_village: '',
  province: '', district: '', commune: '', village: '',
  enrollment_date: '',
  guardian_mom_name: '', guardian_mom_job: '', guardian_mom_phone: '',
  guardian_dad_name: '', guardian_dad_job: '', guardian_dad_phone: '',
  status: 1, photo: null
})

const fetchData = async () => {
  try {
    const { data } = await api.get('/years')
    academicYears.value = data
  } catch (error) {
    console.error("Fetch academic years error:", error)
  }
}

onMounted(fetchData)

// Watch studentData
watch(() => props.studentData, (newVal) => {
  if (!newVal) return

  Object.keys(form).forEach(key => {
    if (key === 'status') form[key] = 1
    else if (key === 'gender') form[key] = 'male'
    else form[key] = ''
  })

  Object.keys(form).forEach(key => {
    if (newVal.hasOwnProperty(key) && key !== 'photo') {
      form[key] = newVal[key] ?? ''
    }
  })

  if (newVal.date_of_birth) form.date_of_birth = newVal.date_of_birth.split('T')[0]
  if (newVal.enrollment_date) form.enrollment_date = newVal.enrollment_date.split('T')[0]

  photoPreview.value = null
  errorMessage.value = null
}, { immediate: true, deep: true })

const handlePhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    if (photoPreview.value) URL.revokeObjectURL(photoPreview.value)
    form.photo = file
    photoPreview.value = URL.createObjectURL(file)
  }
}

const copyPOB = () => {
  form.village = form.pob_village
  form.commune = form.pob_commune
  form.district = form.pob_district
  form.province = form.pob_province
}

const closeModal = () => emit('update:modelValue', false)

const handleSubmit = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true
  errorMessage.value = null

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')

    Object.keys(form).forEach(key => {
      if (key === 'id') return
      if (key === 'photo' && !(form[key] instanceof File)) return
      if (form[key] !== null && form[key] !== undefined && form[key] !== '') {
        formData.append(key, form[key])
      }
    })

    // ✅ Use teacher-specific endpoint
    await api.post(`/my-students/${form.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    emit('refresh')
    closeModal()
  } catch (error) {
    console.error('Update Error:', error)
    
    if (error.response?.status === 422) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(', ')
    } else if (error.response?.status === 403) {
      errorMessage.value = 'អ្នកមិនមានសិទ្ធិកែសម្រួលសិស្សនេះទេ'
    } else {
      errorMessage.value = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
</style>