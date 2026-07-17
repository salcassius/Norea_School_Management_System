<template>
  <Transition name="fade">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
      <div
        class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white font-[Battambang] shadow-2xl animate-in zoom-in duration-200"
      >
        <!-- Header -->
        <div
          class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4"
        >
          <h3 class="text-xl font-bold text-slate-800">បន្ថែមគ្រូបង្រៀនថ្មី</h3>
          <button
            type="button"
            aria-label="បិទ"
            class="text-slate-400 transition-colors hover:text-rose-500"
            @click="closeModal"
          >
            <X class="h-6 w-6" />
          </button>
        </div>

        <!-- Form -->
        <form
          id="create-teacher-form"
          class="custom-scrollbar flex-1 space-y-6 overflow-y-auto p-6"
          @submit.prevent="submitForm"
        >
          <!-- Profile photo -->
          <div
            class="flex w-full flex-col items-center justify-center gap-2 border-b border-slate-100 pb-2"
          >
            <div class="relative shrink-0">
              <div
                class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-slate-300 bg-slate-50 shadow-md"
              >
                <img
                  v-if="previewUrl"
                  :src="previewUrl"
                  alt="រូបថតប្រវត្តិរូប"
                  class="h-full w-full object-cover"
                />
                <User v-else class="h-12 w-12 text-slate-300" />
              </div>

              <label
                class="absolute -bottom-1 -right-1 cursor-pointer rounded-full border border-slate-200 bg-white p-2 text-slate-600 shadow-md transition-colors hover:text-indigo-600"
                title="ជ្រើសរើសរូបថត"
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
              <h4 class="text-sm font-semibold text-slate-700">រូបថតប្រវត្តិរូប</h4>
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
                placeholder="ឧ. សុខ សំណាង"
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
                placeholder="Ex. SOK SAMNANG"
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

            <!-- More positions are rendered from positionOptions -->
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
                អ៊ីមែល <span class="text-rose-500">*</span>
              </label>
              <input
                v-model.trim="form.email"
                type="email"
                :class="inputClass"
                placeholder="example@mail.com"
                autocomplete="email"
                required
              />
            </div>

            <div class="relative">
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ពាក្យសម្ងាត់ <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
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

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ថ្ងៃខែឆ្នាំកំណើត
              </label>
              <input v-model="form.dob" type="date" :class="inputClass" />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                លេខទូរស័ព្ទ
              </label>
              <input
                v-model.trim="form.phone"
                type="tel"
                :class="inputClass"
                placeholder="012 345 678"
                autocomplete="tel"
              />
            </div>
          </div>

          <!-- Place of birth -->
          <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
            <p class="mb-3 flex items-center gap-2 text-sm font-bold text-indigo-600">
              <span class="h-4 w-1.5 rounded-full bg-indigo-600"></span>
              ទីកន្លែងកំណើត
            </p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ភូមិ</label>
                <input
                  v-model.trim="form.pob_village"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. កណ្តាលស្ទឹង"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ឃុំ/សង្កាត់</label>
                <input
                  v-model.trim="form.pob_commune"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. កណ្តាលស្ទឹង"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ស្រុក/ខណ្ឌ</label>
                <input
                  v-model.trim="form.pob_district"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. កណ្តាលស្ទឹង"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ខេត្ត/ក្រុង</label>
                <input
                  v-model.trim="form.pob_province"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. កណ្តាល"
                />
              </div>
            </div>
          </div>

          <!-- Current address -->
          <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
            <div class="mb-3 flex items-center justify-between">
              <p class="flex items-center gap-2 text-sm font-bold text-indigo-600">
                <span class="h-4 w-1.5 rounded-full bg-indigo-600"></span>
                អាសយដ្ឋានបច្ចុប្បន្ន
              </p>
              <button
                type="button"
                class="text-[14px] font-semibold text-indigo-600 transition-colors hover:text-indigo-700"
                @click="copyPOB"
              >
                ដូចទីកន្លែងកំណើត
              </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ភូមិ</label>
                <input
                  v-model.trim="form.village"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. បុរីវិមានភ្នំពេញ"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ឃុំ/សង្កាត់</label>
                <input
                  v-model.trim="form.commune"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. និរោធ"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ស្រុក/ខណ្ឌ</label>
                <input
                  v-model.trim="form.district"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. ច្បារអំពៅ"
                />
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">ខេត្ត/ក្រុង</label>
                <input
                  v-model.trim="form.province"
                  type="text"
                  :class="inputClass"
                  placeholder="ឧ. ភ្នំពេញ"
                />
              </div>
            </div>
          </div>

          <!-- Specialty and employment -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
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

            <div>
              <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ថ្ងៃចូលធ្វើការ
              </label>
              <input v-model="form.hire_date" type="date" :class="inputClass" />
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
          class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-8 py-4"
        >
          <button
            type="button"
            class="rounded-xl bg-indigo-200 px-6 py-2.5 text-sm font-medium text-slate-700 transition-all hover:bg-slate-200"
            @click="closeModal"
          >
            បោះបង់
          </button>

          <button
            type="submit"
            form="create-teacher-form"
            :disabled="loading"
            class="flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-medium font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <span
              v-if="loading"
              class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
            ></span>
            {{ loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import { X, Camera, User, AlertCircle } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({
  modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'refresh'])

const loading = ref(false)
const errorMessage = ref(null)
const previewUrl = ref(null)
const showPassword = ref(false)

const inputClass =
  'w-full mt-1.5 border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 text-sm font-medium text-slate-700 bg-white'

// Add, remove, or rename positions here without changing the template.
// Keep each `value` consistent with the value accepted by your backend.
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
  teacher_id_card: '',
  name_kh: '',
  name_en: '',
  gender: 'male',
  specialty: [],
  position: '',
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

const clearPreviewUrl = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

const resetForm = () => {
  clearPreviewUrl()
  form.value = getInitialState()
  showPassword.value = false
  errorMessage.value = null
  loading.value = false
}

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) resetForm()
  },
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

  loading.value = true
  errorMessage.value = null

  try {
    const formData = new FormData()

    Object.entries(form.value).forEach(([key, value]) => {
      if (key === 'specialty') {
        formData.append(key, JSON.stringify(value))
      } else if (key === 'photo') {
        if (value instanceof File) formData.append(key, value)
      } else if (value !== null && value !== '') {
        formData.append(key, String(value))
      }
    })

    await api.post('/teachers', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    emit('refresh')
    emit('update:modelValue', false)
  } catch (error) {
    console.error('Save Error:', error)

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
  animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
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
