<template>
  <Transition name="fade">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
      <div
        class="flex w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white font-[Battambang] shadow-xl animate-in zoom-in duration-200"
      >
        <!-- Header -->
        <div
          class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4"
        >
          <h3 class="text-xl font-bold text-slate-800">
            កែសម្រួលព័ត៌មានគ្រូបង្រៀន
          </h3>
          <button
            type="button"
            aria-label="បិទ"
            class="text-slate-400 transition-colors hover:text-slate-600"
            @click="closeModal"
          >
            <X class="h-6 w-6" />
          </button>
        </div>

        <!-- Form -->
        <form
          id="edit-teacher-form"
          class="custom-scrollbar max-h-[65vh] flex-1 space-y-4 overflow-y-auto bg-white p-6"
          @submit.prevent="submitForm"
        >
          <!-- Profile photo -->
          <div
            class="flex w-full flex-col items-center justify-center gap-2 border-b border-slate-100 pb-4"
          >
            <div class="relative shrink-0">
              <div
                class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-slate-300 bg-slate-50 shadow-md"
              >
                <img
                  v-if="previewUrl"
                  :src="previewUrl || teacherData?.photo_url"
                  class="h-full w-full object-cover"
                />
                <User v-else class="h-12 w-12 text-slate-300" />
              </div>

              <label
                class="absolute -bottom-1 -right-1 cursor-pointer rounded-full border border-slate-200 bg-white p-2 text-slate-600 shadow-md transition-colors hover:text-indigo-600"
                title="ផ្លាស់ប្តូររូបថត"
              >
                <Camera class="h-4 w-4" />
                <input
                  type="file"
                  accept="image/*"
                  class="sr-only"
                  @change="handleFile"
                />
              </label>
            </div>

            <div class="mt-1 text-center">
              <h4 class="text-sm font-semibold text-slate-700">
                រូបថតប្រវត្តិរូប
              </h4>
            </div>
          </div>

          <!-- Basic information -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ឈ្មោះ (ខ្មែរ) <span class="text-rose-500">*</span>
              </label>
              <input
                v-model.trim="form.name_kh"
                type="text"
                :class="inputClass"
                required
              />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ឈ្មោះឡាតាំង <span class="text-rose-500">*</span>
              </label>
              <input
                v-model.trim="form.name_en"
                type="text"
                :class="inputClass"
                class="uppercase"
                required
              />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ភេទ <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="form.gender"
                :class="inputClass"
                class="cursor-pointer"
                required
              >
                <option value="male">ប្រុស</option>
                <option value="female">ស្រី</option>
              </select>
            </div>

            <!-- Position -->
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                មុខតំណែង <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="form.position"
                :class="inputClass"
                class="cursor-pointer"
                required
              >
                <option value="" disabled>សូមជ្រើសរើសមុខតំណែង</option>

                <!-- Preserve an old/custom backend value when it is not in the list. -->
                <option
                  v-if="hasCustomPosition"
                  :value="form.position"
                >
                  {{ form.position }}
                </option>

                <option
                  v-for="position in positionOptions"
                  :key="position.value"
                  :value="position.value"
                >
                  {{ position.label }}
                </option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ថ្ងៃខែឆ្នាំកំណើត <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.dob"
                type="date"
                :class="inputClass"
                required
              />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                លេខទូរស័ព្ទ <span class="text-rose-500">*</span>
              </label>
              <input
                v-model.trim="form.phone"
                type="tel"
                :class="inputClass"
                autocomplete="tel"
                required
              />
            </div>

            <div class="md:col-span-2">
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                អ៊ីមែល <span class="text-rose-500">*</span>
              </label>
              <input
                v-model.trim="form.email"
                type="email"
                :class="inputClass"
                autocomplete="email"
                required
              />
            </div>
          </div>

          <!-- New password -->
          <div class="relative">
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
              លេខសម្ងាត់ថ្មី
              <span class="text-xs font-normal text-slate-400">
                (ទុកទំនេរ បើមិនចង់ប្តូរ)
              </span>
            </label>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              minlength="6"
              placeholder="••••••••"
              autocomplete="new-password"
              class="w-full rounded-xl border border-slate-300 px-4 py-2.5 pr-12 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
            />

            <button
              type="button"
              :aria-label="showPassword ? 'លាក់ពាក្យសម្ងាត់' : 'បង្ហាញពាក្យសម្ងាត់'"
              class="absolute right-3 top-[38px] text-gray-400 hover:text-slate-600 focus:outline-none"
              @click="showPassword = !showPassword"
            >
              <svg
                v-if="!showPassword"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>

              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"
                />
              </svg>
            </button>
          </div>

          <!-- Place of birth -->
          <div class="space-y-2 pt-1">
            <label
              class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold text-slate-700"
            >
              <MapPin class="h-4 w-4 text-slate-400" />
              ទីកន្លែងកំណើត
            </label>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <input
                v-model.trim="form.pob_village"
                type="text"
                :class="inputClass"
                placeholder="ភូមិ"
              />
              <input
                v-model.trim="form.pob_commune"
                type="text"
                :class="inputClass"
                placeholder="ឃុំ/សង្កាត់"
              />
              <input
                v-model.trim="form.pob_district"
                type="text"
                :class="inputClass"
                placeholder="ស្រុក/ខណ្ឌ"
              />
              <input
                v-model.trim="form.pob_province"
                type="text"
                :class="inputClass"
                placeholder="ខេត្ត/ក្រុង"
              />
            </div>
          </div>

          <!-- Current address -->
          <div class="space-y-2 pt-1">
            <div class="mb-1.5 flex items-center justify-between">
              <label
                class="flex items-center gap-1.5 text-sm font-semibold text-slate-700"
              >
                <Home class="h-4 w-4 text-slate-400" />
                អាសយដ្ឋានបច្ចុប្បន្ន
              </label>
              <button
                type="button"
                class="text-[14px] font-medium text-indigo-600 transition-colors hover:text-indigo-700"
                @click="copyPOB"
              >
                ដូចទីកន្លែងកំណើត
              </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <input
                v-model.trim="form.village"
                type="text"
                :class="inputClass"
                placeholder="ភូមិ"
              />
              <input
                v-model.trim="form.commune"
                type="text"
                :class="inputClass"
                placeholder="ឃុំ/សង្កាត់"
              />
              <input
                v-model.trim="form.district"
                type="text"
                :class="inputClass"
                placeholder="ស្រុក/ខណ្ឌ"
              />
              <input
                v-model.trim="form.province"
                type="text"
                :class="inputClass"
                placeholder="ខេត្ត/ក្រុង"
              />
            </div>
          </div>

          <!-- Specialty -->
          <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
              មុខវិជ្ជាជំនាញ
            </label>
            <div
              class="grid grid-cols-2 gap-2 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-3"
            >
              <label
                v-for="subject in subjectOptions"
                :key="subject"
                class="group flex cursor-pointer items-center gap-2"
              >
                <input
                  v-model="form.specialty"
                  type="checkbox"
                  :value="subject"
                  class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                />
                <span
                  class="text-xs text-slate-600 transition-colors group-hover:text-indigo-600"
                >
                  {{ subject }}
                </span>
              </label>
            </div>
          </div>

          <!-- Employment -->
          <div class="grid grid-cols-1 gap-4 pt-1 md:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ថ្ងៃចូលធ្វើការ
              </label>
              <input
                v-model="form.hire_date"
                type="date"
                :class="inputClass"
              />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ស្ថានភាព <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="form.status"
                :class="inputClass"
                class="cursor-pointer"
                required
              >
                <option :value="1">នៅបង្រៀន</option>
                <option :value="0">ឈប់បង្រៀន/ផ្អាក</option>
              </select>
            </div>
          </div>

          <!-- API error -->
          <div
            v-if="errorMessage"
            class="animate-shake flex items-center gap-2 rounded-xl border border-rose-100 bg-rose-50 p-3 text-xs text-rose-600"
          >
            <AlertCircle class="h-4 w-4 shrink-0" />
            <span>{{ errorMessage }}</span>
          </div>
        </form>

        <!-- Footer -->
        <div
          class="mt-4 flex justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4"
        >
          <button
            type="button"
            :disabled="loading"
            class="rounded-xl bg-indigo-200 px-6 py-2.5 text-sm font-medium text-slate-700 transition-all hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-60"
            @click="closeModal"
          >
            បោះបង់
          </button>

          <button
            type="submit"
            form="edit-teacher-form"
            :disabled="loading || !form.id"
            class="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-70"
          >
            <span
              v-if="loading"
              class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
            ></span>
            {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការកែប្រែ' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import {
  X,
  Camera,
  User,
  AlertCircle,
  MapPin,
  Home,
} from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({
  modelValue: Boolean,
  teacherData: Object,
})

