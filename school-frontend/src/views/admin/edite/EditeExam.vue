<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden animate-in zoom-in duration-200 font-[Battambang]">
      <div class="p-5 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center shadow-sm border border-indigo-100/50">
            <ClipboardCheck class="w-5 h-5 text-indigo-600" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800">កែសម្រួលការប្រឡង</h3>
            <p class="text-[10px] text-slate-400 font-semibold uppercase mt-0.5">ធ្វើបច្ចុប្បន្នភាពព័ត៌មាន</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-5 space-y-4">
        <div class="space-y-1.5">
          <label class="text-[14px] font-bold text-slate-600 ml-0.5">ឈ្មោះការប្រឡង <span class="text-rose-500">*</span></label>
          <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm outline-none focus:border-indigo-500 transition-all" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-[14px] font-bold text-slate-600 ml-0.5">កាលបរិច្ឆេទ <span class="text-rose-500">*</span></label>
            <input v-model="form.exam_date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm outline-none" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-[14px] font-bold text-slate-600 ml-0.5">ប្រភេទ <span class="text-rose-500">*</span></label>
            <select v-model="form.type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm outline-none" required>
              <option value="ប្រចាំខែ">ប្រចាំខែ</option>
              <option value="ឆមាស">ឆមាស</option>
              <option value="ចុងឆ្នាំ">ប្រចាំឆ្នាំ</option>
            </select>
          </div>
        </div>

        <div class="flex gap-2.5 pt-4">
          <button type="button" @click="$emit('close')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-all">បោះបង់</button>
          <button type="submit" :disabled="isSubmitting" class="flex-[1.5] py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 disabled:opacity-50 transition-all">
            {{ isSubmitting ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកការកែប្រែ' }}
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
  examId: { type: [Number, String], required: true }
})

const emit = defineEmits(['close', 'saved'])
const isSubmitting = ref(false)
const form = ref({
  name: '',
  type: '',
  exam_date: ''
})

// នៅក្នុង EditExam.vue (script setup)

const loadExam = async () => {
  try {
    const res = await api.get(`/exams/${props.examId}`);
    const data = res.data.data || res.data; 

    console.log("ទិន្នន័យដែលទទួលបាន៖", data);
    form.value = {
      name: data.name || '',
      type: data.type || 'ប្រចាំខែ',
      exam_date: data.exam_date ? data.exam_date.split('T')[0] : new Date().toISOString().split('T')[0]
    };
    
  } catch (err) {
    console.error('Error loading exam:', err);
    alert('មិនអាចទាញយកទិន្នន័យបានទេ! សូមពិនិត្យមើល Console');
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    await api.put(`/exams/${props.examId}`, form.value)
    emit('saved')
    emit('close')
  } catch (error) {
    alert('មានកំហុសក្នុងការកែសម្រួល!')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(loadExam)
</script>