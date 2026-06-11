<template>
  <Transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl overflow-hidden animate-in zoom-in duration-200 font-[Battambang] max-h-[90vh] flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-xl font-bold text-slate-800">បន្ថែមគ្រូបង្រៀនថ្មី</h3>
          <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">

          <div class="flex flex-col items-center justify-center pb-2 border-b border-slate-100 w-full gap-2">
            <div class="relative shrink-0">
              <div class="w-24 h-24 rounded-2xl border border-slate-300 overflow-hidden bg-slate-50 flex items-center justify-center shadow-md">
                <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                <User v-else class="w-12 h-12 text-slate-300" />
              </div>
              
              <label class="absolute -bottom-1 -right-1 bg-white p-2 rounded-full shadow-md border border-slate-200 cursor-pointer text-slate-600 hover:text-indigo-600 transition-colors">
                <Camera class="w-4 h-4" />
                <input type="file" @change="handleFile" accept="image/*" class="sr-only" />
              </label>
            </div>
            <div class="text-center mt-1">
              <h4 class="text-sm font-semibold text-slate-700">រូបថតប្រវត្តិរូប</h4>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">លេខសម្គាល់គ្រូបង្រៀន <span class="text-rose-500">*</span></label>
              <input v-model="form.teacher_id_card" type="text" :class="inputClass" placeholder="ឧ. T-2026-001" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះ (ខ្មែរ) <span class="text-rose-500">*</span></label>
              <input v-model="form.name_kh" type="text" :class="inputClass" placeholder="ឧ. សុខ សំណាង" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះឡាតាំង <span class="text-rose-500">*</span></label>
              <input v-model="form.name_en" type="text" :class="inputClass" placeholder="Ex. SOK SAMNANG" class="uppercase" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ភេទ <span class="text-rose-500">*</span></label>
              <select v-model="form.gender" :class="inputClass" class="cursor-pointer" required>
                <option value="male">ប្រុស</option>
                <option value="female">ស្រី</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">អ៊ីមែល <span class="text-rose-500">*</span></label>
              <input v-model="form.email" type="email" :class="inputClass" placeholder="example@mail.com" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃខែឆ្នាំកំណើត</label>
              <input v-model="form.dob" type="date" :class="inputClass" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">លេខទូរស័ព្ទ</label>
              <input v-model="form.phone" type="text" :class="inputClass" placeholder="012 345 678" />
            </div>
          </div>

          <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
            <p class="text-sm font-bold text-indigo-600 mb-3 flex items-center gap-2">
              <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
              ទីកន្លែងកំណើត
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ភូមិ</label>
                <input v-model="form.pob_village" type="text" :class="inputClass" placeholder="ឧ. កណ្តាលស្ទឹង" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ឃុំ/សង្កាត់</label>
                <input v-model="form.pob_commune" type="text" :class="inputClass" placeholder="ឧ. កណ្តាលស្ទឹង" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ស្រុក/ខណ្ឌ</label>
                <input v-model="form.pob_district" type="text" :class="inputClass" placeholder="ឧ. កណ្តាលស្ទឹង" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ខេត្ត/ក្រុង</label>
                <input v-model="form.pob_province" type="text" :class="inputClass" placeholder="ឧ. កណ្តាល" />
              </div>
            </div>
          </div>

          <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div class="flex justify-between items-center mb-3">
              <p class="text-sm font-bold text-indigo-600 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                អាសយដ្ឋានបច្ចុប្បន្ន
              </p>
              <button type="button" @click="copyPOB" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                ដូចទីកន្លែងកំណើត
              </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ភូមិ</label>
                <input v-model="form.village" type="text" :class="inputClass" placeholder="ឧ. បុរីវិមានភ្នំពេញ" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ឃុំ/សង្កាត់</label>
                <input v-model="form.commune" type="text" :class="inputClass" placeholder="ឧ. និរោធ" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ស្រុក/ខណ្ឌ</label>
                <input v-model="form.district" type="text" :class="inputClass" placeholder="ឧ. ច្បារអំពៅ" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ខេត្ត/ក្រុង</label>
                <input v-model="form.province" type="text" :class="inputClass" placeholder="ឧ. ភ្នំពេញ" />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">មុខវិជ្ជាជំនាញ</label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-4 bg-white border border-slate-200 rounded-xl">
                <label v-for="sub in ['គណិតវិទ្យា', 'រូបវិទ្យា', 'គីមីវិទ្យា', 'ជីវវិទ្យា', 'អក្សរសាស្ត្រខ្មែរ', 'ភាសាអង់គ្លេស']" :key="sub" class="flex items-center gap-2 cursor-pointer group">
                  <input type="checkbox" :value="sub" v-model="form.specialty" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                  <span class="text-xs text-slate-600 group-hover:text-indigo-600 transition-colors">{{ sub }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃចូលធ្វើការ</label>
              <input v-model="form.hire_date" type="date" :class="inputClass" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាព <span class="text-rose-500">*</span></label>
              <select v-model="form.status" :class="inputClass" class="cursor-pointer" required>
                <option :value="1">សកម្ម (Active)</option>
                <option :value="0">មិនសកម្ម (Inactive)</option>
              </select>
            </div>
          </div>

          <div v-if="errorMessage" class="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2 animate-shake">
            <AlertCircle class="w-4 h-4" />
            {{ errorMessage }}
          </div>
        </form>

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
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import { X, Camera, User, AlertCircle } from 'lucide-vue-next';
import api from '../../../services/authService'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue', 'refresh'])

const loading = ref(false)
const errorMessage = ref(null)
const previewUrl = ref(null) // បន្ថែម State សម្រាប់រក្សាទុកតំណភ្ជាប់រូបភាព Preview

const inputClass = "w-full mt-1.5 border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 text-sm font-medium text-slate-700 bg-white";

const initialState = {
  teacher_id_card: '', 
  name_kh: '',
  name_en: '',
  gender: 'male',
  specialty: [],
  email: '',
  phone: '',
  dob: '',
  pob_village: '',
  pob_commune: '',
  pob_province: '',
  pob_district: '',
  province: '',
  district: '',
  commune: '',
  village: '',
  hire_date: '',
  status: 1,
  photo: null
}

const form = ref({ ...initialState })

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    form.value = { ...initialState, specialty: [] }
    previewUrl.value = null // សម្អាតរូបភាពចាស់ចោលរាល់ពេលបើកផ្ទាំងបន្ថែមថ្មី
    errorMessage.value = null
  }
})

const handleFile = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.value.photo = file
    previewUrl.value = URL.createObjectURL(file) // បង្កើត URL ដើម្បីបង្ហាញរូបភាពភ្លាមៗលើស្បែក Form
  }
}

const copyPOB = () => {
  form.value.village = form.value.pob_village
  form.value.commune = form.value.pob_commune
  form.value.district = form.value.pob_district
  form.value.province = form.value.pob_province
}

const closeModal = () => emit('update:modelValue', false)

const submitForm = async () => {
  loading.value = true
  errorMessage.value = null

  try {
    const formData = new FormData()
    
    Object.keys(form.value).forEach(key => {
      if (key === 'specialty') {
        formData.append(key, JSON.stringify(form.value[key]))
      } else if (form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key])
      }
    })

    await api.post('/teachers', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    emit('refresh')
    closeModal()
  } catch (error) {
    console.error("Save Error:", error)
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors)[0][0]
    } else {
      errorMessage.value = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

.animate-shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>