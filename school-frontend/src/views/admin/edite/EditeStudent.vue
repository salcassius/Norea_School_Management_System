<template>
    <Transition name="fade">
        <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden animate-in zoom-in duration-200 font-[Battambang] max-h-[90vh] flex flex-col">
                
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">កែសម្រួលព័ត៌មានសិស្ស</h3>
                        <p class="text-xs text-slate-500">កែប្រែព័ត៌មានចាំបាច់របស់សិស្សឱ្យបានត្រឹមត្រូវ</p>
                    </div>
                    <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors p-2 hover:bg-rose-50 rounded-full">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
                    
                    <div class="flex flex-col items-center justify-center pb-2 border-b border-slate-100 w-full gap-2">
                        <div class="relative shrink-0">
                            <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-300 shadow-md overflow-hidden flex items-center justify-center">
                                <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                                <img v-else-if="studentData?.photo" :src="`http://localhost:8000/storage/${studentData.photo}`" class="w-full h-full object-cover" />
                                <User v-else class="w-12 h-12 text-slate-300" />
                            </div>
                            <label class="absolute -bottom-1 -right-1 bg-white p-2 rounded-full shadow-md border border-slate-200 cursor-pointer text-slate-600 hover:text-amber-600 transition-colors">
                                <Camera class="w-4 h-4" />
                                <input type="file" class="hidden" @change="handlePhotoChange" accept="image/*" />
                            </label>
                        </div>
                        <div class="text-center mt-1">
                            <h4 class="text-sm font-semibold text-slate-700">ប្តូររូបថតសិស្ស</h4>
                        </div>
                    </div>

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
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">អត្តសញ្ញាណប័ណ្ណសិស្ស (ID) <span class="text-rose-500">*</span></label>
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
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃខែឆ្នាំកំណើត</label>
                            <input v-model="form.date_of_birth" type="date" :class="inputClass" />
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

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-sm font-bold text-indigo-600 mb-3 flex items-center gap-2">
                            <MapPin class="w-4 h-4 text-indigo-500" />
                            ទីកន្លែងកំណើត
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <input v-model="form.pob_village" type="text" :class="inputClass" placeholder="ភូមិ" />
                            <input v-model="form.pob_commune" type="text" :class="inputClass" placeholder="ឃុំ" />
                            <input v-model="form.pob_district" type="text" :class="inputClass" placeholder="ស្រុក/ខណ្ឌ" />
                            <input v-model="form.pob_province" type="text" :class="inputClass" placeholder="ខេត្ត/ក្រុង" />
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-emerald-600 flex items-center gap-2">
                                <Navigation class="w-4 h-4 text-emerald-500" />
                                អាសយដ្ឋានបច្ចុប្បន្ន
                            </p>
                            <button type="button" @click="copyPOB" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">អ៊ីមែល (សម្រាប់ Login) <span class="text-rose-500">*</span></label>
                            <input v-model="form.email" type="email" :class="inputClass" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">លេខទូរស័ព្ទសិស្ស</label>
                            <input v-model="form.phone" type="tel" :class="inputClass" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃចូលរៀន</label>
                            <input v-model="form.enrollment_date" type="date" :class="inputClass" />
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <p class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                            <Users class="w-5 h-5 text-slate-400" /> ព័ត៌មានអាណាព្យាបាល
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-slate-500 mb-1">ឈ្មោះឪពុក</label>
                                <input v-model="form.guardian_dad_name" type="text" :class="inputClass" placeholder="ឈ្មោះឪពុក" />
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-slate-500 mb-1">មុខរបរ</label>
                                <input v-model="form.guardian_dad_job" type="text" :class="inputClass" placeholder="មុខរបរ" />
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-slate-500 mb-1">លេខទូរស័ព្ទ</label>
                                <input v-model="form.guardian_dad_phone" type="tel" :class="inputClass" placeholder="លេខទូរស័ព្ទ" />
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-slate-500 mb-1">ឈ្មោះម្ដាយ</label>
                                <input v-model="form.guardian_mom_name" type="text" :class="inputClass" placeholder="ឈ្មោះម្ដាយ" />
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-slate-500 mb-1">មុខរបរ</label>
                                <input v-model="form.guardian_mom_job" type="text" :class="inputClass" placeholder="មុខរបរ" />
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-slate-500 mb-1">លេខទូរស័ព្ទ</label>
                                <input v-model="form.guardian_mom_phone" type="tel" :class="inputClass" placeholder="លេខទូរស័ព្ទ" />
                            </div>
                        </div>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2 animate-shake">
                        <AlertCircle class="w-4 h-4" />
                        {{ errorMessage }}
                    </div>
                </form>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="closeModal" class="px-5 py-2.5 text-slate-600 hover:bg-slate-200 rounded-xl font-medium transition-all text-sm">
                        បោះបង់
                    </button>
                    <button @click="handleSubmit" :disabled="isSubmitting"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl bg-indigo-600 shadow-lg shadow-amber-100 transition-all font-bold text-sm flex items-center gap-2 disabled:opacity-50">
                        <span v-if="isSubmitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <Save v-else class="w-4 h-4" />
                        {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'ធ្វើបច្ចុប្បន្នភាព' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { X, Camera, User, Save, MapPin, Navigation, Users, AlertCircle } from 'lucide-vue-next'
import studentService from '../../../services/studentService'
import api from '../../../services/authService'

const props = defineProps(['modelValue', 'studentData'])
const emit = defineEmits(['update:modelValue', 'refresh'])

const isSubmitting = ref(false)
const errorMessage = ref(null) // State សម្រាប់បង្ហាញសារ Error
const photoPreview = ref(null)
const academicYears = ref([])

const inputClass = "w-full mt-1.5 border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 text-sm font-medium text-slate-700 bg-white";

// Form State
const form = reactive({
    id: '',
    name_kh: '',
    name_en: '',
    student_id_card: '',
    gender: 'male',
    date_of_birth: new Date().toISOString().substr(0, 10),
    email: '',
    phone: '',
    year_id: '',
    pob_province: '',
    pob_district: '',
    pob_commune: '',
    pob_village: '',
    province: '',
    district: '',
    commune: '',
    village: '',
    enrollment_date: new Date().toISOString().substr(0, 10),
    guardian_mom_name: '',
    guardian_mom_job: '',
    guardian_mom_phone: '',
    guardian_dad_name: '',
    guardian_dad_job: '',
    guardian_dad_phone: '',
    status: 1,
    photo: null
})

// ទាញយកទិន្នន័យឆ្នាំសិក្សា
const fetchData = async () => {
    try {
        const yearsRes = await api.get('/years')
        academicYears.value = yearsRes.data
    } catch (error) {
        console.error("Fetch academic years error:", error)
    }
}

onMounted(fetchData)

// តាមដានការផ្លាស់ប្តូរទិន្នន័យសិស្ស
watch(() => props.studentData, (newVal) => {
    if (newVal) {
        photoPreview.value = null
        form.photo = null
        errorMessage.value = null
        
        Object.assign(form, { status: 1 }) // fallback status ដើម

        Object.keys(form).forEach(key => {
            if (newVal[key] !== undefined && newVal[key] !== null) {
                form[key] = newVal[key]
            } else {
                form[key] = '' 
            }
        })

        if (newVal.user && newVal.user.email) {
            form.email = newVal.user.email
        } else if (newVal.email) {
            form.email = newVal.email
        }

        form.id = newVal.id
    }
}, { immediate: true })

// គ្រប់គ្រងការជ្រើសរើសរូបភាពថ្មី
const handlePhotoChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.photo = file
        photoPreview.value = URL.createObjectURL(file)
    }
}