const emit = defineEmits(['update:modelValue', 'refresh'])

const loading = ref(false)
const errorMessage = ref(null)
const previewUrl = ref(null)
const showPassword = ref(false)

const inputClass =
  'w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 text-sm font-medium text-slate-700 bg-white'

// Keep these values identical to positionOptions in CreateTeacher.vue.
// If your backend uses different enum values, update only each `value`.
const positionOptions = [
  { value: 'director', label: 'នាយក' },
  { value: 'deputy_director', label: 'នាយករង' },
  { value: 'teacher', label: 'គ្រូបង្រៀន' },
  { value: 'secretary', label: 'លេខាធិការ' },
  { value: 'accountant', label: 'គណនេយ្យករ' },
]

const subjectOptions = [
  'គណិតវិទ្យា',
  'រូបវិទ្យា',
  'គីមីវិទ្យា',
  'ជីវវិទ្យា',
  'អក្សរសាស្ត្រខ្មែរ',
  'ភាសាអង់គ្លេស',
  'ភូមិវិទ្យា',
  'ប្រវត្តិវិទ្យា',
  'សីលធម៌',
  'ផែនដីវិទ្យា',
  'គេហវិទ្យា',
]

const getInitialState = () => ({
  id: null,
  teacher_id_card: '',
  name_kh: '',
  name_en: '',
  gender: 'male',
  position: '',
  specialty: [],
  email: '',
  password: '',
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
  photo: null,
})

