<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden flex flex-col animate-in zoom-in duration-200">
      
      <div class="p-5 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shadow-sm border border-amber-100/50">
            <BookOpen class="w-5 h-5 text-blue-500" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800 tracking-tight">កែសម្រួលម៉ោងសិក្សា</h3>
            <p class="text-[12px] text-slate-500 uppercase tracking-wider mt-0.5">
              សូមកែប្រែព័ត៌មានខាងក្រោម រួចចុចប៊ូតុងធ្វើបច្ចុប្បន្នភាព
            </p>
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
          <span>{{ errorMessage }}</span>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-600 ml-0.5">មុខវិជ្ជា <span class="text-rose-500">*</span></label>
          <div class="relative">
            <Layout class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select v-model="form.subject_id" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer" required>
              <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
            </select>
            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-600 ml-0.5">គ្រូបង្រៀន <span class="text-rose-500">*</span></label>
          <div class="relative">
            <UserRound class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <select v-model="form.teacher_id" class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-10 text-sm font-medium text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer" required>
              <option v-for="tc in teachers" :key="tc.id" :value="tc.id">
                {{ tc.name_kh || tc.name }} {{ tc.name_en ? `(${tc.name_en})` : '' }}
              </option>
            </select>
            <ChevronDown class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs font-semibold text-slate-500">
          <div>ថ្ងៃសិក្សា៖ <span class="text-slate-800 font-bold">{{ form.day }}</span></div>
          <div>ម៉ោងសិក្សា៖ <span class="text-slate-800 font-bold">{{ form.time }}</span></div>
        </div>

        <div class="pt-2 flex justify-end gap-3">
          <button type="button" @click="$emit('close')" class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
            បោះបង់
          </button>
          <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all font-bold text-sm flex items-center gap-2 disabled:opacity-50">
            {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការកែប្រែ' }}
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { BookOpen, X, Layout, ChevronDown, UserRound, AlertCircle } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({
  entryData: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'saved'])
const form = ref({ ...props.entryData })
const isSubmitting = ref(false)
const errorMessage = ref('')

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''
  try {
    await api.put(`/schedules/${form.value.id}`, form.value)
    emit('saved')
    emit('close')
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'មានកំហុសក្នុងការកែសម្រួលកាលវិភាគ! សូមពិនិត្យមើល ID ឬ Connection។'
  } finally {
    isSubmitting.value = false
  }
}
</script>