// មុខងារចម្លងអាសយដ្ឋាន (Copy Place of Birth to Current Address)
const copyPOB = () => {
    form.village = form.pob_village
    form.commune = form.pob_commune
    form.district = form.pob_district
    form.province = form.pob_province
}

// បិទផ្ទាំងកែសម្រួល
const closeModal = () => {
    emit('update:modelValue', false)
}

// ដាក់ស្នើទិន្នន័យទៅកាន់ Server
const handleSubmit = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    errorMessage.value = null;
    
    try {
        const formData = new FormData();
        
        Object.keys(form).forEach(key => {
            if (key === 'id') return; 
            if (key === 'photo' && !(form[key] instanceof File)) return; 
            
            if (form[key] !== null && form[key] !== undefined) {
                formData.append(key, form[key]);
            }
        });

        await studentService.updateStudent(form.id, formData);
        
        emit('refresh');   
        closeModal();      
        
    } catch (error) {
        console.error("Update Student Error:", error);
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            errorMessage.value = "ទិន្នន័យមិនត្រឹមត្រូវ៖ " + Object.values(errors).flat().join(", ");
        } else {
            errorMessage.value = "មានបញ្ហាកើតឡើងក្នុងការរក្សាទិន្នន័យ៖ " + (error.response?.data?.message || error.message);
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.animate-shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>