const form = ref(getInitialState())

const hasCustomPosition = computed(() => {
  if (!form.value.position) return false

  return !positionOptions.some(
    (position) => position.value === form.value.position,
  )
})

const clearPreviewUrl = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

const formatDateForInput = (value) => {
  if (!value) return ''

  const dateString = String(value)
  const match = dateString.match(/^\d{4}-\d{2}-\d{2}/)
  return match ? match[0] : ''
}

const parseSpecialty = (value) => {
  if (Array.isArray(value)) return [...value]
  if (!value || typeof value !== 'string') return []

  try {
    const parsedValue = JSON.parse(value)
    if (Array.isArray(parsedValue)) return parsedValue
  } catch {
    // Support old comma-separated specialty values.
    return value
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean)
  }

  return []
}

const normalizeStatus = (status) => {
  const activeValues = [1, true, '1', 'Active', 'active']
  return activeValues.includes(status) ? 1 : 0
}

const fillForm = (teacher) => {
  if (!teacher) {
    form.value = getInitialState()
    return
  }

  clearPreviewUrl()
  errorMessage.value = null
  showPassword.value = false

  form.value = {
    id: teacher.id ?? null,
    teacher_id_card: teacher.teacher_id_card || '',
    name_kh: teacher.name_kh || '',
    name_en: teacher.name_en || '',
    gender: teacher.gender || 'male',
    position: teacher.position || '',
    email: teacher.email || teacher.user?.email || '',
    password: '',
    phone: teacher.phone || '',
    dob: formatDateForInput(teacher.dob),
    hire_date: formatDateForInput(teacher.hire_date),
    status: normalizeStatus(teacher.status),
    photo: null,
    pob_village: teacher.pob_village || teacher.pob_khum || '',
    pob_commune: teacher.pob_commune || teacher.pob_sangkat || '',
    pob_district: teacher.pob_district || teacher.pob_khan || '',
    pob_province: teacher.pob_province || teacher.pob_city || '',
    village: teacher.village || teacher.khum || '',
    commune: teacher.commune || teacher.sangkat || '',
    district: teacher.district || teacher.khan || '',
    province: teacher.province || teacher.city || '',
    specialty: parseSpecialty(teacher.specialty),
  }
}

watch(
  [() => props.modelValue, () => props.teacherData],
  ([isOpen, teacher]) => {
    if (isOpen) {
      loading.value = false
      fillForm(teacher)
    }
  },
  { immediate: true, deep: true },
)

const handleFile = (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    errorMessage.value = 'សូមជ្រើសរើសឯកសាររូបភាព។'
    event.target.value = ''
    return
  }

  const maxFileSize = 5 * 1024 * 1024
  if (file.size > maxFileSize) {
    errorMessage.value = 'ទំហំរូបភាពមិនត្រូវលើសពី 5MB។'
    event.target.value = ''
    return
  }

  clearPreviewUrl()
  form.value.photo = file
  previewUrl.value = URL.createObjectURL(file)
  errorMessage.value = null
}

const copyPOB = () => {
  form.value.village = form.value.pob_village
  form.value.commune = form.value.pob_commune
  form.value.district = form.value.pob_district
  form.value.province = form.value.pob_province
}

const closeModal = () => {
  if (loading.value) return
  emit('update:modelValue', false)
}

const submitForm = async () => {
  if (loading.value) return

  if (!form.value.id) {
    errorMessage.value = 'រកមិនឃើញអត្តសញ្ញាណគ្រូបង្រៀន។'
    return
  }

  loading.value = true
  errorMessage.value = null

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')

    Object.entries(form.value).forEach(([key, value]) => {
      // The ID is already part of the request URL.
      if (key === 'id') return

      // Do not change the password when the field is empty.
      if (key === 'password' && !value) return

      if (key === 'specialty') {
        formData.append(key, JSON.stringify(value))
      } else if (key === 'photo') {
        if (value instanceof File) formData.append(key, value)
      } else if (value !== null && value !== undefined && value !== '') {
        formData.append(key, String(value))
      }
    })

    await api.post(`/teachers/${form.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    emit('refresh')
    emit('update:modelValue', false)
  } catch (error) {
    console.error('Update Error:', error)

    const validationErrors = error.response?.data?.errors
    if (validationErrors) {
      const firstError = Object.values(validationErrors).flat()[0]
      errorMessage.value = firstError || 'ទិន្នន័យមិនត្រឹមត្រូវ។'
    } else {
      errorMessage.value =
        error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក'
    }
  } finally {
    loading.value = false
  }
}

onBeforeUnmount(clearPreviewUrl)
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

/* Scrollbar តូចល្អិត មិនរំខានដល់ទិដ្ឋភាពរួម */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.animate-shake {
  animation: shake 0.4s both;
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
