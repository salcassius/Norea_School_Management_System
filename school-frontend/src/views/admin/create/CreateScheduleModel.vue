<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden flex flex-col animate-in zoom-in duration-200 font-[Battambang]">
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
        <button @click="$emit('close')" class="p-1.5 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-slate-600">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-5 space-y-4">

        <!-- Inline error message -->
        <div v-if="errorMessage" class="flex items-start gap-2 bg-rose-50 border border-rose-200 text-rose-600 text-xs font-medium rounded-xl px-3.5 py-2.5">
          <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
          <span class="whitespace-pre-line">{{ errorMessage }}</span>
        </div>

        <div class="space-y-1.5">
          <label class="text-[14px] font-bold text-slate-600 ml-0.5">មុខវិជ្ជា <span class="text-rose-500">*</span></label>
          <div class="relative">
            <Layout class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select v-model="form.subject_id" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer" required>
              <option value="" disabled>--- ជ្រើសរើសមុខវិជ្ជា ---</option>
              <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
            </select>
            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-[14px] font-bold text-slate-600 ml-0.5">
            គ្រូបង្រៀន <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <UserRound
              :class="['absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5', isTeacherFieldInvalid ? 'text-rose-400' : 'text-slate-400']" />
            <select v-model="form.teacher_id" @change="errorMessage = ''"
            :class="['w-full bg-slate-50/50 border rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-slate-700 outline-none focus:ring-4 transition-all appearance-none cursor-pointer max-h-[140px] overflow-y-auto custom-scrollbar min-w-[140px]',
              isTeacherFieldInvalid ? 'border-rose-300 focus:ring-rose-500/10 focus:border-rose-500' : 'border-slate-200 focus:ring-indigo-500/10 focus:border-indigo-500']"
            required>
            <option value="" disabled>--- ជ្រើសរើសគ្រូបង្រៀន ---</option>
            <option v-for="tc in teachers" :key="tc.id" :value="tc.id">
              {{ tc.name_kh || tc.name }} {{ tc.name_en ? `(${tc.name_en})` : '' }}
            </option>
          </select>
            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-1xs font-semibold text-slate-500">
          <div>ថ្ងៃសិក្សា៖ <span class="text-slate-800 font-bold">{{ form.day }}</span></div>
          <div>ម៉ោងសិក្សា៖ <span class="text-slate-800 font-bold">{{ form.time }}</span></div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
          <button type="button" @click="$emit('close')" class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
              បោះបង់
          </button>
          <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-sm transition-all font-semibold flex items-center justify-center gap-2 disabled:opacity-50">
            {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុក' }}
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { BookOpen, X, Layout, ChevronDown, UserRound, MapPin, AlertCircle } from 'lucide-vue-next'
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
// ✅ សម្គាល់ថាតើ Error បច្ចុប្បន្ន ទាក់ទងទៅនឹង "គ្រូបង្រៀន" ដែរឬទេ (ដើម្បីធ្វើ highlight field)
const isTeacherFieldInvalid = ref(false)

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''
  isTeacherFieldInvalid.value = false
  try {
    // 💡 ជួសជុល Error 404៖ ដក /api ចេញ ពីព្រោះក្នុង authService មានស្រាប់ហើយ
    await api.post('/schedules', form.value)
    emit('saved')
  } catch (error) {
    console.error(error)

    if (error.response && error.response.status === 422) {
      const responseData = error.response.data
      const validationErrors = responseData?.errors

      if (validationErrors) {
        // ករណី Laravel Validation ធម្មតា បដិសេធ (ឧ. Field ទទេ, ខុសទម្រង់)
        errorMessage.value = Object.values(validationErrors).flat().join('\n')
      } else if (responseData?.message) {
        // ✅ ករណីលក្ខខណ្ឌជំនួញរបស់យើង (ឧ. គ្រូបង្រៀនជាន់ម៉ោង, ថ្នាក់ជាន់ម៉ោង)
        // Backend ផ្ញើ 'message' មកជាសារពេញលេញរួចហើយ ត្រូវបង្ហាញវាដោយផ្ទាល់
        errorMessage.value = responseData.message

        // បើសារនិយាយអំពីគ្រូ (មានពាក្យ "គ្រូ" ក្នុងសារ) សម្គាល់ field គ្រូជា Error
        if (responseData.message.includes('គ្រូ')) {
          isTeacherFieldInvalid.value = true
        }
      } else {
        errorMessage.value = 'ទិន្នន័យមិនត្រឹមត្រូវ សូមពិនិត្យមើលព័ត៌មានដែលបានបំពេញ'
      }
    } else {
      errorMessage.value = error.response?.data?.message || 'មានកំហុសក្នុងការបង្កើតកាលវិភាគថ្មី!'
    }

    // ✅ ជូនដំណឹងទៅ Component មេផងដែរ (សម្រាប់ Toast) ក្រៅពីសារខាងក្នុង Modal
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