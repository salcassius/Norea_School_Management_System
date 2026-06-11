<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in zoom-in duration-200 font-[Battambang]">
      <div class="p-5 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center shadow-sm border border-indigo-100/50">
            <ClipboardCheck class="w-5 h-5 text-indigo-600" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800">បន្ថែមការប្រឡងថ្មី</h3>
            <p class="text-[10px] text-slate-400 font-semibold uppercase mt-0.5">សូមបំពេញព័ត៌មានការប្រឡង</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-5 space-y-4">
        
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-slate-600 ml-0.5">ឈ្មោះការប្រឡង <span class="text-rose-500">*</span></label>
          <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm outline-none focus:border-indigo-500 transition-all" placeholder="ឧ. ប្រឡងប្រចាំខែឧសភា" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-600 ml-0.5">មុខវិជ្ជា <span class="text-rose-500">*</span></label>
            <select v-model="form.subject_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none cursor-pointer" required>
              <option value="" disabled>ជ្រើសរើស</option>
              <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-600 ml-0.5">ថ្នាក់ <span class="text-rose-500">*</span></label>
            <select v-model="form.class_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none cursor-pointer" required>
              <option value="" disabled>ជ្រើសរើស</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-600 ml-0.5">កាលបរិច្ឆេទ <span class="text-rose-500">*</span></label>
            <input v-model="form.exam_date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate-600 ml-0.5">ប្រភេទ <span class="text-rose-500">*</span></label>
            <select v-model="form.type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none" required>
              <option value="ប្រចាំខែ">ប្រចាំខែ</option>
              <option value="ឆមាស">ឆមាស</option>
              <option value="ចុងឆ្នាំ">ចុងឆ្នាំ</option>
            </select>
          </div>
        </div>

        <div class="flex gap-2.5 pt-4">
          <button type="button" @click="$emit('close')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-all">បោះបង់</button>
          <button type="submit" :disabled="isSubmitting" class="flex-[1.5] py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 disabled:opacity-50 transition-all">
            {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការប្រឡង' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ClipboardCheck, X } from 'lucide-vue-next'
import api from '@/services/authService'

const props = defineProps({
  subjects: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  // ទទួលយក activeYearID ពី Parent ដើម្បីកំណត់ year_id ដោយស្វ័យប្រវត្តិ
  activeYearId: { type: [Number, String], default: 1 } 
})

const emit = defineEmits(['close', 'saved'])

const isSubmitting = ref(false)
const form = ref({
  name: '',
  type: 'ប្រចាំខែ',
  subject_id: '',
  class_id: '',
  year_id: props.activeYearId, 
  exam_date: new Date().toISOString().split('T')[0],
  status: 'ជិតមកដល់'
})

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    // ត្រូវប្រាកដថាបានបញ្ជូន year_id ត្រឹមត្រូវ
    await api.post('/exams', form.value)
    emit('saved')
  } catch (error) {
    console.error('Error creating exam:', error)
    alert('មានកំហុសក្នុងការបង្កើតការប្រឡង សូមពិនិត្យព័ត៌មានម្តងទៀត!')
  } finally {
    isSubmitting.value = false
  }
}
</script>