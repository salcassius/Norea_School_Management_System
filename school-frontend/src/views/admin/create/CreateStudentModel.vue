<template>
    <Transition name="fade">
        <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div
                class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden animate-in zoom-in duration-200 font-[Battambang] max-h-[90vh] flex flex-col">

                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">ចុះឈ្មោះសិស្សថ្មី</h3>
                        <p class="text-xs text-slate-500">សូមបំពេញព័ត៌មានខាងក្រោមឱ្យបានត្រឹមត្រូវ</p>
                    </div>
                    <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">

                    <div class="flex flex-col items-center justify-center pb-2 border-b border-slate-100 w-full gap-2">
                        <div class="relative shrink-0">
                            <div
                                class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-300 shadow-md overflow-hidden flex items-center justify-center">
                                <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                                <User v-else class="w-12 h-12 text-slate-300" />
                            </div>
                            <label
                                class="absolute -bottom-1 -right-1 bg-white p-2 rounded-full shadow-md border border-slate-200 cursor-pointer text-slate-600 hover:text-indigo-600 transition-colors">
                                <Camera class="w-4 h-4" />
                                <input type="file" class="hidden" @change="handlePhotoChange" accept="image/*" />
                            </label>
                        </div>
                        <div class="text-center mt-1">
                            <h4 class="text-sm font-semibold text-slate-700">រូបថតសិស្ស</h4>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះ (ខ្មែរ) <span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.name_kh" type="text" :class="inputClass" placeholder="ឧ. សុខ ហេង"
                                required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឈ្មោះឡាតាំង <span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.name_en" type="text" :class="inputClass" placeholder="Ex. SOK HENG"
                                class="uppercase" required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ឆ្នាំសិក្សា <span
                                    class="text-rose-500">*</span></label>
                            <select v-model="form.year_id" :class="inputClass" class="cursor-pointer" required>
                                <option value="" disabled>--- ជ្រើសរើសឆ្នាំសិក្សា ---</option>
                                <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.year_name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">អត្ថលេខ <span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.student_id_card" type="text" :class="inputClass"
                                placeholder="ឧ. STU-001" class="uppercase" required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ភេទ <span
                                    class="text-rose-500">*</span></label>
                            <select v-model="form.gender" :class="inputClass" class="cursor-pointer" required>
                                <option value="male">ប្រុស</option>
                                <option value="female">ស្រី</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃខែឆ្នាំកំណើត</label>
                            <input v-model="form.date_of_birth" type="date" :class="inputClass" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">មកពី <span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.pri_school" type="text" :class="inputClass"
                                placeholder="ឧ. បឋមសិក្សានរា" class="uppercase" required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្នាក់ទី <span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.from_class" type="text" :class="inputClass" placeholder="ឧ. ៦ក"
                                class="uppercase" required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ថ្ងៃខែឆ្នាំចូលរៀន</label>
                            <input v-model="form.enrollment_date" type="date" :class="inputClass" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ស្ថានភាព <span
                                    class="text-rose-500">*</span></label>
                            <select v-model="form.status" :class="inputClass" required>
                                <option value="1">កំពុងរៀន</option>
                                <option value="0">ឈប់រៀន/ព្យួរ</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <p class="text-sm font-bold text-indigo-600 mb-3 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                            ទីកន្លែងកំណើត
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ភូមិ</label>
                                <input v-model="form.pob_village" type="text" :class="inputClass"
                                    placeholder="ឧ. កណ្តាលស្ទឹង" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ឃុំ/សង្កាត់</label>
                                <input v-model="form.pob_commune" type="text" :class="inputClass"
                                    placeholder="ឧ. កណ្តាល" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ស្រុក/ខណ្ឌ</label>
                                <input v-model="form.pob_district" type="text" :class="inputClass"
                                    placeholder="ឧ. កណ្តាលស្ទឹង" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ខេត្ត/ក្រុង</label>
                                <input v-model="form.pob_province" type="text" :class="inputClass"
                                    placeholder="ឧ. កណ្តាល" />
                            </div>

                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm font-bold text-indigo-600 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                                អាសយដ្ឋានបច្ចុប្បន្ន
                            </p>
                            <button type="button" @click="copyPOB"
                                class="text-[14px] text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                                ដូចទីកន្លែងកំណើត
                            </button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ភូមិ</label>
                                <input v-model="form.village" type="text" :class="inputClass"
                                    placeholder="ឧ. បុរីវិមានភ្នំពេញ" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ឃុំ/សង្កាត់</label>
                                <input v-model="form.commune" type="text" :class="inputClass" placeholder="ឧ. និរោធ" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ស្រុក/ខណ្ឌ</label>
                                <input v-model="form.district" type="text" :class="inputClass"
                                    placeholder="ឧ. ច្បារអំពៅ" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">ខេត្ត/ក្រុង</label>
                                <input v-model="form.province" type="text" :class="inputClass"
                                    placeholder="ឧ. ភ្នំពេញ" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">អ៉ីមែល (សម្រាប់ login)
                                <span class="text-rose-500">*</span></label>
                            <input v-model="form.email" type="email" :class="inputClass" placeholder="example@gmail.com"
                                required />
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ពាក្យសម្ងាត់ <span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required
                                placeholder="••••••••"
                                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 pr-12 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all" />

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-[38px] text-gray-400 hover:text-slate-600 focus:outline-none">
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        <div>

                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">លេខទូរស័ព្ទសិស្ស</label>
                            <input v-model="form.phone" type="tel" :class="inputClass" placeholder="012 345 678" />
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-3 text-sm font-bold text-indigo-600 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                                ព័ត៌មានឪពុកម្តាយ
                            </div>

                            <div>
                                <label class="block text-[14px] font-semibold text-slate-600 mb-1">ឈ្មោះឪពុក</label>
                                <input v-model="form.guardian_dad_name" type="text" :class="inputClass"
                                    placeholder="ឈ្មោះឪពុក" />
                            </div>
                            <div>
                                <label class="block text-[14px] font-semibold text-slate-600 mb-1">មុខរបរ</label>
                                <input v-model="form.guardian_dad_job" type="text" :class="inputClass"
                                    placeholder="មុខរបរ" />
                            </div>
                            <div>
                                <label class="block text-[14px] font-semibold text-slate-600 mb-1">លេខទូរស័ព្ទ</label>
                                <input v-model="form.guardian_dad_phone" type="tel" :class="inputClass"
                                    placeholder="លេខទូរស័ព្ទ" />
                            </div>

                            <div>
                                <label class="block text-[14px] font-semibold text-slate-600 mb-1">ឈ្មោះម្ដាយ</label>
                                <input v-model="form.guardian_mom_name" type="text" :class="inputClass"
                                    placeholder="ឈ្មោះម្ដាយ" />
                            </div>
                            <div>
                                <label class="block text-[14px] font-semibold text-slate-600 mb-1">មុខរបរ</label>
                                <input v-model="form.guardian_mom_job" type="text" :class="inputClass"
                                    placeholder="មុខរបរ" />
                            </div>
                            <div>
                                <label class="block text-[14px] font-semibold text-slate-600 mb-1">លេខទូរស័ព្ទ</label>
                                <input v-model="form.guardian_mom_phone" type="tel" :class="inputClass"
                                    placeholder="លេខទូរស័ព្ទ" />
                            </div>
                        </div>
                    </div>

                    <div v-if="errorMessage"
                        class="p-3 bg-rose-50 text-rose-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2 animate-shake">
                        <AlertCircle class="w-4 h-4" />
                        {{ errorMessage }}
                    </div>
                </form>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
                    <button type="button" @click="closeModal"
                        class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
                        បោះបង់
                    </button>
                    <button @click="handleSubmit" :disabled="isSubmitting"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all font-bold text-sm flex items-center gap-2 disabled:opacity-50">
                        <span v-if="isSubmitting"
                            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { X, Camera, User, AlertCircle } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue', 'refresh'])

