<template>
  <div v-if="modelValue" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-in zoom-in duration-150 font-[Battambang]">
      
      <div class="p-5 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shadow-sm border border-amber-100/50">
            <Edit3 class="w-5 h-5 text-blue-500" />
          </div>
          <div>
            <h2 class="text-base font-bold text-slate-800">កែសម្រួលមុខវិជ្ជា</h2>
            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Edit Subject</p>
          </div>
        </div>
        <button @click="closeModal" class="p-1.5 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-slate-600">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-5 space-y-4">
        
        <div class="space-y-1.5">
          <label class="text-1xs font-bold text-slate-600 ml-0.5">ឈ្មោះមុខវិជ្ជា <span class="text-blue-500">*</span></label>
          <div class="relative">
            <Type class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-slate-400" />
            <input 
              v-model="form.name"
              type="text" 
              placeholder="ឧទាហរណ៍៖ គណិតវិទ្យា"
              required
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-blue-500 outline-none transition-all"
            />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-1xs font-bold text-slate-600 ml-0.5">ពិន្ទុអតិបរមា <span class="text-blue-500">*</span></label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">#</span>
            <input 
              v-model="form.max_score"
              type="number" 
              placeholder="ឧទាហរណ៍៖ 100"
              required
              class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-11 pr-4 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-blue-500 outline-none transition-all"
            />
          </div>
        </div>

        <div v-if="errorMessage" class="p-3 bg-rose-50 text-blue-600 text-xs rounded-xl border border-rose-100 flex items-center gap-2 animate-shake">
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>

        <div class="pt-2 flex justify-end gap-3">
          <button type="button" @click="closeModal" 
            class="px-6 py-2.5 text-slate-700 hover:bg-slate-200 bg-indigo-200 rounded-xl font-medium transition-all text-sm">
            បោះបង់
          </button>
          <button type="submit" :disabled="isSubmitting"
            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all font-bold text-sm flex items-center gap-2 disabled:opacity-50">
            <span v-if="isSubmitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ isSubmitting ? 'កំពុងកែសម្រួល...' : 'រក្សាទុកការកែប្រែ' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { X, Edit3, Type, AlertCircle } from 'lucide-vue-next'
import api from '../../../services/authService'

const props = defineProps({
  modelValue: Boolean,
  subjectData: Object
})

const emit = defineEmits(['update:modelValue', 'refresh'])
const isSubmitting = ref(false)
const errorMessage = ref(null)

const form = ref({ 
  name: '',
  max_score: '' 
})

watch(() => props.subjectData, (val) => {
  if (val) {
    form.value.name = val.name
    form.value.max_score = val.max_score
  }
}, { immediate: true })

const closeModal = () => emit('update:modelValue', false)

const handleSubmit = async () => {
  if (!form.value.name || !form.value.max_score) return

  isSubmitting.value = true
  errorMessage.value = null
  
  try {
    await api.put(`/subjects/${props.subjectData.id}`, { 
      name: form.value.name,
      max_score: form.value.max_score
    }) 
    emit('refresh') 
    closeModal() 
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុក!'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.animate-shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>