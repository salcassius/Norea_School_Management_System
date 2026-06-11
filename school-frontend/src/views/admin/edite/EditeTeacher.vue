<template>
  <Transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-2xl shadow-xl overflow-hidden animate-in zoom-in duration-200 flex flex-col font-[Battambang]">
        
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-xl font-bold text-slate-800">កែសម្រួលព័ត៌មានគ្រូបង្រៀន</h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
            <X class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar flex-1 bg-white">
          
          <div class="flex flex-col items-center justify-center pb-4 border-b border-slate-100 w-full gap-2">
  <div class="relative shrink-0">
    <div class="w-24 h-24 rounded-2xl border border-slate-300 overflow-hidden bg-slate-50 flex items-center justify-center shadow-md">
      <img v-if="previewUrl || teacherData?.photo_url" :src="previewUrl || teacherData?.photo_url" class="w-full h-full object-cover" />
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

          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">លេខសម្គាល់គ្រូ <span class="text-rose-500">*</span></label>
              <input v-model="form.teacher_id_card" type="text" :class="inputClass" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះ (ខ្មែរ) <span class="text-rose-500">*</span></label>
              <input v-model="form.name_kh" type="text" :class="inputClass" required />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះឡាតាំង <span class="text-rose-500">*</span></label>
              <input v-model="form.name_en" type="text" :class="inputClass" class="uppercase" required />
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
              <input v-model="form.email" type="email" :class="inputClass" required />
            </div>
          </div>

          <div class="space-y-2 pt-1">
            <label class="block text-sm font-semibold text-slate-700 flex items-center gap-1.5 mb-1.5">
              <MapPin class="w-4 h-4 text-slate-400" /> ទីកន្លែងកំណើត
            </label>
            <div class="grid grid-cols-2 gap-4">
              <input v-model="form.pob_village" type="text" :class="inputClass" placeholder="ភូមិ" />
              <input v-model="form.pob_commune" type="text" :class="inputClass" placeholder="ឃុំ/សង្កាត់" />
              <input v-model="form.pob_district" type="text" :class="inputClass" placeholder="ស្រុក/ខណ្ឌ" />
              <input v-model="form.pob_province" type="text" :class="inputClass" placeholder="ខេត្ត/ក្រុង" />
            </div>
          </div>

          <div class="space-y-2 pt-1">
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-sm font-semibold text-slate-700 flex items-center gap-1.5">
                <Home class="w-4 h-4 text-slate-400" /> អាសយដ្ឋានបច្ចុប្បន្ន
              </label>
              <button type="button" @click="copyPOB" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                ដូចទីកន្លែងកំណើត
              </button>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <input v-model="form.village" type="text" :class="inputClass" placeholder="ភូមិ" />
              <input v-model="form.commune" type="text" :class="inputClass" placeholder="ឃុំ/សង្កាត់" />
              <input v-model="form.district" type="text" :class="inputClass" placeholder="ស្រុក/ខណ្ឌ" />
              <input v-model="form.province" type="text" :class="inputClass" placeholder="ខេត្ត/ក្រុង" />
            </div>
          </div>

          <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">មុខវិជ្ជាជំនាញ</label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-4 bg-white border border-slate-200 rounded-xl">
                <label v-for="sub in ['គណិតវិទ្យា', 'រូបវិទ្យា', 'គីមីវិទ្យា', 'ជីវវិទ្យា', 'អក្សរសាស្ត្រខ្មែរ', 'ភាសាអង់គ្លេស']" :key="sub" class="flex items-center gap-2 cursor-pointer group">
                  <input type="checkbox" :value="sub" v-model="form.specialty" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                  <span class="text-xs text-slate-600 group-hover:text-indigo-600 transition-colors">{{ sub }}</span>
                </label>
              </div>
            </div>

          <div class="grid grid-cols-2 gap-4 pt-1">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃចូលធ្វើការ</label>
              <input v-model="form.hire_date" type="date" :class="inputClass" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាព <span class="text-rose-500">*</span></label>
              <select v-model="form.status" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 outline-none cursor-pointer focus:border-indigo-500">
                <option :value="1">សកម្ម</option>
                <option :value="0">អសកម្ម</option>
              </select>
            </div>
          </div>

          <div v-if="errorMessage" class="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2 animate-shake">
            <AlertCircle class="w-4 h-4 shrink-0" />
            <span>{{ errorMessage }}</span>
          </div>
        </form>

        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 mt-4 bg-slate-50/50">
          <button type="button" @click="closeModal" class="px-5 py-2.5 text-slate-600 hover:bg-slate-100 rounded-xl font-medium transition-all">
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
import { ref, watch } from 'vue'
import { X, Camera, User, MapPin, Home, AlertCircle } from 'lucide-vue-next';
import api from '../../../services/authService'

const props = defineProps({
  modelValue: Boolean,
  teacherData: Object
})

const emit = defineEmits(['update:modelValue', 'refresh'])

const loading = ref(false)
const errorMessage = ref(null)
const previewUrl = ref(null)

// ប្រើប្រាស់ inputClass ដែលមាន Style ដូច EditeUser បេះបិទ
const inputClass = "w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all";

const form = ref({
  id: null,
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
})

watch(() => props.teacherData, (newData) => {
  if (newData) {
    console.log("ទិន្នន័យធ្លាក់មកកាន់ Modal:", newData);
    errorMessage.value = null
    previewUrl.value = null
    
    form.value = {
      id: newData.id || null,
      teacher_id_card: newData.teacher_id_card || '',
      name_kh: newData.name_kh || '',
      name_en: newData.name_en || '',
      gender: newData.gender || 'male',
      email: newData.email || (newData.user && newData.user.email ? newData.user.email : ''),
      phone: newData.phone || '',
      dob: newData.dob || '',
      hire_date: newData.hire_date || '',
      status: (newData.status === 'Active' || newData.status == 1) ? 1 : 0,
      photo: null,

      pob_village: newData.pob_village || newData.pob_khum || '',
      pob_commune: newData.pob_commune || newData.pob_sangkat || '',
      pob_district: newData.pob_district || newData.pob_khan || '',
      pob_province: newData.pob_province || newData.pob_city || '',

      village: newData.village || newData.khum || '',
      commune: newData.commune || newData.sangkat || '',
      district: newData.district || newData.khan || '',
      province: newData.province || newData.city || '',

      specialty: typeof newData.specialty === 'string' 
        ? JSON.parse(newData.specialty) 
        : (Array.isArray(newData.specialty) ? newData.specialty : [])
    };
  }
}, { immediate: true, deep: true })

const handleFile = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.value.photo = file
    previewUrl.value = URL.createObjectURL(file)
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
    formData.append('_method', 'PUT')

    Object.keys(form.value).forEach(key => {
      if (key === 'specialty') {
        formData.append(key, JSON.stringify(form.value[key]))
      } else if (key === 'photo') {
        if (form.value.photo) formData.append('photo', form.value.photo)
      } else if (form.value[key] !== null && form.value[key] !== undefined) {
        formData.append(key, form.value[key])
      }
    })

    await api.post(`/teachers/${form.value.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    emit('refresh')
    closeModal()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក'
  } finally {
    loading.value = false
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
/* Scrollbar តូចល្អិត មិនរំខានដល់ទិដ្ឋភាពរួម */
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
</style>