const isSubmitting = ref(false)
const errorMessage = ref(null) // បន្ថែម State សម្រាប់គ្រប់គ្រង Error
const photoPreview = ref(null)
const academicYears = ref([])

const showPassword = ref(false);

const inputClass = "w-full mt-1.5 border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 text-sm font-medium text-slate-700 bg-white";

const initialState = {
    name_kh: '',
    name_en: '',
    student_id_card: '',
    gender: 'male',
    date_of_birth: '',
    pri_school: '',
    from_class: '',
    pob_village: '',
    pob_commune: '',
    pob_province: '',
    pob_district: '',
    province: '',
    district: '',
    commune: '',
    village: '',
    email: null,
    password: '',
    phone: '',
    guardian_mom_name: '',
    guardian_mom_job: '',
    guardian_mom_phone: '',
    guardian_dad_name: '',
    guardian_dad_job: '',
    guardian_dad_phone: '',
    enrollment_date: '',
    photo: null,
    status: '1',
    year_id: '',
}

const form = reactive({ ...initialState })

const fetchData = async () => {
    try {
        const yearsRes = await api.get('/years')
        academicYears.value = yearsRes.data
        const activeYear = academicYears.value.find(y => y.is_active == 1)
        if (activeYear) form.year_id = activeYear.id
    } catch (error) {
        console.error("Fetch error:", error)
    }
}

onMounted(fetchData)

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        Object.assign(form, initialState)
        photoPreview.value = null
        errorMessage.value = null
        fetchData()
    }
})

const handlePhotoChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.photo = file
        photoPreview.value = URL.createObjectURL(file)
    }
}

// មុខងារចម្លងអាសយដ្ឋាន
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

        // ប្រើ Object.keys(form) ព្រោះ form ជា reactive object
        Object.keys(form).forEach(key => {
            const value = form[key]

            // គ្រប់គ្រងរូបភាពឱ្យបានត្រឹមត្រូវ
            if (key === 'photo') {
                if (value instanceof File) {
                    formData.append('photo', value)
                }
            }
            // គ្រប់គ្រងតម្លៃផ្សេងទៀត
            else if (value !== null && value !== undefined && value !== '') {
                formData.append(key, value)
            }
        })

        await api.post('/students', formData, {
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
        // កែពី loading.value មកជា isSubmitting.value
        isSubmitting.value = false
    }
}

</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.animate-shake {
    animation: shake 0.4s cubic-bezier(.36, .07, .19, .97) both;
}

@keyframes shake {

    10%,
    90% {
        transform: translate3d(-1px, 0, 0);
    }

    20%,
    80% {
        transform: translate3d(2px, 0, 0);
    }

    30%,
    50%,
    70% {
        transform: translate3d(-4px, 0, 0);
    }

    40%,
    60% {
        transform: translate3d(4px, 0, 0);
    }
}
</style>